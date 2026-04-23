<?php

namespace App\Services;

use App\Models\Monster;
use Illuminate\Support\Facades\DB;

/**
 * Handles monster-specific combat logic:
 *   • Elite prefix application (BESTIARY.md §4)
 *   • Monster skill selection & execution (MO01-MO10, MD01-MD10 — BESTIARY.md §3)
 *   • Boss phase 2 transitions
 *   • Elite passive effects (lifesteal, poison-on-hit, regen…)
 */
class MonsterService
{
    // ── Skill definitions (BESTIARY.md §3.1) ─────────────────────────────────

    /** All 20 skills, keyed by ID.  'aoe' = targets all heroes. */
    private const SKILLS = [
        // Offensive
        'MO01' => ['name'=>'Morsure Vicieuse',    'dmg_pct'=>130, 'aoe'=>false, 'target'=>'random',     'poison_pct'=>20, 'poison_turns'=>3],
        'MO02' => ['name'=>'Charge',              'dmg_pct'=>150, 'aoe'=>false, 'target'=>'random',     'self_dmg_pct'=>10],
        'MO03' => ['name'=>'Souffle Élémentaire', 'dmg_pct'=>80,  'aoe'=>true,  'elemental'=>true],
        'MO04' => ['name'=>'Frappe Ciblée',       'dmg_pct'=>180, 'aoe'=>false, 'target'=>'lowest_hp'],
        'MO05' => ['name'=>'Drain',               'dmg_pct'=>100, 'aoe'=>false, 'target'=>'random',     'lifesteal_pct'=>50],
        'MO06' => ['name'=>'Attaque Sournoise',   'dmg_pct'=>120, 'aoe'=>false, 'target'=>'random',     'ignore_def_pct'=>30],
        'MO07' => ['name'=>'Explosion Suicide',   'dmg_pct'=>200, 'aoe'=>true,  'self_destroy'=>true],
        'MO08' => ['name'=>'Griffure Profonde',   'dmg_pct'=>110, 'aoe'=>false, 'target'=>'random',     'bleed_pct'=>3, 'bleed_turns'=>3],
        'MO09' => ['name'=>'Cri Strident',        'dmg_pct'=>60,  'aoe'=>true,  'stun_pct'=>25, 'stun_turns'=>1],
        'MO10' => ['name'=>'Embuscade',           'dmg_pct'=>200, 'aoe'=>false, 'target'=>'random',     'first_turn_only'=>true],
        // Defensive / Support
        'MD01' => ['name'=>'Carapace',            'buff_def_pct'=>50, 'turns'=>2],
        'MD02' => ['name'=>'Régénération',        'heal_pct'=>8],
        'MD03' => ['name'=>'Hurlement',           'buff_atq_pct'=>15, 'turns'=>2, 'allied'=>true],
        'MD04' => ['name'=>'Bouclier Élémentaire','immune_element'=>true, 'turns'=>2],
        'MD05' => ['name'=>'Fuite Tactique',      'retreat_turns'=>1, 'return_atq_pct'=>20],
        'MD06' => ['name'=>'Invocation',          'summon'=>true, 'summon_stats_pct'=>50],
        'MD07' => ['name'=>'Transfert de Vie',    'self_dmg_pct'=>20, 'ally_heal_pct'=>30],
        'MD08' => ['name'=>'Provocation',         'taunt_turns'=>2],
        'MD09' => ['name'=>'Miroir',              'reflect_pct'=>30, 'turns'=>1],
        'MD10' => ['name'=>'Camouflage',          'dodge_bonus_pct'=>30, 'turns'=>2],
    ];

    public function __construct(
        private readonly SettingsService $settings
    ) {}

    // ── Public interface ──────────────────────────────────────────────────────

