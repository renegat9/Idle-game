<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\Monster;
use Illuminate\Support\Facades\DB;

class CombatService
{
    /** Cache des multiplicateurs élémentaires chargés depuis element_chart */
    private ?array $elementChart = null;

    public function __construct(
        private readonly SettingsService $settings,
        private readonly TraitService $traitService
    ) {}

    /**
     * Résout un combat complet entre une équipe de héros et des ennemis.
     * Retourne le résultat structuré.
     */
    public function resolveCombat(array $heroModels, array $monsterModels): array
    {
        $maxTurns = $this->settings->get('COMBAT_MAX_TURNS', 15);

        // Réinitialiser le cache élémentaire pour ce combat
        $this->elementChart = null;

        // Calculer les synergies d'équipe (integer-only)
        $heroCollection = collect($heroModels);
        $synergyMods = $this->traitService->getTeamSynergyModifiers($heroCollection);

        // Initialiser l'état de combat
        $state = [
            'heroes' => $this->initHeroesWithSynergies($heroModels, $synergyMods),
            'enemies' => $this->initEnemies($monsterModels),
            'turn' => 0,
            'fled_heroes' => [],
            'sleeping' => [],
            'pending_buffs' => [],
            'kleptomane_hero_id' => null,
            'consumed_potion_hero_id' => null,
            'blocked_heroes' => [],
            'revealed' => false,
            'log' => [],
            'trait_triggers' => [],
            'xp_gained' => 0,
            'gold_gained' => 0,
            'loot_candidates' => [],
            'synergy_mods' => $synergyMods,
        ];

        // Log des synergies actives
        foreach ($synergyMods['active_synergies'] as $syn) {
            $state['log'][] = "✨ Synergie active : {$syn['hero_name']} — {$syn['name']}";
        }

        // Appliquer debuff de VIT ennemi (barde_narcoleptique)
        if ($synergyMods['enemy_vit_debuff_pct'] > 0) {
            foreach ($state['enemies'] as &$enemy) {
                $enemy['vit'] = max(0, (int) ($enemy['vit'] * (100 - $synergyMods['enemy_vit_debuff_pct']) / 100));
            }
            unset($enemy);
        }

        // Boucle de combat
        while ($state['turn'] < $maxTurns) {
            $state['turn']++;

            if ($this->checkCombatEnd($state)) {
                break;
            }

            // Construire l'ordre d'initiative pour ce tour
            $initiative = $this->buildInitiativeOrder($state);

            foreach ($initiative as $combatant) {
                if ($this->checkCombatEnd($state)) {
                    break;
                }

                if ($combatant['type'] === 'hero') {
                    $this->processHeroTurn($combatant['index'], $state, $heroModels);
                } else {
                    $this->processEnemyTurn($combatant['index'], $state);
                }
            }
        }

        // Calculer les récompenses
        $result = $this->calculateResult($state, $monsterModels);

        return $result;
    }

    private function initHeroes(array $heroModels): array
    {
        return $this->initHeroesWithSynergies($heroModels, []);
    }

