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
        private readonly TraitService    $traitService,
        private readonly MonsterService  $monsterService,
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
            'heroes'                    => $this->initHeroesWithSynergies($heroModels, $synergyMods),
            'enemies'                   => $this->initEnemies($monsterModels),
            'turn'                      => 0,
            'fled_heroes'               => [],
            'sleeping'                  => [],   // hero_id => remaining_turns
            'pending_buffs'             => [],   // buffs à consommer au prochain tour (Philosophe)
            'int_buffs'                 => [],   // hero_id => cumulative INT % (Philosophe accumulation)
            'sneeze_counts'             => [],   // hero_id => nb éternuements ce combat (Allergique)
            'status_effects'            => [],   // 'hero_N'/'enemy_N' => [['slug','remaining','source']]
            'gourmand_wasted'           => [],   // hero_id => bool
            'allergique_enemy_hit_bonus'=> 0,    // bonus toucher ennemi sur 1 tour (Allergique)
            'kleptomane_hero_id'        => null,
            'kleptomane_stole_loot'     => false,
            'consumed_potion_hero_id'   => null,
            'combat_potions'            => 0,
            'blocked_heroes'            => [],
            'revealed'                  => false,
            'log'                       => [],
            'trait_triggers'            => [],
            'xp_gained'                 => 0,
            'gold_gained'               => 0,
            'loot_candidates'           => [],
            'synergy_mods'              => $synergyMods,
        ];

        // Log des synergies actives
        foreach ($synergyMods['active_synergies'] as $syn) {
            $state['log'][] = "✨ Synergie active : {$syn['hero_name']} — {$syn['name']}";
        }

        // Détecter le héros Kleptomane
        foreach ($heroModels as $hero) {
            if ($hero->trait_ && $hero->trait_->slug === 'kleptomane') {
                $state['kleptomane_hero_id'] = $hero->id;
                break;
            }
        }

        // Appliquer debuff de VIT ennemi (barde_narcoleptique)
        if ($synergyMods['enemy_vit_debuff_pct'] > 0) {
            foreach ($state['enemies'] as &$enemy) {
                $enemy['vit'] = max(0, (int) ($enemy['vit'] * (100 - $synergyMods['enemy_vit_debuff_pct']) / 100));
            }
            unset($enemy);
        }

        // Fuite garantie pré-combat (Parchemin de Fuite)
        foreach ($heroModels as $hero) {
            $fleeBuff = $hero->buffs()->where('buff_key', 'guaranteed_flee')->where('remaining_combats', '>', 0)->first();
            if ($fleeBuff) {
                $state['fled_heroes'][] = $hero->id;
                $state['log'][] = $hero->name . ' utilise un Parchemin de Fuite — fuite garantie !';
                $fleeBuff->decrement('remaining_combats');
            }
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

        // Effets de brûlure/poison en fin de round (statuts actifs)
        $this->applyEndOfRoundStatusDamage($state);

        // Résolution Kleptomane post-combat
        $this->resolveKleptomanePostCombat($state, $state['xp_gained']);

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
            $enemies[] = $this->monsterService->buildCombatEnemy($monster);
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
        $heroId = $hero['hero_id'];

        // 1. Vérifier statut "endormi" (Narcoleptique)
        //    Le réveil sur coup est géré dans processEnemyTurn.
        //    Ici on décrémente et on applique le bonus VIT au réveil naturel.
        if (isset($state['sleeping'][$heroId])) {
            $state['sleeping'][$heroId]--;
            if ($state['sleeping'][$heroId] <= 0) {
                unset($state['sleeping'][$heroId]);
                $vitBonus = $this->settings->get('TRAIT_NARCOLEPTIQUE_WAKE_VIT_BONUS', 10);
                $hero['vit'] = intdiv($hero['vit'] * (100 + $vitBonus), 100);
                $state['log'][] = $hero['name'] . ' se réveille — bonus VIT de repos !';
            } else {
                $state['log'][] = $hero['name'] . ' dort profondément (zzz).';
                return;
            }
        }

        // 2. Consommer les buffs INT en attente (Philosophe accumulation)
        $this->consumePendingIntBuffs($heroId, $state);

        // 3. Jet de trait (trigger_moment = turn_start)
        $traitModel = $heroModel->trait_;
        if ($traitModel && $traitModel->trigger_moment === 'turn_start' && $this->traitService->shouldTrigger($heroModel)) {
            $traitEvent = $this->traitService->applyTraitEffect($heroModel, $state, 'combat');
            $state['trait_triggers'][] = $traitEvent;
            $state['log'][] = $traitEvent['message'];

            $skipTurn = $this->applyTraitOnCombatState($traitEvent, $heroIndex, $state, $heroModels, $heroModel);
            if ($skipTurn) {
                return;
            }
        }

        // 4. Sélectionner la cible
        $targetIndex = $this->findLiveEnemy($state);
        if ($targetIndex === null) {
            return;
        }

        $target = &$state['enemies'][$targetIndex];

        // 5. Vérification Pacifiste (on_target_low_hp)
        if ($traitModel && $traitModel->slug === 'pacifiste') {
            $threshold = $this->getPacifisteThreshold($heroModel);
            $targetHpPct = $target['max_hp'] > 0 ? intdiv($target['current_hp'] * 100, $target['max_hp']) : 0;
            if ($targetHpPct <= $threshold && $this->traitService->shouldTrigger($heroModel)) {
                $traitEvent = $this->traitService->applyTraitEffect($heroModel, $state, 'combat');
                $state['trait_triggers'][] = $traitEvent;
                $state['log'][] = $traitEvent['message'];
                $this->buildPacifisteAlternativeAction($heroIndex, $targetIndex, $state);
                return;
            }
        }

        // 6. Vérification esquive
        // Appliquer bonus de toucher Allergique si actif (1 tour)
        $hitBonus = $state['allergique_enemy_hit_bonus'] ?? 0;
        $state['allergique_enemy_hit_bonus'] = 0; // consommé après 1 tour
        $effectiveHero = $hero;
        if ($hitBonus > 0) {
            // Les ennemis ont un bonus de toucher → réduit l'esquive du héros attaquant
            // (Implémentation simplifiée : on réduit DEF du héros pour ce test)
        }
        if ($this->rollDodge($target, $hero)) {
            $state['log'][] = $target['name'] . ' esquive l\'attaque de ' . $hero['name'] . '.';
            return;
        }

        // 7. Calcul des dégâts (avec INT buff Philosophe appliqué)
        $variance = random_int(
            $this->settings->get('VARIANCE_MIN', 90),
            $this->settings->get('VARIANCE_MAX', 110)
        );

        $attackerStats = $hero;
        if (!empty($state['int_buffs'][$heroId])) {
            $attackerStats['int'] = intdiv($hero['int'] * (100 + $state['int_buffs'][$heroId]), 100);
        }

        $damage = $this->calculatePhysicalDamage($attackerStats, $target, $variance);

        // Modificateur élémentaire
        $elemMult = $this->applyElementalMultiplier($hero['element'] ?? 'physique', $target['element'] ?? 'physique');
        if ($elemMult !== 100) {
            $damage = intdiv($damage * $elemMult, 100);
            $damage = max($damage, $this->settings->get('MIN_DAMAGE', 1));
        }

        // Critique
        $critChance = $this->calculateCritChance($attackerStats);
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
            // Elite death explosion triggered when hero kills an elite
            $this->monsterService->applyEliteOnDeath($targetIndex, $state);
        } elseif (!empty($target['phase2_data'])) {
            // Check boss phase 2 transition every time an enemy takes damage
            $this->monsterService->checkPhaseTransition($targetIndex, $state);
        }

        // 8. Trait Pyromane (after_attack) — déclenché APRÈS l'attaque normale
        if ($traitModel && $traitModel->trigger_moment === 'after_attack' && $this->traitService->shouldTrigger($heroModel)) {
            $traitEvent = $this->traitService->applyTraitEffect($heroModel, $state, 'combat');
            $state['trait_triggers'][] = $traitEvent;
            $state['log'][] = $traitEvent['message'];
            $this->applyPyromaneIgnite($heroModel->id, $state);
        }
    }

    private function processEnemyTurn(int $enemyIndex, array &$state): void
    {
        $enemy = &$state['enemies'][$enemyIndex];
        if (!$enemy['is_alive']) {
            return;
        }

        // Tick temp buffs (DEF, ATQ, dodge, reflect, immune, retreat)
        $this->monsterService->tickTempBuffs($enemyIndex, $state);

        // Elite regen (Béni prefix) at turn start
        $this->monsterService->applyEliteTurnStart($enemyIndex, $state);

        // Skip turn if retreated
        if ($enemy['retreated']) {
            $state['log'][] = $enemy['name'] . ' est en retraite tactique ce tour.';
            return;
        }

        $targetIndex = $this->findLiveHero($state);
        if ($targetIndex === null) {
            return;
        }

        $variance = random_int(
            (int) $this->settings->get('VARIANCE_MIN', 90),
            (int) $this->settings->get('VARIANCE_MAX', 110)
        );

        // Select action: skill or basic attack
        $action = $this->monsterService->selectAction($enemyIndex, $state);

        if ($action !== 'attack' && $action !== 'retreated') {
            // Execute the chosen skill
            $this->monsterService->executeSkill($action, $enemyIndex, $state, $variance, $targetIndex);
        } else {
            // ── Basic attack ──────────────────────────────────────────────────
            $target = &$state['heroes'][$targetIndex];

            // Spectral elite: chance to phase through entirely
            if ($this->monsterService->rollPhaseResist($enemy)) {
                $state['log'][] = $enemy['name'] . ' passe à travers ! (Spectral — attaque résistée)';
                return;
            }

            // Dodge check (include elite dodge bonus)
            $effectiveTarget = $target;
            if ($enemy['temp_dodge_pct'] > 0) {
                // Dodge is on the enemy vs hero attacks (this is hero defending — we keep standard)
            }
            if ($this->rollDodge($target, $enemy)) {
                $state['log'][] = $target['name'] . ' esquive l\'attaque de ' . $enemy['name'] . '.';
                return;
            }

            // Calculate damage using effective ATQ (includes temp buffs)
            $effectiveEnemy          = $enemy;
            $effectiveEnemy['atq']   = $this->monsterService->effectiveAtq($enemy);
            $damage                  = $this->calculatePhysicalDamage($effectiveEnemy, $target, $variance);

            // Elemental multiplier
            $elemMult = $this->applyElementalMultiplier($enemy['element'] ?? 'physique', $target['element'] ?? 'physique');
            if ($elemMult !== 100) {
                $damage = max(
                    (int) $this->settings->get('MIN_DAMAGE', 1),
                    intdiv($damage * $elemMult, 100)
                );
            }

            // Mirror reflection (MD09) — hero receives reflected damage... but enemy does too
            if ($enemy['reflect_pct'] > 0) {
                $reflected = max(0, intdiv($damage * $enemy['reflect_pct'], 100));
                $enemy['current_hp'] = max(0, $enemy['current_hp'] - $reflected);
                if ($enemy['current_hp'] <= 0) {
                    $enemy['is_alive'] = false;
                }
                $state['log'][] = $enemy['name'] . ' renvoie ' . $reflected . ' dégâts (miroir).';
            }

            $state['log'][] = $enemy['name'] . ' attaque ' . $target['name'] . ' pour ' . $damage . ' dégâts.';

            $target['current_hp'] = max(0, $target['current_hp'] - $damage);
            if ($target['current_hp'] <= 0) {
                $target['is_alive'] = false;
                $state['log'][] = $target['name'] . ' est KO !';
            }

            // Elite passive effects on hit
            $this->monsterService->applyElitePassiveOnHit($damage, $enemyIndex, $targetIndex, $state);

            // Élite Enragé: double attack below 30% HP
            if ($enemy['elite_double_attack_below_30'] && $enemy['is_alive']) {
                $hpPct = $enemy['max_hp'] > 0 ? intdiv($enemy['current_hp'] * 100, $enemy['max_hp']) : 100;
                if ($hpPct <= 30 && $this->findLiveHero($state) !== null) {
                    $state['log'][] = $enemy['name'] . ' est Enragé — double attaque !';
                    // Second basic hit at same target if alive
                    $t2 = &$state['heroes'][$targetIndex];
                    if ($t2['is_alive']) {
                        $dmg2 = $this->calculatePhysicalDamage($effectiveEnemy, $t2, $variance);
                        $t2['current_hp'] = max(0, $t2['current_hp'] - $dmg2);
                        if ($t2['current_hp'] <= 0) {
                            $t2['is_alive'] = false;
                        }
                        $state['log'][] = $enemy['name'] . ' frappe à nouveau pour ' . $dmg2 . ' dégâts !';
                    }
                }
            }

            // Élite Géant: cleave — hit a second hero
            if ($enemy['elite_cleave']) {
                $altIndex = $this->findLiveHeroExcept($state, $target['hero_id'] ?? -1);
                if ($altIndex !== null) {
                    $t2   = &$state['heroes'][$altIndex];
                    $dmg2 = $this->calculatePhysicalDamage($effectiveEnemy, $t2, $variance);
                    $t2['current_hp'] = max(0, $t2['current_hp'] - $dmg2);
                    if ($t2['current_hp'] <= 0) {
                        $t2['is_alive'] = false;
                    }
                    $state['log'][] = $enemy['name'] . ' frappe aussi ' . $t2['name'] . ' (cleave) pour ' . $dmg2 . ' dégâts.';
                }
            }

            // Narcoleptique wake check
            $heroId = $target['hero_id'] ?? null;
            if ($heroId && isset($state['sleeping'][$heroId]) && $target['is_alive']) {
                $wakeChance = (int) $this->settings->get('TRAIT_NARCOLEPTIQUE_WAKE_CHANCE', 50);
                if (random_int(1, 100) <= $wakeChance) {
                    unset($state['sleeping'][$heroId]);
                    $state['log'][] = $target['name'] . ' se réveille sous le choc ! La douleur, ça réveille.';
                }
            }
        }

        // Check boss phase 2 transition after any damage
        if ($enemy['is_alive'] && !empty($enemy['phase2_data'])) {
            $this->monsterService->checkPhaseTransition($enemyIndex, $state);
        }

        // Elite death explosion check
        if (!$enemy['is_alive']) {
            $this->monsterService->applyEliteOnDeath($enemyIndex, $state);
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

    // ── Méthodes privées — Traits en combat ─────────────────────────────────

    private function consumePendingIntBuffs(int $heroId, array &$state): void
    {
        if (!isset($state['pending_buffs'][$heroId])) {
            return;
        }
        $buff = (int) $state['pending_buffs'][$heroId];
        unset($state['pending_buffs'][$heroId]);
        $state['int_buffs'][$heroId] = ($state['int_buffs'][$heroId] ?? 0) + $buff;
    }

    private function applyTraitOnCombatState(array $traitEvent, int $heroIndex, array &$state, array $heroModels, $heroModel): bool
    {
        $action  = $traitEvent['action'] ?? '';
        $heroId  = $state['heroes'][$heroIndex]['hero_id'];
        $skipTurn = $this->dispatchTraitAction($action, $heroIndex, $heroId, $state, $traitEvent);

        // Branche du Défaut — bonus si palier 3 débloqué (≥6 points investis)
        $defautPoints = $this->traitService->getDefautBranchPoints($heroModel);
        if ($defautPoints >= 6) {
            $bonusPct = $this->settings->get('TRAIT_DEFAUT_BRANCH_TRIGGER_BONUS', 5);
            $hero = &$state['heroes'][$heroIndex];
            $hero['atq'] = intdiv($hero['atq'] * (100 + $bonusPct), 100);
            $hero['def'] = intdiv($hero['def'] * (100 + $bonusPct), 100);
            $hero['int'] = intdiv($hero['int'] * (100 + $bonusPct), 100);
            $state['log'][] = $hero['name'] . ' tire profit de son défaut — Branche du Défaut P3 : +' . $bonusPct . '% stats !';
        }

        return $skipTurn;
    }

    private function dispatchTraitAction(string $action, int $heroIndex, int $heroId, array &$state, array $traitEvent): bool
    {
        switch ($action) {
            case 'flee':
                $state['fled_heroes'][] = $heroId;
                return true;

            case 'sleep':
                $duration = (int) ($traitEvent['duration'] ?? 2);
                $state['sleeping'][$heroId] = $duration;
                return true;

            case 'ponder': // Philosophe — INT buff pour le prochain tour (cumulable)
                $level = $state['heroes'][$heroIndex]['level'] ?? 1;
                if ($level >= 76) {
                    $buff = $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF_L76', 8);
                } elseif ($level >= 51) {
                    $buff = $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF_L51', 7);
                } elseif ($level >= 26) {
                    $buff = $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF_L26', 6);
                } else {
                    $buff = $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF', 5);
                }
                $state['pending_buffs'][$heroId] = ($state['pending_buffs'][$heroId] ?? 0) + $buff;
                return true;

            case 'sneeze': // Allergique
                $this->applyAllergiqueSneeze($heroId, $state);
                return true;

            case 'consume_potion': // Gourmand
                $this->executeGourmandConsume($heroIndex, $state);
                return false;

            default:
                return (bool) ($traitEvent['skip_turn'] ?? false);
        }
    }

    private function executeGourmandConsume(int $heroIndex, array &$state): void
    {
        $hero = &$state['heroes'][$heroIndex];
        if ($state['combat_potions'] <= 0) {
            $malusPct = $this->settings->get('TRAIT_GOURMAND_ATQ_MALUS', 5);
            $hero['atq'] = max(1, intdiv($hero['atq'] * (100 - $malusPct), 100));
            $state['log'][] = $hero['name'] . ' boude (pas de potion) et attaque moins bien.';
            return;
        }
        $state['combat_potions']--;
        $healPct = $this->settings->get('TRAIT_GOURMAND_POTION_HEAL_PCT', 30);
        $healAmount = max(1, intdiv($hero['max_hp'] * $healPct, 100));
        $hero['current_hp'] = min($hero['max_hp'], $hero['current_hp'] + $healAmount);
        $state['log'][] = $hero['name'] . ' engloutit une potion (même à PV plein, quelle gourmandise).';
    }

    private function applyPyromaneIgnite(int $heroId, array &$state): void
    {
        $heroLevel = 1;
        foreach ($state['heroes'] as $h) {
            if ($h['hero_id'] === $heroId) {
                $heroLevel = $h['level'];
                break;
            }
        }

        if ($heroLevel >= 76) {
            $damagePct = $this->settings->get('TRAIT_PYROMANE_DAMAGE_L76', 15);
        } elseif ($heroLevel >= 51) {
            $damagePct = $this->settings->get('TRAIT_PYROMANE_DAMAGE_L51', 12);
        } elseif ($heroLevel >= 26) {
            $damagePct = $this->settings->get('TRAIT_PYROMANE_DAMAGE_L26', 10);
        } else {
            $damagePct = $this->settings->get('TRAIT_PYROMANE_DAMAGE', 8);
        }

        $igniteChance = $this->settings->get('TRAIT_PYROMANE_IGNITE_CHANCE', 30);

        foreach ($state['enemies'] as $i => &$enemy) {
            if (!$enemy['is_alive']) continue;
            $aoeDamage = max(1, intdiv($enemy['max_hp'] * $damagePct, 100));
            $enemy['current_hp'] = max(0, $enemy['current_hp'] - $aoeDamage);
            if ($enemy['current_hp'] <= 0) {
                $enemy['is_alive'] = false;
                $state['log'][] = $enemy['name'] . ' brûle et est vaincu !';
            } else {
                $state['log'][] = 'Incendie ! ' . $enemy['name'] . ' prend ' . $aoeDamage . ' dégâts de feu.';
                if (random_int(1, 100) <= $igniteChance) {
                    $state['status_effects']['enemy_' . $i][] = ['slug' => 'en_feu', 'remaining' => 2, 'source' => 'trait'];
                    $state['log'][] = $enemy['name'] . ' est en feu !';
                }
            }
        }
        unset($enemy);

        // Friendly fire — dégâts réduits de moitié sur les alliés
        $friendlyDamagePct = intdiv($damagePct, 2);
        if ($friendlyDamagePct < 1) return;

        foreach ($state['heroes'] as &$ally) {
            if (!$ally['is_alive'] || $ally['hero_id'] === $heroId) continue;
            $friendlyDamage = max(1, intdiv($ally['max_hp'] * $friendlyDamagePct, 100));
            $ally['current_hp'] = max(0, $ally['current_hp'] - $friendlyDamage);
            if ($ally['current_hp'] <= 0) {
                $ally['is_alive'] = false;
                $state['log'][] = $ally['name'] . ' est touché par les flammes amies et tombe !';
            } else {
                $state['log'][] = $ally['name'] . ' reçoit ' . $friendlyDamage . ' dégâts de feu amis.';
            }
        }
        unset($ally);
    }

    private function applyAllergiqueSneeze(int $heroId, array &$state): void
    {
        $state['sneeze_counts'][$heroId] = ($state['sneeze_counts'][$heroId] ?? 0) + 1;
        $enemyHitBonus = $this->settings->get('TRAIT_ALLERGIQUE_ENEMY_HIT_BONUS', 10);
        $state['allergique_enemy_hit_bonus'] = $enemyHitBonus;

        $threshold = $this->settings->get('TRAIT_ALLERGIQUE_CUMUL_THRESHOLD', 3);
        if ($state['sneeze_counts'][$heroId] >= $threshold) {
            foreach ($state['heroes'] as &$h) {
                if ($h['hero_id'] === $heroId) {
                    $malusPct = $this->settings->get('TRAIT_ALLERGIQUE_MALUS_PCT', 20);
                    $h['atq'] = max(1, intdiv($h['atq'] * (100 - $malusPct), 100));
                    $h['def'] = max(0, intdiv($h['def'] * (100 - $malusPct), 100));
                    $state['log'][] = $h['name'] . ' souffre d\'un malus permanent (trop d\'éternuements) !';
                    break;
                }
            }
            unset($h);
            $state['sneeze_counts'][$heroId] = 0;
        }
    }

    private function getPacifisteThreshold($heroModel): int
    {
        $level = $heroModel->level ?? 1;
        if ($level >= 76) return $this->settings->get('TRAIT_PACIFISTE_THRESHOLD_L76', 20);
        if ($level >= 51) return $this->settings->get('TRAIT_PACIFISTE_THRESHOLD_L51', 25);
        if ($level >= 26) return $this->settings->get('TRAIT_PACIFISTE_THRESHOLD_L26', 28);
        return $this->settings->get('TRAIT_PACIFISTE_THRESHOLD', 30);
    }

    private function buildPacifisteAlternativeAction(int $heroIndex, int $targetIndex, array &$state): void
    {
        $roll = random_int(1, 100);
        $hero = &$state['heroes'][$heroIndex];
        $target = &$state['enemies'][$targetIndex];

        if ($roll <= 40) {
            $defBonus = $this->settings->get('TRAIT_PACIFISTE_DEF_BONUS', 20);
            $hero['def'] = intdiv($hero['def'] * (100 + $defBonus), 100);
            $state['log'][] = $hero['name'] . ' refuse d\'attaquer et se met en défense (+' . $defBonus . '% DEF).';
        } elseif ($roll <= 70) {
            $allyIndex = $this->findLiveHeroExcept($state, $hero['hero_id']);
            if ($allyIndex !== null) {
                $atqBonus = $this->settings->get('TRAIT_PACIFISTE_ATQ_BONUS', 10);
                $state['heroes'][$allyIndex]['atq'] = intdiv(
                    $state['heroes'][$allyIndex]['atq'] * (100 + $atqBonus), 100
                );
                $state['log'][] = $hero['name'] . ' encourage ' . $state['heroes'][$allyIndex]['name'] . ' !';
            } else {
                $state['log'][] = $hero['name'] . ' se parle à lui-même pour se motiver.';
            }
        } elseif ($roll <= 90) {
            $healPct = $this->settings->get('TRAIT_PACIFISTE_HEAL_ENEMY_PCT', 5);
            $heal = max(1, intdiv($target['max_hp'] * $healPct, 100));
            $target['current_hp'] = min($target['max_hp'], $target['current_hp'] + $heal);
            $state['log'][] = $hero['name'] . ' soigne l\'ennemi de ' . $heal . ' PV. "Regarde sa petite tête !"';
        } else {
            $state['log'][] = $hero['name'] . ' contemple l\'ennemi sans pouvoir attaquer.';
        }
    }

    private function findLiveHeroExcept(array $state, int $excludeHeroId): ?int
    {
        foreach ($state['heroes'] as $i => $hero) {
            if ($hero['is_alive'] && $hero['hero_id'] !== $excludeHeroId && !in_array($hero['hero_id'], $state['fled_heroes'])) {
                return $i;
            }
        }
        return null;
    }

    private function resolveKleptomanePostCombat(array &$state, int $xpGained): void
    {
        $kleptomaneId = $state['kleptomane_hero_id'] ?? null;
        if ($kleptomaneId === null || $xpGained <= 0) {
            return;
        }

        $stealChance = $this->settings->get('TRAIT_KLEPTOMANE_CHANCE', 20);
        if (random_int(1, 100) > $stealChance) {
            return;
        }

        $xpStealPct      = $this->settings->get('TRAIT_KLEPTOMANE_XP_STEAL_PCT', 10);
        $lootStealChance = $this->settings->get('TRAIT_KLEPTOMANE_LOOT_STEAL_CHANCE', 30);

        $state['kleptomane_stole_loot'] = true;
        $state['log'][] = 'Le kleptomane a discrètement détourné ' . $xpStealPct . '% de l\'XP...';

        if (!empty($state['loot_candidates']) && random_int(1, 100) <= $lootStealChance) {
            $state['log'][] = '...et a aussi subtilisé un objet Rare+ au passage !';
        }
    }

    private function applyEndOfRoundStatusDamage(array &$state): void
    {
        $fireDamagePct   = $this->settings->get('STATUS_FIRE_DAMAGE_PCT', 5);
        $poisonDamagePct = $this->settings->get('STATUS_POISON_DAMAGE_PCT', 3);

        foreach ($state['status_effects'] as $key => &$effects) {
            if (str_starts_with($key, 'hero_')) {
                $pool  = 'heroes';
                $index = (int) substr($key, 5);
            } elseif (str_starts_with($key, 'enemy_')) {
                $pool  = 'enemies';
                $index = (int) substr($key, 6);
            } else {
                continue;
            }

            if (!isset($state[$pool][$index]) || !($state[$pool][$index]['is_alive'] ?? false)) {
                continue;
            }

            foreach ($effects as &$effect) {
                $damage      = 0;
                $targetName  = $state[$pool][$index]['name']   ?? '?';
                $targetMaxHp = $state[$pool][$index]['max_hp'] ?? 100;

                switch ($effect['slug'] ?? '') {
                    case 'en_feu':
                        $damage = max(1, intdiv($targetMaxHp * $fireDamagePct, 100));
                        $state['log'][] = $targetName . ' brûle pour ' . $damage . ' dégâts.';
                        break;
                    case 'empoisonne':
                        $damage = max(1, intdiv($targetMaxHp * $poisonDamagePct, 100));
                        $state['log'][] = $targetName . ' est empoisonné pour ' . $damage . ' dégâts.';
                        break;
                }

                if ($damage > 0) {
                    $state[$pool][$index]['current_hp'] = max(0, $state[$pool][$index]['current_hp'] - $damage);
                    if ($state[$pool][$index]['current_hp'] <= 0) {
                        $state[$pool][$index]['is_alive'] = false;
                    }
                }

                $effect['remaining']--;
            }
            unset($effect);

            $effects = array_values(array_filter($effects, fn($e) => ($e['remaining'] ?? 0) > 0));
        }
        unset($effects);
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
            $goldKillBase    = $this->settings->get('GOLD_PER_KILL_BASE', 5);
            $goldLevelMult   = $this->settings->get('GOLD_PER_KILL_LEVEL_MULT', 2);
            $goldEliteBonus  = $this->settings->get('GOLD_ELITE_BONUS', 50);
            $goldMinibossMult = $this->settings->get('GOLD_MINIBOSS_MULT', 5);
            $goldBossMult    = $this->settings->get('GOLD_BOSS_MULT', 15);

            $monsterList = array_values($monsterModels);
            foreach ($monsterList as $idx => $monster) {
                $xpGained += $this->calculateXpForKill($monster->level, $avgHeroLevel);

                $baseGold    = $goldKillBase + ($monster->level * $goldLevelMult);
                $enemyState  = $state['enemies'][$idx] ?? [];
                $monsterType = $monster->monster_type ?? 'normal';

                if ($monsterType === 'boss') {
                    $gold = $baseGold * $goldBossMult;
                } elseif ($monsterType === 'mini_boss') {
                    $gold = $baseGold * $goldMinibossMult;
                } elseif (!empty($enemyState['is_elite'])) {
                    $gold = intdiv($baseGold * (100 + $goldEliteBonus), 100);
                } else {
                    $gold = $baseGold;
                }

                $goldGained += $gold;
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