    /**
     * Build the initial combat enemy dict from a Monster model.
     * Rolls for elite prefix, loads skills, sets temp-buff slots.
     */
    public function buildCombatEnemy(Monster $monster): array
    {
        $base = array_merge($monster->toStatArray(), [
            'is_alive'        => true,
            'monster_id'      => $monster->id,
            'slug'            => $monster->slug ?? '',
            'monster_type'    => $monster->monster_type,
            'phase2_data'     => $monster->phase2_data,
            'phase2_triggered'=> false,
            'max_hp'          => $monster->base_hp,
            'current_hp'      => $monster->base_hp,
            // Temp buffs (per-enemy, decremented per turn)
            'temp_def_pct'    => 0, 'temp_def_turns'   => 0,
            'temp_atq_pct'    => 0, 'temp_atq_turns'   => 0,
            'temp_dodge_pct'  => 0, 'temp_dodge_turns' => 0,
            'reflect_pct'     => 0, 'reflect_turns'    => 0,
            'immune_element'  => false, 'immune_turns'  => 0,
            'retreated'       => false, 'retreat_turns' => 0, 'retreat_atq_bonus' => 0,
            // Elite data (null = not elite)
            'elite_prefix'    => null,
            'elite_lifesteal' => 0,
            'elite_poison_on_hit' => false,
            'elite_poison_pct' => 0,
            'elite_cleave'    => false,
            'elite_phase_chance' => 0,
            'elite_regen_pct' => 0,
            'elite_death_explosion' => false,
            'elite_explosion_pct' => 0,
            'elite_double_attack_below_30' => false,
            'elite_reduce_crit_pct' => 0,
            // Skills loaded from DB
            'combat_skills'   => $this->loadMonsterSkills($monster->id),
            'skill_cooldowns' => [],
            'mo10_used'       => false,
        ]);

        return $this->maybeApplyElitePrefix($base);
    }

    /**
     * Decrement temp buff counters at the start of each enemy turn.
     */
    public function tickTempBuffs(int $enemyIndex, array &$state): void
    {
        $e = &$state['enemies'][$enemyIndex];

        foreach (['temp_def', 'temp_atq', 'temp_dodge', 'reflect', 'immune'] as $key) {
            $turnsKey = $key . '_turns';
            if (isset($e[$turnsKey]) && $e[$turnsKey] > 0) {
                $e[$turnsKey]--;
                if ($e[$turnsKey] <= 0) {
                    $pctKey = ($key === 'immune') ? 'immune_element' : $key . '_pct';
                    if (isset($e[$pctKey])) {
                        $e[$pctKey] = ($key === 'immune') ? false : 0;
                    }
                }
            }
        }

        // Handle retreat return
        if ($e['retreated']) {
            $e['retreat_turns']--;
            if ($e['retreat_turns'] <= 0) {
                $e['retreated']     = false;
                $e['temp_atq_pct'] += $e['retreat_atq_bonus'];
                $e['temp_atq_turns'] = 1;
                $state['log'][] = $e['name'] . ' revient avec +' . $e['retreat_atq_bonus'] . '% ATQ furieux !';
            }
        }

        // Tick skill cooldowns
        foreach ($e['skill_cooldowns'] as $key => &$cd) {
            if ($cd > 0) {
                $cd--;
            }
        }
        unset($cd);
    }

    /**
     * Apply elite passive at turn start: regen for 'beni' prefix.
     */
    public function applyEliteTurnStart(int $enemyIndex, array &$state): void
    {
        $e = &$state['enemies'][$enemyIndex];
        if ($e['elite_regen_pct'] > 0 && $e['is_alive']) {
            $heal = max(1, intdiv($e['max_hp'] * $e['elite_regen_pct'], 100));
            $e['current_hp'] = min($e['max_hp'], $e['current_hp'] + $heal);
            $state['log'][] = $e['name'] . ' régénère ' . $heal . ' PV (élite béni).';
        }
    }

    /**
     * Select the action for this turn.
     * Returns 'MO01'|...'MD10'|'attack'.
     */
    public function selectAction(int $enemyIndex, array $state): string
    {
        $e = $state['enemies'][$enemyIndex];

        if ($e['retreated']) {
            return 'retreated';
        }

        foreach ($e['combat_skills'] as $skill) {
            $key = $skill['skill_key'];
            $def = self::SKILLS[$key] ?? null;
            if (!$def) {
                continue;
            }

            // MO10: first-turn only
            if (($def['first_turn_only'] ?? false) && ($e['mo10_used'] || $state['turn'] > 1)) {
                continue;
            }

            // Check cooldown
            if (($e['skill_cooldowns'][$key] ?? 0) > 0) {
                continue;
            }

            // Roll use_chance
            if (rand(1, 100) <= ($skill['use_chance'] ?? 40)) {
                return $key;
            }
        }

        return 'attack';
    }