    private function initHeroesWithSynergies(array $heroModels, array $synergyMods): array
    {
        $heroes = [];
        $atqBonus   = (int) ($synergyMods['atq_bonus_pct'] ?? 0);
        $defBonus   = (int) ($synergyMods['def_bonus_pct'] ?? 0);
        $defPenalty = (int) ($synergyMods['def_penalty_pct'] ?? 0);
        $atqPenalty = (int) ($synergyMods['atq_penalty_pct'] ?? 0);
        $intBonus   = (int) ($synergyMods['int_bonus_pct'] ?? 0);
        $vitPenalty = (int) ($synergyMods['vit_penalty_pct'] ?? 0);
        $allyVitDebuff = (int) ($synergyMods['ally_vit_debuff_pct'] ?? 0);

        foreach ($heroModels as $hero) {
            $stats = $hero->computedStats();

            $element = 'physique';
            if ($hero->relationLoaded('items')) {
                $weapon = $hero->items->firstWhere('slot', 'weapon');
                if ($weapon && !empty($weapon->element)) {
                    $element = $weapon->element;
                }
            }

            // Appliquer les modificateurs de synérgies (integer-only)
            $atq = (int) $stats['atq'];
            $def = (int) $stats['def'];
            $vit = (int) $stats['vit'];
            $int = (int) $stats['int'];

            if ($atqBonus > 0)    { $atq = intdiv($atq * (100 + $atqBonus), 100); }
            if ($atqPenalty > 0)  { $atq = intdiv($atq * (100 - $atqPenalty), 100); }
            if ($defBonus > 0)    { $def = intdiv($def * (100 + $defBonus), 100); }
            if ($defPenalty > 0)  { $def = intdiv($def * (100 - $defPenalty), 100); }
            if ($intBonus > 0)    { $int = intdiv($int * (100 + $intBonus), 100); }
            if ($vitPenalty > 0)  { $vit = intdiv($vit * (100 - $vitPenalty), 100); }
            if ($allyVitDebuff > 0) { $vit = intdiv($vit * (100 - $allyVitDebuff), 100); }

            $heroes[] = [
                'hero_id'    => $hero->id,
                'name'       => $hero->name,
                'current_hp' => $stats['current_hp'],
                'max_hp'     => $stats['max_hp'],
                'atq'        => max(1, $atq),
                'def'        => max(0, $def),
                'vit'        => max(0, $vit),
                'cha'        => (int) $stats['cha'],
                'int'        => max(0, $int),
                'element'    => $element,
                'level'      => $hero->level,
                'is_alive'   => $stats['current_hp'] > 0,
            ];
        }
        return $heroes;
    }

    private function initEnemies(array $monsterModels): array
    {
        $enemies = [];
        foreach ($monsterModels as $monster) {
            $enemies[] = array_merge($monster->toStatArray(), ['is_alive' => true]);
        }
        return $enemies;
    }

    private function buildInitiativeOrder(array $state): array
    {
        $combatants = [];

        foreach ($state['heroes'] as $i => $hero) {
            if ($hero['is_alive'] && !in_array($hero['hero_id'], $state['fled_heroes'])) {
                $combatants[] = [
                    'type' => 'hero',
                    'index' => $i,
                    'initiative' => $hero['vit'] + random_int(1, 20),
                ];
            }
        }

        foreach ($state['enemies'] as $i => $enemy) {
            if ($enemy['is_alive']) {
                $combatants[] = [
                    'type' => 'enemy',
                    'index' => $i,
                    'initiative' => $enemy['vit'] + random_int(1, 20),
                ];
            }
        }

        // Trier par initiative décroissante (tout en entiers)
        usort($combatants, fn($a, $b) => $b['initiative'] - $a['initiative']);

        return $combatants;
    }

