<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Guerrier — 3 branches × 7 talents.
 * GDD §1. Branches : Rempart (offensive=Tank), Bras Armé (defensive=DPS), Calamité (defaut).
 */
class GuerrierTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Rempart (Tank) — coûts 1,2 | 1,2 | 2,2,3 ─────────────
        $t[] = $base + ['name' => 'Peau Épaisse', 'description' => 'DEF +10%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'def', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Provocation', 'description' => 'Force tous les ennemis à cibler le Guerrier pendant 2 tours. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'taunt', 'duration' => 2, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Endurance', 'description' => 'PV max +15%.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'hp', 'bonus_percent' => 15])];
        $t[] = $base + ['name' => 'Mur de Bouclier', 'description' => 'Quand un allié tombe sous 20% PV, absorbe 50% des dégâts suivants pendant 1 tour.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'ally_protect', 'trigger_hp_pct' => 20, 'absorb_pct' => 50, 'duration' => 1])];
        $t[] = $base + ['name' => 'Représailles', 'description' => 'Chaque coup reçu riposte pour 30% de l\'ATQ.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'riposte', 'damage_pct' => 30])];
        $t[] = $base + ['name' => 'Cri Intimidant', 'description' => 'Tous les ennemis -20% ATQ pendant 3 tours. CD : 6 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'enemy_debuff', 'stat' => 'atq', 'debuff_pct' => 20, 'duration' => 3, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Forteresse Vivante', 'description' => 'DEF +25%. Au-dessus de 50% PV, l\'équipe gagne +10% DEF.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'stat' => 'def', 'bonus_percent' => 25, 'aura_def_pct' => 10, 'aura_hp_threshold' => 50])];

        // ── Branche B : Bras Armé (DPS) — coûts 1,2 | 1,2 | 2,2,3 ──────────────
        $t[] = $base + ['name' => 'Force Brute', 'description' => 'ATQ +10%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'atq', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Coup Puissant', 'description' => 'Attaque à 180% de l\'ATQ. CD : 3 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'heavy_strike', 'damage_pct' => 180, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Entraînement', 'description' => 'Chance de critique +8%.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['crit_bonus_percent' => 8])];
        $t[] = $base + ['name' => 'Exécution', 'description' => 'Si la cible est sous 25% PV, dégâts doublés.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'execute', 'hp_threshold_pct' => 25, 'damage_multiplier' => 2])];
        $t[] = $base + ['name' => 'Frénésie', 'description' => 'Chaque kill donne +10% ATQ cumulable, max 5 stacks (reset par combat).', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'frenzy', 'atq_per_kill_pct' => 10, 'max_stacks' => 5])];
        $t[] = $base + ['name' => 'Charge Dévastatrice', 'description' => 'Attaque tous les ennemis à 80% ATQ. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'cleave', 'damage_pct' => 80, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Maître d\'Armes', 'description' => 'ATQ +20%. Les critiques infligent 200% au lieu de 150%.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'stat' => 'atq', 'bonus_percent' => 20, 'crit_damage_multiplier' => 200])];

        // ── Branche C : Calamité (Défaut) — coûts 1,2 | 2,1 | 2,2,3 ─────────
        $t[] = $base + ['name' => 'Maladresse Calculée', 'description' => 'Quand un trait négatif se déclenche, +15% ATQ pendant 2 tours.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'on_trait_trigger', 'stat' => 'atq', 'bonus_percent' => 15, 'duration' => 2])];
        $t[] = $base + ['name' => 'Casse-Tout', 'description' => '20% de chance de détruire l\'arme de l\'ennemi (ATQ -30% pour le combat).', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'break_weapon', 'chance_pct' => 20, 'enemy_atq_debuff_pct' => 30])];
        $t[] = $base + ['name' => 'Dommages Collatéraux', 'description' => 'Quand le Guerrier rate, inflige 50% des dégâts à un ennemi adjacent aléatoire.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'collateral', 'damage_pct' => 50])];
        $t[] = $base + ['name' => 'Tête Dure', 'description' => 'Les effets de statut négatifs durent 1 tour de moins.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'status_resistance', 'reduce_duration' => 1])];
        $t[] = $base + ['name' => 'Chaos Tactique', 'description' => 'Après 2 déclenchements du trait dans un combat : +30% ATQ/VIT pendant 3 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'rage', 'trigger_count' => 2, 'atq_bonus_pct' => 30, 'vit_bonus_pct' => 30, 'duration' => 3])];
        $t[] = $base + ['name' => 'Fier Incompétent', 'description' => 'Trait -5% chance. Chaque déclenchement : +5% toutes stats pour le combat.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'trait_scaling', 'trait_chance_reduction' => 5, 'all_stats_per_trigger_pct' => 5])];
        $t[] = $base + ['name' => 'Catastrophe Ambulante', 'description' => 'Les effets du trait affectent aussi l\'ennemi le plus proche.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'trait_mirror_enemy'])];

        return $t;
    }
}