    /**
     * Execute a skill key on the combat state.
     * $variance : the combat variance roll (85-115).
     */
    public function executeSkill(
        string $skillKey,
        int    $enemyIndex,
        array  &$state,
        int    $variance,
        int    $defaultTargetIndex
    ): void {
        $def = self::SKILLS[$skillKey] ?? null;
        if (!$def) {
            return;
        }

        $e = &$state['enemies'][$enemyIndex];

        // Set cooldown
        $cd = (int) $this->settings->get('MONSTER_SKILL_COOLDOWN_MIN', 2);
        foreach ($e['combat_skills'] as $sk) {
            if ($sk['skill_key'] === $skillKey) {
                $cd = $sk['cooldown_turns'] ?? $cd;
                break;
            }
        }
        $e['skill_cooldowns'][$skillKey] = $cd;

        // MO10 marker
        if ($skillKey === 'MO10') {
            $e['mo10_used'] = true;
        }

        $state['log'][] = $e['name'] . ' utilise ' . ($def['name'] ?? $skillKey) . ' !';

        // ── Offensive skills ──────────────────────────────────────────────────

        if (str_starts_with($skillKey, 'MO')) {
            $this->executeOffensiveSkill($skillKey, $def, $enemyIndex, $defaultTargetIndex, $variance, $state);
            return;
        }

        // ── Defensive / Support skills ────────────────────────────────────────
        $this->executeDefensiveSkill($skillKey, $def, $enemyIndex, $state);
    }

    /**
     * Apply elite passive effects after a successful hit.
     */
    public function applyElitePassiveOnHit(
        int   $damage,
        int   $enemyIndex,
        int   $targetIndex,
        array &$state
    ): void {
        $e   = &$state['enemies'][$enemyIndex];
        $t   = &$state['heroes'][$targetIndex];

        // Lifesteal (vampirique)
        if ($e['elite_lifesteal'] > 0) {
            $heal = max(1, intdiv($damage * $e['elite_lifesteal'], 100));
            $e['current_hp'] = min($e['max_hp'], $e['current_hp'] + $heal);
        }

        // Poison on hit (toxique)
        if ($e['elite_poison_on_hit']) {
            $statusKey = 'hero_' . $targetIndex;
            $state['status_effects'][$statusKey][] = [
                'slug'      => 'poison',
                'remaining' => 3,
                'source'    => $e['name'],
                'value'     => $e['elite_poison_pct'],
            ];
        }
    }

    /**
     * Apply elite passive on death: death explosion (explosif).
     */
    public function applyEliteOnDeath(int $enemyIndex, array &$state): void
    {
        $e = $state['enemies'][$enemyIndex];
        if (!$e['elite_death_explosion']) {
            return;
        }
        $dmgPct = $e['elite_explosion_pct'];
        $state['log'][] = $e['name'] . ' EXPLOSE ! (élite explosif)';
        foreach ($state['heroes'] as $hi => &$hero) {
            if (!$hero['is_alive']) {
                continue;
            }
            $dmg = max(1, intdiv($e['atq'] * $dmgPct, 100));
            $hero['current_hp'] = max(0, $hero['current_hp'] - $dmg);
            if ($hero['current_hp'] <= 0) {
                $hero['is_alive'] = false;
            }
            $state['log'][] = $hero['name'] . ' prend ' . $dmg . ' dégâts d\'explosion !';
        }
        unset($hero);
    }