    private function processHeroTurn(int $heroIndex, array &$state, array $heroModels): void
    {
        $hero = &$state['heroes'][$heroIndex];
        if (!$hero['is_alive']) {
            return;
        }

        $heroModel = $heroModels[$heroIndex];

        // 1. Vérifier statuts (sommeil)
        if (isset($state['sleeping'][$hero['hero_id']])) {
            $wakeChance = $this->settings->get('TRAIT_NARCOLEPTIQUE_WAKE_CHANCE', 50);
            if (random_int(1, 100) <= $wakeChance) {
                unset($state['sleeping'][$hero['hero_id']]);
                $state['log'][] = $hero['name'] . ' se réveille en sursaut.';
            } else {
                $state['sleeping'][$hero['hero_id']]--;
                if ($state['sleeping'][$hero['hero_id']] <= 0) {
                    unset($state['sleeping'][$hero['hero_id']]);
                }
                $state['log'][] = $hero['name'] . ' dort profondément (zzz).';
                return;
            }
        }

        // 2. Jet de trait (si trigger_moment = turn_start)
        $traitModel = $heroModel->trait_;
        if ($traitModel && in_array($traitModel->trigger_moment, ['turn_start']) && $this->traitService->shouldTrigger($heroModel)) {
            $traitEvent = $this->traitService->applyTraitEffect($heroModel, $state, 'combat');
            $state['trait_triggers'][] = $traitEvent;
            $state['log'][] = $traitEvent['message'];

            if ($traitEvent['skip_turn'] ?? false) {
                return;
            }
        }

        // 3. Action normale : attaquer l'ennemi le plus proche vivant
        $targetIndex = $this->findLiveEnemy($state);
        if ($targetIndex === null) {
            return;
        }

        $target = &$state['enemies'][$targetIndex];

        // Vérification esquive
        if ($this->rollDodge($target, $hero)) {
            $state['log'][] = $target['name'] . ' esquive l\'attaque de ' . $hero['name'] . '.';
            return;
        }

        // Calcul dégâts
        $variance = random_int(
            $this->settings->get('VARIANCE_MIN', 90),
            $this->settings->get('VARIANCE_MAX', 110)
        );

        $damage = $this->calculatePhysicalDamage($hero, $target, $variance);

        // Modificateur élémentaire
        $elemMult = $this->applyElementalMultiplier($hero['element'] ?? 'physique', $target['element'] ?? 'physique');
        if ($elemMult !== 100) {
            $damage = intdiv($damage * $elemMult, 100);
            $damage = max($damage, $this->settings->get('MIN_DAMAGE', 1));
        }

        // Critique ?
        $critChance = $this->calculateCritChance($hero);
        $isCrit = random_int(1, 100) <= $critChance;
        if ($isCrit) {
            $damage = $this->applyCritDamage($damage);
            $state['log'][] = $hero['name'] . ' critique ! ' . $damage . ' dégâts sur ' . $target['name'] . '.';
        } else {
            $state['log'][] = $hero['name'] . ' attaque ' . $target['name'] . ' pour ' . $damage . ' dégâts.';
        }

        $target['current_hp'] = max(0, $target['current_hp'] - $damage);
        if ($target['current_hp'] <= 0) {
            $target['is_alive'] = false;
            $state['log'][] = $target['name'] . ' est vaincu !';
        }

        // Trait Pyromane (after_attack)
        if ($traitModel && $traitModel->trigger_moment === 'after_attack' && $this->traitService->shouldTrigger($heroModel)) {
            $traitEvent = $this->traitService->applyTraitEffect($heroModel, $state, 'combat');
            $state['trait_triggers'][] = $traitEvent;
            $state['log'][] = $traitEvent['message'];
        }
    }

    private function processEnemyTurn(int $enemyIndex, array &$state): void
    {
        $enemy = &$state['enemies'][$enemyIndex];
        if (!$enemy['is_alive']) {
            return;
        }

        $targetIndex = $this->findLiveHero($state);
        if ($targetIndex === null) {
            return;
        }

        $target = &$state['heroes'][$targetIndex];

        // Vérification esquive
        if ($this->rollDodge($target, $enemy)) {
            $state['log'][] = $target['name'] . ' esquive l\'attaque de ' . $enemy['name'] . '.';
            return;
        }

        $variance = random_int(
            $this->settings->get('VARIANCE_MIN', 90),
            $this->settings->get('VARIANCE_MAX', 110)
        );

        $damage = $this->calculatePhysicalDamage($enemy, $target, $variance);

        // Modificateur élémentaire
        $elemMult = $this->applyElementalMultiplier($enemy['element'] ?? 'physique', $target['element'] ?? 'physique');
        if ($elemMult !== 100) {
            $damage = intdiv($damage * $elemMult, 100);
            $damage = max($damage, $this->settings->get('MIN_DAMAGE', 1));
        }

        $state['log'][] = $enemy['name'] . ' attaque ' . $target['name'] . ' pour ' . $damage . ' dégâts.';

        $target['current_hp'] = max(0, $target['current_hp'] - $damage);
        if ($target['current_hp'] <= 0) {
            $target['is_alive'] = false;
            $state['log'][] = $target['name'] . ' est KO !';
        }
    }

