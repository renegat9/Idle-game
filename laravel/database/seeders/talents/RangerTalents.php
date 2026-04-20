<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Ranger — 3 branches × 7 talents.
 * GDD §4. Branches : Tireur d'Élite (offensive), Survivaliste (defensive), Distrait (defaut).
 */
class RangerTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Tireur d'Élite — coûts 1,2 | 2,1 | 2,3,3 ──────────────
        $t[] = $base + ['name' => 'Visée Stable', 'description' => 'ATQ +10%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'atq', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Tir Précis', 'description' => 'Attaque à 170% ATQ, ne peut pas être esquivée. CD : 3 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'precise_shot', 'damage_pct' => 170, 'ignore_dodge' => true, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Point Faible', 'description' => 'Les critiques ignorent 30% de la DEF de la cible.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'crit_armor_pen', 'pen_pct' => 30])];
        $t[] = $base + ['name' => 'Tir Perforant', 'description' => 'Traverse la cible et touche l\'ennemi derrière à 60%. CD : 4 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'piercing_shot', 'behind_pct' => 60, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Concentration Absolue', 'description' => 'Si non touché pendant un tour, prochain tir +50%.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'untouched_bonus', 'next_shot_pct' => 50])];
        $t[] = $base + ['name' => 'Marque du Chasseur', 'description' => 'Marque un ennemi : équipe +20% dégâts sur la cible pendant 3 tours. CD : 6 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'hunter_mark', 'team_bonus_pct' => 20, 'duration' => 3, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Oeil de Faucon', 'description' => 'Critique +15%. Les critiques infligent 250% au lieu de 150%.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'crit_bonus_percent' => 15, 'crit_damage_multiplier' => 250])];

        // ── Branche B : Survivaliste — coûts 1,2 | 1,2 | 2,2,3 ────────────────
        $t[] = $base + ['name' => 'Peau Tannée', 'description' => 'DEF +10%, PV +5%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'def', 'bonus_percent' => 10, 'hp_bonus_pct' => 5])];
        $t[] = $base + ['name' => 'Piège à Ours', 'description' => 'Prochain attaquant au corps à corps immobilisé 2 tours. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'bear_trap', 'immobilize_duration' => 2, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Herboristerie', 'description' => 'Les potions du Ranger soignent 30% de plus.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'potion_boost', 'bonus_pct' => 30])];
        $t[] = $base + ['name' => 'Compagnon Faucon', 'description' => 'Un faucon attaque chaque tour pour 20% ATQ du Ranger.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'pet_falcon', 'damage_pct_atq' => 20])];
        $t[] = $base + ['name' => 'Terrain Connu', 'description' => '+10% toutes stats dans les zones déjà complétées.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'familiar_terrain', 'bonus_pct' => 10])];
        $t[] = $base + ['name' => 'Pluie de Flèches', 'description' => 'Touche tous les ennemis à 60% ATQ + Ralenti 1 tour. CD : 6 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'arrow_rain', 'damage_pct' => 60, 'slow_duration' => 1, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Seigneur des Bêtes', 'description' => 'Le faucon devient un loup : 40% ATQ/tour + provoque l\'attaquant d\'un allié sous 20% PV.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'pet_wolf', 'damage_pct_atq' => 40, 'ally_hp_threshold_pct' => 20])];

        // ── Branche C : Distrait (Défaut) — coûts 2,1 | 2,1 | 2,2,3 ──────────
        $t[] = $base + ['name' => 'Tir Chanceux', 'description' => 'Quand le Ranger rate, 25% de chance de toucher un autre ennemi à plein dégâts.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'lucky_miss', 'chance_pct' => 25])];
        $t[] = $base + ['name' => 'Instinct', 'description' => 'Quand le trait se déclenche, +20% VIT pendant 2 tours.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'on_trait_trigger', 'stat' => 'vit', 'bonus_percent' => 20, 'duration' => 2])];
        $t[] = $base + ['name' => 'Rebond', 'description' => 'Les tirs ratés ont 15% de chance de ricocher à 70% des dégâts.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'ricochet', 'chance_pct' => 15, 'damage_pct' => 70])];
        $t[] = $base + ['name' => 'Observation Passive', 'description' => 'Quand le Ranger skip un tour (trait), prochain tir +40%.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'skip_bonus', 'next_shot_pct' => 40])];
        $t[] = $base + ['name' => 'Tir dans le Noir', 'description' => 'Cible aléatoire à 200% ATQ. CD : 4 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'random_shot', 'damage_pct' => 200, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Flèche Égarée', 'description' => '10% de chance de tir gratuit supplémentaire sur cible aléatoire à 60% ATQ.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'free_shot', 'chance_pct' => 10, 'damage_pct' => 60])];
        $t[] = $base + ['name' => 'Sniper Somnambule', 'description' => 'Les effets négatifs du trait déclenchent un tir auto à 100% ATQ sur l\'ennemi le plus faible AVANT de s\'appliquer.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'pre_trait_shot', 'damage_pct' => 100, 'target' => 'weakest_enemy'])];

        return $t;
    }
}