    /**
     * Check if an enemy should transition to phase 2.
     * Applies stat changes from phase2_data when threshold is crossed.
     */
    public function checkPhaseTransition(int $enemyIndex, array &$state): void
    {
        $e = &$state['enemies'][$enemyIndex];
        if ($e['phase2_triggered'] || empty($e['phase2_data'])) {
            return;
        }

        $phaseData  = $e['phase2_data'];
        $threshold  = (int) ($phaseData['hp_threshold'] ?? $this->settings->get('BOSS_PHASE_HP_THRESHOLD', 50));
        $hpPct      = $e['max_hp'] > 0 ? intdiv($e['current_hp'] * 100, $e['max_hp']) : 0;

        if ($hpPct > $threshold) {
            return;
        }

        $e['phase2_triggered'] = true;
        $statMult              = (int) ($phaseData['stat_multiplier'] ?? 130);

        // Apply stat multiplier (integer, min 1)
        foreach (['atq', 'def', 'vit', 'int', 'cha'] as $stat) {
            $e[$stat] = max(1, intdiv($e[$stat] * $statMult, 100));
        }

        $narratorText = $phaseData['narrator_text'] ?? $e['name'] . ' est en rage !';
        $state['log'][] = '⚡ PHASE 2 — ' . $narratorText;
    }

    /**
     * Compute effective ATQ and DEF including temp buffs.
     */
    public function effectiveAtq(array $enemy): int
    {
        $base = $enemy['atq'];
        if ($enemy['temp_atq_pct'] > 0) {
            $base = intdiv($base * (100 + $enemy['temp_atq_pct']), 100);
        }
        return max(1, $base);
    }

    public function effectiveDef(array $enemy): int
    {
        $base = $enemy['def'];
        if ($enemy['temp_def_pct'] > 0) {
            $base = intdiv($base * (100 + $enemy['temp_def_pct']), 100);
        }
        return max(0, $base);
    }