    // ── Éléments ─────────────────────────────────────────────────────────────

    /**
     * Charge le tableau élémentaire une seule fois par combat.
     * Retourne le multiplicateur (centièmes) de l'attaquant sur le défenseur.
     * Défaut = 100 (neutre).
     */
    public function applyElementalMultiplier(string $attackerElement, string $defenderElement): int
    {
        if ($this->elementChart === null) {
            $rows = DB::table('element_chart')->get(['attacker_element', 'defender_element', 'damage_multiplier']);
            $this->elementChart = [];
            foreach ($rows as $row) {
                $this->elementChart[$row->attacker_element][$row->defender_element] = (int) $row->damage_multiplier;
            }
        }

        return $this->elementChart[$attackerElement][$defenderElement] ?? 100;
    }

    // ── Formules de combat (tout en intdiv) ──────────────────────────────────

    public function calculateInitiative(array $combatant): int
    {
        return $combatant['vit'] + random_int(1, 20);
    }

    public function calculateDodgeChance(array $target, array $attacker): int
    {
        $speedBase = $this->settings->get('SPEED_BASE', 100);
        $dodgeCap = $this->settings->get('DODGE_CAP', 40);
        $denominator = $target['def'] + $attacker['vit'] + $speedBase;

        if ($denominator <= 0) {
            return 0;
        }

        return min(intdiv($target['def'] * 100, $denominator), $dodgeCap);
    }

    public function rollDodge(array $target, array $attacker): bool
    {
        $chance = $this->calculateDodgeChance($target, $attacker);
        return random_int(1, 100) <= $chance;
    }

    public function calculatePhysicalDamage(array $attacker, array $target, int $variance): int
    {
        $softCap = $this->settings->get('DEF_SOFT_CAP', 200);
        $hardCap = $this->settings->get('DEF_HARD_CAP', 75);
        $minDamage = $this->settings->get('MIN_DAMAGE', 1);

        $raw = intdiv($attacker['atq'] * $variance, 100);

        $denominator = $target['def'] + $softCap;
        $reduction = $denominator > 0
            ? min(intdiv($target['def'] * 100, $denominator), $hardCap)
            : 0;

        $net = intdiv($raw * (100 - $reduction), 100);

        return max($net, $minDamage);
    }

    public function calculateMagicDamage(array $attacker, array $target, int $variance): int
    {
        $softCap = $this->settings->get('DEF_SOFT_CAP', 200);
        $hardCap = $this->settings->get('DEF_HARD_CAP', 75);
        $minDamage = $this->settings->get('MIN_DAMAGE', 1);

        $raw = intdiv(($attacker['int'] ?? 0) * $variance, 100);

        $denominator = ($target['int'] ?? 0) + $softCap;
        $resistance = $denominator > 0
            ? min(intdiv(($target['int'] ?? 0) * 100, $denominator), $hardCap)
            : 0;

        // La résistance magique est divisée par 2 avant application
        $net = intdiv($raw * (100 - intdiv($resistance, 2)), 100);

        return max($net, $minDamage);
    }

    public function calculateCritChance(array $combatant): int
    {
        $base = $this->settings->get('CRIT_BASE_CHANCE', 5);
        $cap = $this->settings->get('CRIT_CAP', 50);
        $cha = $combatant['cha'] ?? 0;

        return min($base + intdiv($cha, 4), $cap);
    }

    public function applyCritDamage(int $damage): int
    {
        $multiplier = $this->settings->get('CRIT_DAMAGE_MULTIPLIER', 150);
        return intdiv($damage * $multiplier, 100);
    }

