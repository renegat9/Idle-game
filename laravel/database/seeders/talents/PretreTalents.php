<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Prêtre — 3 branches × 7 talents.
 * GDD §5. Branches : Guérisseur (offensive), Inquisiteur (defensive), Foi Vacillante (defaut).
 */
class PretreTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Guérisseur — coûts 1,2 | 2,1 | 2,3,3 ──────────────────
        $t[] = $base + ['name' => 'Bénédiction', 'description' => 'Soins +15%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'heal_bonus', 'bonus_pct' => 15])];
        $t[] = $base + ['name' => 'Prière Rapide', 'description' => 'Soigne un allié de 120% INT. CD : 2 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'heal_single', 'heal_pct_int' => 120, 'cooldown' => 2])];
        $t[] = $base + ['name' => 'Cercle de Soin', 'description' => 'Soigne toute l\'équipe de 50% INT. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'heal_aoe', 'heal_pct_int' => 50, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Bouclier Sacré', 'description' => 'Un allié gagne un bouclier de 40% INT pendant 3 tours. CD : 4 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'sacred_shield', 'shield_pct_int' => 40, 'duration' => 3, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Résurrection', 'description' => 'Ranime un allié K.O. avec 30% PV max. 1 fois par combat.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'resurrect', 'hp_pct' => 30, 'uses_per_combat' => 1])];
        $t[] = $base + ['name' => 'Aura Sacrée', 'description' => 'Tous les alliés récupèrent 3% PV max par tour.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'team_regen', 'hp_pct_max_per_turn' => 3])];
        $t[] = $base + ['name' => 'Saint Patron', 'description' => 'Quand un allié tomberait à 0 PV, maintient à 1 PV (1×/combat/allié). Soins +20%.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'guardian_save', 'uses_per_ally' => 1, 'heal_bonus_pct' => 20])];

        // ── Branche B : Inquisiteur — coûts 2,1 | 2,1 | 2,2,3 ────────────────
        $t[] = $base + ['name' => 'Châtiment', 'description' => 'Dégâts sacrés à 130% INT. CD : 2 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'smite', 'damage_pct_int' => 130, 'cooldown' => 2])];
        $t[] = $base + ['name' => 'Ferveur', 'description' => 'INT +10%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'int', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Marque Sacrée', 'description' => 'Cible prend +15% de dégâts toutes sources pendant 3 tours. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'sacred_mark', 'damage_taken_bonus_pct' => 15, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Purification', 'description' => 'Retire les effets négatifs d\'un allié ET les inflige à l\'ennemi le plus proche. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'purify_redirect', 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Jugement', 'description' => 'Dégâts = différence entre PV max et PV actuels de la cible. CD : 7 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'judgment', 'damage_formula' => 'hp_delta', 'cooldown' => 7])];
        $t[] = $base + ['name' => 'Fanatisme', 'description' => 'Les attaques sacrées soignent le Prêtre de 20% des dégâts infligés.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'holy_lifesteal', 'heal_pct_damage' => 20])];
        $t[] = $base + ['name' => 'Fléau Divin', 'description' => 'Les sorts font dégâts ET soignent l\'allié le plus faible de 50% des dégâts.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'damage_to_ally_heal', 'heal_pct_damage' => 50])];

        // ── Branche C : Foi Vacillante (Défaut) — coûts 2,1 | 1,2 | 2,2,3 ────
        $t[] = $base + ['name' => 'Prière Confuse', 'description' => 'Quand le trait se déclenche pendant un soin, le soin échoue mais inflige les dégâts équivalents à un ennemi.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'confused_heal'])];
        $t[] = $base + ['name' => 'Martyr', 'description' => '+5% de toutes les stats par allié K.O.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'martyr_scaling', 'bonus_pct_per_ko' => 5])];
        $t[] = $base + ['name' => 'Foi Aveugle', 'description' => 'Les soins ciblent un allié aléatoire mais sont 30% plus puissants.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'random_heal_bonus', 'bonus_pct' => 30])];
        $t[] = $base + ['name' => 'Crise Mystique', 'description' => 'Quand le trait se déclenche, 50% de chance de "Révélation" : prochain sort sans cooldown.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'revelation', 'chance_pct' => 50])];
        $t[] = $base + ['name' => 'Doute Existentiel', 'description' => 'Trait -5% chance. Quand il se déclenche, équipe +10% résistance pendant 2 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'doubt_aura', 'trait_chance_reduction' => 5, 'team_resist_pct' => 10, 'duration' => 2])];
        $t[] = $base + ['name' => 'Miracle Involontaire', 'description' => '5% de chance par tour de lancer un sort gratuit aléatoire.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'random_miracle', 'chance_pct' => 5])];
        $t[] = $base + ['name' => 'Hérétique Sacré', 'description' => 'Le trait donne Inspiré (+20% ATQ/INT) à toute l\'équipe quand il se déclenche.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'heretic_inspire', 'atq_bonus_pct' => 20, 'int_bonus_pct' => 20])];

        return $t;
    }
}
