<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Barbare — 3 branches × 7 talents.
 * GDD §7. Branches : Rage (offensive), Brute (defensive), Destruction (defaut).
 */
class BarbareTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Rage (DPS pur) — coûts 1,2 | 2,1 | 2,2,3 ─────────────
        $t[] = $base + ['name' => 'Fureur', 'description' => 'ATQ +15%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'atq', 'bonus_percent' => 15])];
        $t[] = $base + ['name' => 'Frappe Sauvage', 'description' => 'Attaque à 200% ATQ, le Barbare prend 10% PV max en dégâts. CD : 3 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'savage_strike', 'damage_pct' => 200, 'self_damage_pct_max' => 10, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Soif de Sang', 'description' => 'Chaque attaque soigne 5% des dégâts infligés.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'lifesteal', 'heal_pct_damage' => 5])];
        $t[] = $base + ['name' => 'Rage Croissante', 'description' => 'ATQ +3% par tour de combat (cumulable).', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'rising_rage', 'atq_per_turn_pct' => 3])];
        $t[] = $base + ['name' => 'Déchaînement', 'description' => '3 attaques à 80% ATQ sur cibles aléatoires. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'frenzy_strikes', 'strikes' => 3, 'damage_pct' => 80, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Dernier Souffle', 'description' => 'Sous 20% PV, ATQ +50%.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'last_stand', 'hp_threshold_pct' => 20, 'atq_bonus_pct' => 50])];
        $t[] = $base + ['name' => 'Fureur Immortelle', 'description' => 'Quand le Barbare devrait mourir, reste à 1 PV + 100% ATQ pendant 2 tours. 1 fois par combat.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'immortal_rage', 'atq_bonus_pct' => 100, 'duration' => 2, 'uses_per_combat' => 1])];

        // ── Branche B : Brute (Tank offensif) — coûts 1,2 | 2,1 | 2,3,3 ──────
        $t[] = $base + ['name' => 'Masse Imposante', 'description' => 'PV +15%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'hp', 'bonus_percent' => 15])];
        $t[] = $base + ['name' => 'Coup de Tête', 'description' => '120% ATQ + Étourdi cible 1 tour. Le Barbare aussi étourdi 1 tour. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'headbutt', 'damage_pct' => 120, 'stun_duration' => 1, 'self_stun' => 1, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Peau de Pierre', 'description' => 'DEF +10%.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'def', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Cri de Guerre', 'description' => 'Équipe +10% ATQ 3 tours. Le Barbare +20%. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'war_cry', 'team_atq_pct' => 10, 'self_atq_pct' => 20, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Balayage', 'description' => 'Attaque tous les ennemis à 90% ATQ. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'sweep', 'damage_pct' => 90, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Endurci', 'description' => 'Dégâts reçus -10% tant qu\'au-dessus de 50% PV.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'hardened', 'hp_threshold_pct' => 50, 'damage_reduction_pct' => 10])];
        $t[] = $base + ['name' => 'Titan', 'description' => 'PV +25%. Provoque auto l\'ennemi le plus fort. Quand touché, riposte à 40% ATQ.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'stat' => 'hp', 'bonus_percent' => 25, 'action' => 'titan', 'riposte_pct' => 40])];

        // ── Branche C : Destruction (Défaut) — coûts 2,1 | 2,1 | 2,2,3 ───────
        $t[] = $base + ['name' => 'Casse Involontaire', 'description' => 'Quand le trait se déclenche, tous les ennemis prennent 30% ATQ en dégâts.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'on_trait_aoe', 'damage_pct' => 30])];
        $t[] = $base + ['name' => 'Adrénaline', 'description' => 'Quand le trait se déclenche, VIT doublée pour 1 tour.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'adrenaline', 'vit_multiplier' => 2, 'duration' => 1])];
        $t[] = $base + ['name' => 'Fureur Incontrôlable', 'description' => 'Attaque un ennemi aléatoire à 50% ATQ en fin de tour même si étourdi/endormi.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'uncontrolled_fury', 'damage_pct' => 50])];
        $t[] = $base + ['name' => 'Dommages Structurels', 'description' => '20% de chance par attaque de réduire la DEF de la cible de 10% (permanent pour le combat).', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'structural_damage', 'chance_pct' => 20, 'def_reduce_pct' => 10])];
        $t[] = $base + ['name' => 'Tremblement de Terre', 'description' => '120% ATQ sur tous les ennemis. Alliés prennent 5% PV max. CD : 7 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'earthquake', 'damage_pct' => 120, 'ally_damage_pct_max' => 5, 'cooldown' => 7])];
        $t[] = $base + ['name' => 'Instinct Bestial', 'description' => 'Trait +5% chance. Chaque déclenchement : +8% ATQ permanent.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'bestial_instinct', 'trait_chance_bonus' => 5, 'atq_per_trigger_pct' => 8])];
        $t[] = $base + ['name' => 'Force de la Nature', 'description' => 'Chaque déclenchement du trait → onde de choc : 60% ATQ tous les ennemis + 10% chance étourdir. Si Pyromane, inflige "En feu".', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'nature_force', 'damage_pct' => 60, 'stun_chance_pct' => 10, 'pyromane_ignite' => true])];

        return $t;
    }
}