    public function calculateXpForKill(int $enemyLevel, int $heroLevel): int
    {
        $base = $this->settings->get('XP_BASE_PER_KILL', 10);
        $mult = $this->settings->get('XP_LEVEL_MULTIPLIER', 2);
        $xp = $base + ($enemyLevel * $mult);

        $diff = $enemyLevel - $heroLevel;

        if ($diff > 0) {
            $bonus = $this->settings->get('XP_LEVEL_DIFF_BONUS', 10);
            $xp = intdiv($xp * (100 + $diff * $bonus), 100);
        } elseif ($diff < 0) {
            $penalty = $this->settings->get('XP_LEVEL_DIFF_PENALTY', 5);
            $penaltyTotal = abs($diff) * $penalty;
            $xp = max(1, intdiv($xp * (100 - $penaltyTotal), 100));
        }

        return max(1, $xp);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function checkCombatEnd(array $state): bool
    {
        $allEnemiesDead = empty(array_filter($state['enemies'], fn($e) => $e['is_alive']));
        $allHeroesDead = empty(array_filter($state['heroes'], fn($h) => $h['is_alive']));
        $allHeroesFled = count($state['fled_heroes']) >= count($state['heroes']);

        return $allEnemiesDead || $allHeroesDead || $allHeroesFled;
    }

    private function findLiveEnemy(array $state): ?int
    {
        foreach ($state['enemies'] as $i => $enemy) {
            if ($enemy['is_alive']) {
                return $i;
            }
        }
        return null;
    }

    private function findLiveHero(array $state): ?int
    {
        foreach ($state['heroes'] as $i => $hero) {
            if ($hero['is_alive'] && !in_array($hero['hero_id'], $state['fled_heroes'])) {
                return $i;
            }
        }
        return null;
    }

    private function calculateResult(array $state, array $monsterModels): array
    {
        $aliveHeroes = array_filter($state['heroes'], fn($h) => $h['is_alive']);
        $aliveEnemies = array_filter($state['enemies'], fn($e) => $e['is_alive']);
        $allFled = count($state['fled_heroes']) >= count($state['heroes']);

        if ($allFled || (empty($aliveHeroes) && !empty($aliveEnemies))) {
            $result = 'defeat';
        } elseif (empty($aliveEnemies) && !empty($aliveHeroes)) {
            $result = 'victory';
        } elseif (empty($aliveEnemies) && empty($aliveHeroes)) {
            $result = 'draw';
        } else {
            $result = 'draw';
        }

        $xpGained = 0;
        $goldGained = 0;
        $avgHeroLevel = count($state['heroes']) > 0
            ? intdiv(array_sum(array_column($state['heroes'], 'level')), count($state['heroes']))
            : 1;

        $synergyMods = $state['synergy_mods'] ?? [];
        $lootBonusPct = (int) ($synergyMods['loot_bonus_pct'] ?? 0);

        if ($result === 'victory') {
            foreach ($monsterModels as $monster) {
                $xpGained += $this->calculateXpForKill($monster->level, $avgHeroLevel);
                $baseGold = random_int($monster->gold_min, max($monster->gold_min, $monster->gold_max));
                $goldGained += $baseGold;
            }
        }

        // Appliquer le bonus de loot des synergies (loot_bonus_pct)
        if ($lootBonusPct > 0 && !empty($state['loot_candidates'])) {
            // Ajouter des exemplaires supplémentaires selon le bonus (integer)
            $extra = intdiv(count($state['loot_candidates']) * $lootBonusPct, 100);
            $lootCandidates = $state['loot_candidates'];
            for ($i = 0; $i < $extra; $i++) {
                $lootCandidates[] = $lootCandidates[array_rand($lootCandidates)];
            }
        } else {
            $lootCandidates = $state['loot_candidates'];
        }

        return [
            'result'           => $result,
            'turns'            => $state['turn'],
            'xp_gained'        => $xpGained,
            'gold_gained'      => $goldGained,
            'heroes_state'     => $state['heroes'],
            'trait_triggers'   => $state['trait_triggers'],
            'log'              => $state['log'],
            'loot_candidates'  => $lootCandidates,
            'active_synergies' => $synergyMods['active_synergies'] ?? [],
        ];
    }
}