    /**
     * Dodge bonus for heroes attacking this enemy (for spectral phase chance).
     */
    public function rollPhaseResist(array $enemy): bool
    {
        return $enemy['elite_phase_chance'] > 0 && rand(1, 100) <= $enemy['elite_phase_chance'];
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function loadMonsterSkills(int $monsterId): array
    {
        return DB::table('monster_skills')
            ->where('monster_id', $monsterId)
            ->select('id', 'name', 'skill_type', 'damage_percent', 'cooldown_turns', 'use_chance', 'effect_data')
            ->get()
            ->map(function ($row) {
                $effectData = is_string($row->effect_data) ? json_decode($row->effect_data, true) : (array) $row->effect_data;

                // Try to map DB skill to an MO/MD key by name matching
                $skillKey = $this->matchSkillKey($row->name, $row->skill_type, $effectData);

                return [
                    'skill_key'     => $skillKey,
                    'name'          => $row->name,
                    'damage_percent'=> (int) $row->damage_percent,
                    'cooldown_turns'=> (int) $row->cooldown_turns,
                    'use_chance'    => (int) $row->use_chance,
                    'effect_data'   => $effectData,
                ];
            })
            ->filter(fn($s) => $s['skill_key'] !== null)
            ->values()
            ->toArray();
    }

    private function matchSkillKey(string $name, string $type, array $effectData): ?string
    {
        // Map known DB skill names to canonical MO/MD keys
        $nameMap = [
            'Morsure Vicieuse'     => 'MO01', 'Morsure Poison' => 'MO01',
            'Charge'               => 'MO02', 'Charge Bestiale' => 'MO02',
            'Souffle Élémentaire'  => 'MO03', 'Souffle Feu'    => 'MO03', 'Souffle Poison' => 'MO03',
            'Frappe Ciblée'        => 'MO04',
            'Drain'                => 'MO05',
            'Attaque Sournoise'    => 'MO06',
            'Explosion Suicide'    => 'MO07',
            'Griffure Profonde'    => 'MO08',
            'Cri Strident'         => 'MO09',
            'Embuscade'            => 'MO10',
            'Carapace'             => 'MD01',
            'Régénération'         => 'MD02', 'Régénération Trollique' => 'MD02',
            'Hurlement'            => 'MD03',
            'Bouclier Élémentaire' => 'MD04',
            'Fuite Tactique'       => 'MD05',
            'Invocation'           => 'MD06',
            'Transfert de Vie'     => 'MD07',
            'Provocation'          => 'MD08',
            'Miroir'               => 'MD09',
            'Camouflage'           => 'MD10',
        ];

        foreach ($nameMap as $pattern => $key) {
            if (str_contains($name, $pattern) || $name === $pattern) {
                return $key;
            }
        }

        // Fallback: guess from skill_type and effect_data
        if ($type === 'attaque' && !empty($effectData['targets'])) {
            return 'MO03'; // AoE offensive → Souffle
        }

        return null;
    }

    private function maybeApplyElitePrefix(array $enemy): array
    {
        $chance = (int) $this->settings->get('MONSTER_ELITE_CHANCE', 8);
        if (rand(1, 100) > $chance) {
            return $enemy;
        }

        $prefix = DB::table('elite_prefixes')->inRandomOrder()->first();
        if (!$prefix) {
            return $enemy;
        }

        $effectData = is_string($prefix->effect_data) ? json_decode($prefix->effect_data, true) : (array) $prefix->effect_data;

        // Apply stat multipliers
        $statMult = (int) $this->settings->get('MONSTER_ELITE_STAT_MULT', 150);
        foreach (['hp', 'atq', 'def', 'vit', 'int', 'cha'] as $stat) {
            $colMult  = $prefix->{$stat . '_multiplier'} ?? 100;
            $combined = intdiv($statMult * $colMult, 100);
            $enemy[$stat] = max(1, intdiv($enemy[$stat] * $combined, 10000));
        }
        $enemy['current_hp'] = $enemy['hp'];
        $enemy['max_hp']     = $enemy['hp'];
        $enemy['name']       = $prefix->name . ' ' . $enemy['name'];
        $enemy['elite_prefix'] = $prefix->slug;

        // Map prefix effects to fields
        $effect = $effectData['effect'] ?? '';
        match ($effect) {
            'double_attack'       => $enemy['elite_double_attack_below_30'] = true,
            'reduce_crit_received'=> $enemy['elite_reduce_crit_pct'] = (int) ($effectData['value'] ?? 50),
            'always_first'        => $enemy['temp_dodge_pct'] = (int) ($effectData['dodge_bonus'] ?? 15),
            'lifesteal'           => $enemy['elite_lifesteal'] = (int) ($effectData['value'] ?? 20),
            'poison_on_hit'       => ($enemy['elite_poison_on_hit'] = true) && ($enemy['elite_poison_pct'] = (int) ($effectData['dot_percent'] ?? 2)),
            'cleave'              => $enemy['elite_cleave'] = true,
            'phase_chance'        => $enemy['elite_phase_chance'] = (int) ($effectData['value'] ?? 25),
            'regen'               => $enemy['elite_regen_pct'] = (int) ($effectData['value'] ?? 3),
            'death_explosion'     => ($enemy['elite_death_explosion'] = true) && ($enemy['elite_explosion_pct'] = (int) ($effectData['dmg_percent'] ?? 100)),
            default               => null,
        };

        // VIT multiplier for 'rapide' and 'geant'
        if (isset($effectData['vit_multiplier'])) {
            $enemy['vit'] = max(1, intdiv($enemy['vit'] * (int) $effectData['vit_multiplier'], 100));
        }

        return $enemy;
    }

    private function executeOffensiveSkill(
        string $skillKey,
        array  $def,
        int    $enemyIndex,
        int    $defaultTargetIndex,
        int    $variance,
        array  &$state
    ): void {
        $e = &$state['enemies'][$enemyIndex];

        // MO07 — Suicide Explosion: destroy self
        if ($skillKey === 'MO07') {
            $e['current_hp'] = 0;
            $e['is_alive']   = false;
            $state['log'][]  = $e['name'] . ' se sacrifie dans une explosion !';
        }

        $dmgPct    = (int) ($def['dmg_pct'] ?? 100);
        $attackAtq = $this->effectiveAtq($e);

        // Determine targets
        $targets = [];
        if ($def['aoe'] ?? false) {
            foreach ($state['heroes'] as $hi => $hero) {
                if ($hero['is_alive']) {
                    $targets[] = $hi;
                }
            }
        } else {
            $ti = $this->resolveOffensiveTarget($def['target'] ?? 'random', $state);
            if ($ti !== null) {
                $targets[] = $ti;
            }
        }

        foreach ($targets as $ti) {
            $hero = &$state['heroes'][$ti];
            if (!$hero['is_alive']) {
                continue;
            }

            $defVal = ($def['ignore_def_pct'] ?? 0) > 0
                ? intdiv($hero['def'] * (100 - (int) $def['ignore_def_pct']), 100)
                : $hero['def'];

            $raw    = intdiv($attackAtq * $dmgPct * $variance, 10000);
            $defMod = max(0, $hero['def'] - $defVal);  // ignored portion (not used here — defVal used directly)

            // Simplified DEF formula (same as CombatService)
            $softCap  = 200;
            $hardCap  = 75;
            $denominator = $defVal + $softCap;
            $reduction   = $denominator > 0 ? min(intdiv($defVal * 100, $denominator), $hardCap) : 0;
            $dmg         = max(1, intdiv(intdiv($attackAtq * $dmgPct, 100) * $variance * (100 - $reduction), 10000));

            $hero['current_hp'] = max(0, $hero['current_hp'] - $dmg);
            if ($hero['current_hp'] <= 0) {
                $hero['is_alive'] = false;
                $state['log'][] = $hero['name'] . ' est KO suite à ' . ($def['name'] ?? $skillKey) . ' !';
            } else {
                $state['log'][] = $hero['name'] . ' subit ' . $dmg . ' dégâts.';
            }

            // Apply skill side effects
            if (!empty($def['poison_pct']) && rand(1, 100) <= $def['poison_pct']) {
                $state['status_effects']['hero_' . $ti][] = [
                    'slug' => 'poison', 'remaining' => (int) $def['poison_turns'],
                    'source' => $e['name'], 'value' => 3,
                ];
                $state['log'][] = $hero['name'] . ' est empoisonné !';
            }

            if (!empty($def['bleed_pct'])) {
                $state['status_effects']['hero_' . $ti][] = [
                    'slug' => 'bleed', 'remaining' => (int) $def['bleed_turns'],
                    'source' => $e['name'], 'value' => (int) $def['bleed_pct'],
                ];
                $state['log'][] = $hero['name'] . ' saigne !';
            }

            if (!empty($def['stun_pct']) && rand(1, 100) <= $def['stun_pct']) {
                $state['status_effects']['hero_' . $ti][] = [
                    'slug' => 'stun', 'remaining' => (int) $def['stun_turns'],
                    'source' => $e['name'], 'value' => 0,
                ];
                $state['log'][] = $hero['name'] . ' est étourdi !';
            }

            // MO05 lifesteal
            if (!empty($def['lifesteal_pct'])) {
                $heal = max(1, intdiv($dmg * (int) $def['lifesteal_pct'], 100));
                $e['current_hp'] = min($e['max_hp'], $e['current_hp'] + $heal);
                $state['log'][] = $e['name'] . ' draine ' . $heal . ' PV.';
            }
        }

        // MO02 self-damage
        if (!empty($def['self_dmg_pct'])) {
            $selfDmg = max(1, intdiv($e['max_hp'] * (int) $def['self_dmg_pct'], 100));
            $e['current_hp'] = max(0, $e['current_hp'] - $selfDmg);
            $state['log'][] = $e['name'] . ' se blesse de ' . $selfDmg . ' PV dans sa charge.';
        }
    }

    private function executeDefensiveSkill(
        string $skillKey,
        array  $def,
        int    $enemyIndex,
        array  &$state
    ): void {
        $e = &$state['enemies'][$enemyIndex];

        switch ($skillKey) {
            case 'MD01': // Carapace — +50% DEF 2 turns
                $e['temp_def_pct']   = (int) ($def['buff_def_pct'] ?? 50);
                $e['temp_def_turns'] = (int) ($def['turns'] ?? 2);
                $state['log'][] = $e['name'] . ' se carapace : DEF +' . $e['temp_def_pct'] . '% pendant ' . $e['temp_def_turns'] . ' tours.';
                break;

            case 'MD02': // Régénération — heal 8% max HP
                $heal = max(1, intdiv($e['max_hp'] * (int) ($def['heal_pct'] ?? 8), 100));
                $e['current_hp'] = min($e['max_hp'], $e['current_hp'] + $heal);
                $state['log'][] = $e['name'] . ' se régénère de ' . $heal . ' PV.';
                break;

            case 'MD03': // Hurlement — all allied enemies +15% ATQ 2 turns
                $bonus = (int) ($def['buff_atq_pct'] ?? 15);
                $turns = (int) ($def['turns'] ?? 2);
                foreach ($state['enemies'] as $ei => &$ally) {
                    if ($ally['is_alive']) {
                        $ally['temp_atq_pct']   = $bonus;
                        $ally['temp_atq_turns'] = $turns;
                    }
                }
                unset($ally);
                $state['log'][] = $e['name'] . ' hurle ! Tous les ennemis +' . $bonus . '% ATQ pendant ' . $turns . ' tours.';
                break;

            case 'MD04': // Bouclier Élémentaire — immune to own element 2 turns
                $e['immune_element'] = true;
                $e['immune_turns']   = (int) ($def['turns'] ?? 2);
                $state['log'][] = $e['name'] . ' s\'entoure d\'un bouclier élémentaire (2 tours).';
                break;

            case 'MD05': // Fuite Tactique — invulnerable 1 turn, returns with ATQ +20%
                $e['retreated']         = true;
                $e['retreat_turns']     = (int) ($def['retreat_turns'] ?? 1);
                $e['retreat_atq_bonus'] = (int) ($def['return_atq_pct'] ?? 20);
                $state['log'][] = $e['name'] . ' se retire tactiquement (invulnérable 1 tour).';
                break;

            case 'MD06': // Invocation — log only (full summon requires encounter logic)
                $state['log'][] = $e['name'] . ' tente d\'invoquer un allié ! (renfort attendu)';
                break;

            case 'MD07': // Transfert de Vie — self -20% max HP, ally +30%
                $selfLoss = max(1, intdiv($e['max_hp'] * (int) ($def['self_dmg_pct'] ?? 20), 100));
                $e['current_hp'] = max(0, $e['current_hp'] - $selfLoss);
                if ($e['current_hp'] <= 0) {
                    $e['is_alive'] = false;
                }
                // Pick an ally to heal
                foreach ($state['enemies'] as $ei => &$ally) {
                    if ($ally['is_alive'] && $ei !== $enemyIndex) {
                        $heal = max(1, intdiv($ally['max_hp'] * (int) ($def['ally_heal_pct'] ?? 30), 100));
                        $ally['current_hp'] = min($ally['max_hp'], $ally['current_hp'] + $heal);
                        $state['log'][] = $e['name'] . ' transfère ' . $heal . ' PV à ' . $ally['name'] . '.';
                        break;
                    }
                }
                unset($ally);
                break;

            case 'MD08': // Provocation — force a hero to target this enemy (narrative log only)
                $state['log'][] = $e['name'] . ' provoque ! Un héros est forcé de l\'attaquer.';
                break;

            case 'MD09': // Miroir — reflect 30% dmg 1 turn
                $e['reflect_pct']   = (int) ($def['reflect_pct'] ?? 30);
                $e['reflect_turns'] = (int) ($def['turns'] ?? 1);
                $state['log'][] = $e['name'] . ' active son miroir (renvoie ' . $e['reflect_pct'] . '% dégâts, 1 tour).';
                break;

            case 'MD10': // Camouflage — +30% dodge 2 turns
                $e['temp_dodge_pct']   = (int) ($def['dodge_bonus_pct'] ?? 30);
                $e['temp_dodge_turns'] = (int) ($def['turns'] ?? 2);
                $state['log'][] = $e['name'] . ' se camoufle (+' . $e['temp_dodge_pct'] . '% esquive, 2 tours).';
                break;
        }
    }

    private function resolveOffensiveTarget(string $targetMode, array $state): ?int
    {
        $alive = array_filter($state['heroes'], fn($h) => $h['is_alive']);
        if (empty($alive)) {
            return null;
        }

        if ($targetMode === 'lowest_hp') {
            $lowestIdx = null;
            $lowestHp  = PHP_INT_MAX;
            foreach ($alive as $i => $hero) {
                if ($hero['current_hp'] < $lowestHp) {
                    $lowestHp  = $hero['current_hp'];
                    $lowestIdx = $i;
                }
            }
            return $lowestIdx;
        }

        $keys = array_keys($alive);
        return $keys[array_rand($keys)];
    }
}
