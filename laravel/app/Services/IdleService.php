<?php

namespace App\Services;

use App\Models\EncounterGroup;
use App\Models\IdleEventLog;
use App\Models\Monster;
use App\Models\User;
use App\Models\UserZoneProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IdleService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $lootService,
        private readonly NarratorService $narratorService,
        private readonly ReputationService $reputationService
    ) {}

    /**
     * Calcule la progression offline depuis la dernière collecte.
     * Utilise le ratio de puissance — PAS de simulation tour par tour.
     */
    public function calculateOfflineProgress(User $user): array
    {
        $exploration = $user->activeExploration()->with('zone')->first();

        if (!$exploration) {
            // Guérison au repos : 10% des PV max par heure sans exploration
            $this->healHeroesAtRest($user);
            return [
                'had_exploration' => false,
                'elapsed_seconds' => 0,
                'combats_simulated' => 0,
                'xp_gained' => 0,
                'gold_gained' => 0,
                'items_gained' => [],
                'events' => [],
                'narrator_comment' => $this->narratorService->getComment('offline_return'),
            ];
        }

        $zone = $exploration->zone;
        $now = Carbon::now();
        $lastCalc = $user->last_idle_calc_at ?? $exploration->started_at;

        $maxSeconds = $this->settings->get('OFFLINE_MAX_HOURS', 12) * 3600;
        $elapsed = min((int) $now->diffInSeconds($lastCalc), $maxSeconds);

        if ($elapsed < 30) {
            return [
                'had_exploration' => true,
                'elapsed_seconds' => $elapsed,
                'combats_simulated' => 0,
                'xp_gained' => 0,
                'gold_gained' => 0,
                'items_gained' => [],
                'events' => [],
                'narrator_comment' => $this->narratorService->getComment('offline_return'),
            ];
        }

        $avgCombatDuration = $zone->avg_combat_duration;
        $combatsToSimulate = intdiv($elapsed, $avgCombatDuration);

        if ($combatsToSimulate <= 0) {
            return [
                'had_exploration' => true,
                'elapsed_seconds' => $elapsed,
                'combats_simulated' => 0,
                'xp_gained' => 0,
                'gold_gained' => 0,
                'items_gained' => [],
                'events' => [],
                'narrator_comment' => $this->narratorService->getComment('offline_return'),
            ];
        }

        // Charger l'équipe avec leurs stats
        $heroes = $user->activeHeroes()->with(['race', 'gameClass', 'trait_', 'equippedItems'])->get();

        if ($heroes->isEmpty()) {
            return [
                'had_exploration' => true,
                'elapsed_seconds' => $elapsed,
                'combats_simulated' => 0,
                'xp_gained' => 0,
                'gold_gained' => 0,
                'items_gained' => [],
                'events' => [],
                'narrator_comment' => 'Aucun héros disponible. Le Narrateur est perplexe.',
            ];
        }

        // Héros à 0 PV → arrêter l'exploration
        $aliveHeroes = $heroes->filter(fn($h) => $h->current_hp > 0);
        if ($aliveHeroes->isEmpty()) {
            $exploration->update(['is_active' => false]);
            return [
                'had_exploration'   => true,
                'elapsed_seconds'   => $elapsed,
                'combats_simulated' => 0,
                'xp_gained'         => 0,
                'gold_gained'       => 0,
                'items_gained'      => [],
                'events'            => [],
                'narrator_comment'  => 'Vos héros sont à terre. Le Narrateur les regarde avec une indifférence polie.',
            ];
        }
        $heroes = $aliveHeroes;

        // Charger les groupes de rencontres normaux (pas boss)
        $encounterGroups = EncounterGroup::where('zone_id', $zone->id)
            ->where('is_boss_encounter', false)
            ->where('weight', '>', 0)
            ->get();

        if ($encounterGroups->isEmpty()) {
            return [
                'had_exploration' => true,
                'elapsed_seconds' => $elapsed,
                'combats_simulated' => 0,
                'xp_gained' => 0,
                'gold_gained' => 0,
                'items_gained' => [],
                'events' => [],
                'narrator_comment' => 'Pas d\'ennemis à combattre. Le Narrateur est déçu.',
            ];
        }

        // Calculer puissance de l'équipe une seule fois
        $teamPower = 0;
        $teamStats = [];
        foreach ($heroes as $hero) {
            $stats = $hero->computedStats();
            $heroPower = $this->calculatePower($stats);
            // Appliquer modificateur de trait pour offline
            $traitMult = app(\App\Services\TraitService::class)->getOfflinePowerMultiplier($hero);
            $heroPower = intdiv($heroPower * $traitMult, 100);
            $teamPower += $heroPower;
            $teamStats[] = $stats;
        }

        $efficiency = $this->settings->get('OFFLINE_EFFICIENCY', 75);
        $healBetweenFights = $this->settings->get('HEAL_BETWEEN_FIGHTS', 30);

        $totalXp = 0;
        $totalGold = 0;
        $items = [];
        $events = [];
        $victories = 0;
        $defeats = 0;

        $eliteChance = $this->settings->get('MONSTER_ELITE_CHANCE', 8);
        $eliteXpBonus = $this->settings->get('MONSTER_ELITE_XP_BONUS', 75);
        $eliteLootBonus = $this->settings->get('MONSTER_ELITE_LOOT_BONUS', 75);
        $elitePrefixes = DB::table('elite_prefixes')->get();

        for ($i = 0; $i < $combatsToSimulate; $i++) {
            // Sélectionner un groupe d'ennemis aléatoire pondéré
            $group = $this->selectEncounterGroup($encounterGroups);
            $monsters = Monster::whereIn('id', $group->monster_ids)->get();

            if ($monsters->isEmpty()) {
                continue;
            }

            // Roll élite pour chaque monstre
            $isEliteEncounter = false;
            $eliteXpMult = 100;
            $eliteGoldMult = 100;
            $eliteLootMult = 100;

            $mobPower = 0;
            foreach ($monsters as $monster) {
                $mobStats = $monster->toStatArray();

                if (!$elitePrefixes->isEmpty() && random_int(1, 100) <= $eliteChance) {
                    $isEliteEncounter = true;
                    $prefix = $elitePrefixes->random();
                    // Appliquer multiplicateurs stat au power
                    $mobStats['atq'] = intdiv($mobStats['atq'] * $prefix->atq_multiplier, 100);
                    $mobStats['def'] = intdiv($mobStats['def'] * $prefix->def_multiplier, 100);
                    $mobStats['max_hp'] = intdiv(($mobStats['max_hp'] ?? $mobStats['hp'] ?? 1) * $prefix->hp_multiplier, 100);
                    $eliteXpMult = max($eliteXpMult, $prefix->xp_multiplier);
                    $eliteGoldMult = max($eliteGoldMult, $prefix->gold_multiplier);
                    $eliteLootMult = max($eliteLootMult, $prefix->loot_multiplier);
                }

                $mobPower += $this->calculatePower($mobStats);
            }

            $outcome = $this->simulateCombatOutcome($teamPower, $mobPower);

            if ($outcome === 'victory') {
                $victories++;
                // XP et gold avec efficacité offline
                $xp = 0;
                $gold = 0;
                $avgHeroLevel = (int) ($heroes->avg('level') ?? 1);

                foreach ($monsters as $monster) {
                    $xpBase = $this->settings->get('XP_BASE_PER_KILL', 10)
                        + ($monster->level * $this->settings->get('XP_LEVEL_MULTIPLIER', 2));
                    $xp += $xpBase;
                    $gold += random_int($monster->gold_min, max($monster->gold_min, $monster->gold_max));

                    // Loot (1 sur 3 combats, ou toujours si élite)
                    $shouldLoot = ($i % 3 === 0) || ($isEliteEncounter && random_int(1, 100) <= intdiv($this->settings->get('LOOT_DROP_CHANCE', 60) * $eliteLootMult, 100));
                    if ($shouldLoot) {
                        $item = $this->lootService->rollLoot($zone, $monster, $user);
                        if ($item) {
                            $items[] = $item;
                        }
                    }
                }

                // Appliquer bonus élite
                if ($isEliteEncounter) {
                    $xp = intdiv($xp * $eliteXpMult, 100);
                    $gold = intdiv($gold * $eliteGoldMult, 100);
                }

                // Appliquer efficacité offline
                $totalXp += intdiv($xp * $efficiency, 100);
                $totalGold += intdiv($gold * $efficiency, 100);
            } else {
                $defeats++;
                // Soins partiels entre les combats perdus
            }
        }

        // Réputation : 1 point par victoire (cap par le service)
        $repPerVictory = $this->settings->get('QUEST_REPUTATION_PER_QUEST', 10);
        $repGained = intdiv($victories * $repPerVictory, 10); // 1 rep tous les 10 combats gagnés environ

        // Persister les résultats en transaction
        DB::transaction(function () use ($user, $heroes, $totalXp, $totalGold, $items, $zone, $victories, $defeats, $now, $exploration, $events, $repGained) {
            $user->gold += $totalGold;
            $user->last_idle_calc_at = $now;
            $user->save();

            // XP par héros (distribué équitablement)
            $xpPerHero = $heroes->count() > 0 ? intdiv($totalXp, $heroes->count()) : 0;
            foreach ($heroes as $hero) {
                $hero->xp += $xpPerHero;
                // Vérifier montée de niveau
                while ($hero->xp >= $hero->xp_to_next_level) {
                    $hero->xp -= $hero->xp_to_next_level;
                    $hero->level++;
                    $hero->xp_to_next_level = $this->calculateXpToNextLevel($hero->level);
                    $hero->talent_points++;
                }
                $hero->save();
            }

            // Réputation de zone
            if ($repGained > 0) {
                $this->reputationService->addReputation($user->id, $zone->id, $repGained);
            }

            // Mettre à jour progression de zone
            UserZoneProgress::updateOrCreate(
                ['user_id' => $user->id, 'zone_id' => $zone->id],
                [
                    'total_combats' => \DB::raw('total_combats + ' . ($victories + $defeats)),
                    'total_victories' => \DB::raw('total_victories + ' . $victories),
                ]
            );

            // Marquer exploration comme collectée
            $exploration->last_collected_at = $now;
            $exploration->save();
        });

        return [
            'had_exploration'   => true,
            'elapsed_seconds'   => $elapsed,
            'combats_simulated' => $combatsToSimulate,
            'victories'         => $victories,
            'defeats'           => $defeats,
            'xp_gained'         => $totalXp,
            'gold_gained'       => $totalGold,
            'reputation_gained' => $repGained,
            'items_gained'      => $items,
            'events'            => $events,
            'narrator_comment'  => $this->narratorService->getComment('offline_return'),
        ];
    }

    /**
     * Simule l'issue d'un combat via ratio de puissance.
     * NE PAS simuler tour par tour.
     */
    public function simulateCombatOutcome(int $teamPower, int $mobPower): string
    {
        if ($mobPower <= 0) {
            return 'victory';
        }

        $ratio = intdiv($teamPower * 100, $mobPower);
        $winChance = $this->getWinChance($ratio);

        return random_int(1, 100) <= $winChance ? 'victory' : 'defeat';
    }

    /**
     * Power = (ATQ + INT) × HP × (100 + DEF) / 100
     */
    public function calculatePower(array $stats): int
    {
        $hp = max(1, $stats['max_hp'] ?? $stats['hp'] ?? 1);
        $atq = $stats['atq'] ?? 0;
        $int = $stats['int'] ?? 0;
        $def = $stats['def'] ?? 0;

        return intdiv(($atq + $int) * $hp * (100 + $def), 100);
    }

    public function getWinChance(int $ratio): int
    {
        if ($ratio >= 150) return 95;
        if ($ratio >= 100) return 75;
        if ($ratio >= 70)  return 50;
        if ($ratio >= 50)  return 25;
        return 5;
    }

    private function selectEncounterGroup($groups)
    {
        $totalWeight = $groups->sum('weight');
        $roll = random_int(1, max(1, $totalWeight));
        $cumulative = 0;

        foreach ($groups as $group) {
            $cumulative += $group->weight;
            if ($roll <= $cumulative) {
                return $group;
            }
        }

        return $groups->first();
    }

    private function calculateXpToNextLevel(int $level): int
    {
        $base = $this->settings->get('XP_TO_LEVEL_BASE', 100);
        $exp = $this->settings->get('XP_TO_LEVEL_EXPONENT', 115);

        // XP(N) = base × (exponent/100)^(N-1) — en entiers
        $result = $base;
        for ($i = 1; $i < $level; $i++) {
            $result = intdiv($result * $exp, 100);
        }

        return max($base, $result);
    }

    /**
     * Guérison au repos : 10% des PV max par heure depuis last_idle_calc_at.
     * Déclenché uniquement quand les héros ne sont PAS en exploration.
     */
    /**
     * @return array{initialized: bool, elapsed_minutes: int, heal_percent: float, heroes: list<array{name: string, hp_before: int, hp_after: int, max_hp: int, gained: int}>}
     */
    public function healHeroesAtRest(User $user): array
    {
        // Toujours charger les héros et synchroniser max_hp en DB dès le début
        $heroes = $user->activeHeroes()->with(['race', 'gameClass', 'equippedItems'])->get();
        foreach ($heroes as $hero) {
            $trueMaxHp = $hero->computedStats()['max_hp'];
            if ($hero->max_hp !== $trueMaxHp) {
                $hero->max_hp = $trueMaxHp;
                // Si current_hp dépasse le nouveau max (cas improbable), le plafonner
                $hero->current_hp = min($hero->current_hp, $trueMaxHp);
                $hero->save();
            }
        }

        $lastCalc = $user->last_idle_calc_at;
        if (!$lastCalc) {
            // Première fois : initialiser le timer, soin au prochain passage
            $user->last_idle_calc_at = Carbon::now();
            $user->save();
            return ['initialized' => true, 'elapsed_minutes' => 0, 'heal_percent' => 0.0, 'heroes' => []];
        }

        $elapsedSeconds = Carbon::now()->diffInSeconds($lastCalc);
        $elapsedHours   = $elapsedSeconds / 3600;
        if ($elapsedHours <= 0) {
            return ['initialized' => false, 'elapsed_minutes' => 0, 'heal_percent' => 0.0, 'heroes' => []];
        }

        $healPercentPerHour = $this->settings->get('REST_HEAL_PERCENT_PER_HOUR', 10);
        $healPercent = min(100.0, $elapsedHours * $healPercentPerHour);

        if ($healPercent <= 0) {
            return ['initialized' => false, 'elapsed_minutes' => (int) round($elapsedSeconds / 60), 'heal_percent' => 0.0, 'heroes' => []];
        }

        $heroResults = [];
        foreach ($heroes as $hero) {
            $trueMaxHp = $hero->max_hp; // déjà synchronisé ci-dessus
            if ($hero->current_hp < $trueMaxHp) {
                $hpBefore = $hero->current_hp;
                $heal = intdiv($trueMaxHp * (int) round($healPercent), 100);
                if ($heal <= 0) {
                    continue;
                }
                $hero->current_hp = min($trueMaxHp, $hero->current_hp + $heal);
                $hero->save();
                $heroResults[] = [
                    'name'      => $hero->name,
                    'hp_before' => $hpBefore,
                    'hp_after'  => $hero->current_hp,
                    'max_hp'    => $trueMaxHp,
                    'gained'    => $hero->current_hp - $hpBefore,
                ];
            }
        }

        $user->last_idle_calc_at = Carbon::now();
        $user->save();

        return [
            'initialized'    => false,
            'elapsed_minutes' => (int) round($elapsedSeconds / 60),
            'heal_percent'   => round($healPercent, 2),
            'heroes'         => $heroResults,
        ];
    }
}
