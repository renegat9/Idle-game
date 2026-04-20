<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Voleur — 3 branches × 7 talents.
 * GDD §3. Branches : Assassin (offensive), Ombre (defensive), Filou (defaut).
 */
class VoleurTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Assassin (DPS burst) — coûts 1,2 | 1,2 | 2,2,3 ────────
        $t[] = $base + ['name' => 'Lames Aiguisées', 'description' => 'ATQ +10%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'atq', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Embuscade', 'description' => 'La première attaque du combat inflige 200% des dégâts.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'ambush', 'first_attack_multiplier' => 200])];
        $t[] = $base + ['name' => 'Points Vitaux', 'description' => 'Chance de critique +10%.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['crit_bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Poison', 'description' => 'Prochaine attaque empoisonne (3% PV max/tour pendant 4 tours). CD : 5 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'poison', 'dot_pct_max' => 3, 'duration' => 4, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Ombre Mortelle', 'description' => 'Invisible 1 tour (non ciblable). Prochaine attaque à 250%. CD : 6 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'shadow_strike', 'invisible_duration' => 1, 'next_attack_pct' => 250, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Hémorragie', 'description' => 'Les critiques infligent saignement : 4% PV max/tour pendant 3 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'bleed_on_crit', 'dot_pct_max' => 4, 'duration' => 3])];
        $t[] = $base + ['name' => 'Coup Fatal', 'description' => 'Attaques contre un ennemi sous 15% PV = kills auto. Pas sur les boss.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'execute_auto', 'hp_threshold_pct' => 15, 'exclude_bosses' => true])];

        // ── Branche B : Ombre (Esquive / Utilitaire) — coûts 1,2 | 2,1 | 2,2,3 ─
        $t[] = $base + ['name' => 'Agilité', 'description' => 'VIT +15%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'vit', 'bonus_percent' => 15])];
        $t[] = $base + ['name' => 'Évasion', 'description' => 'Esquive +8%.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['dodge_bonus_percent' => 8])];
        $t[] = $base + ['name' => 'Feinte', 'description' => 'L\'ennemi ciblé rate automatiquement sa prochaine attaque. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'feint', 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Pas de l\'Ombre', 'description' => 'Après une esquive, +30% ATQ sur la prochaine attaque.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'post_dodge_atq', 'bonus_pct' => 30])];
        $t[] = $base + ['name' => 'Double Lame', 'description' => 'Attaque 2 fois par tour, la seconde à 50%.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'double_strike', 'second_pct' => 50])];
        $t[] = $base + ['name' => 'Piège', 'description' => 'Piège invisible. Prochain attaquant étourdi 1 tour + 100% ATQ. CD : 6 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'trap', 'stun_duration' => 1, 'damage_pct' => 100, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Fantôme', 'description' => 'Esquive +15%. Chaque esquive : 30% chance d\'être invisible 1 tour.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'dodge_bonus_percent' => 15, 'action' => 'dodge_invisible', 'invisible_chance_pct' => 30])];

        // ── Branche C : Filou (Défaut) — coûts 2,1 | 2,1 | 2,2,3 ──────────────
        $t[] = $base + ['name' => 'Doigts Agiles', 'description' => '+15% de chance de loot supplémentaire après chaque combat.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'bonus_loot', 'chance_pct' => 15])];
        $t[] = $base + ['name' => 'Pickpocket', 'description' => 'Quand le trait se déclenche, vole un buff de l\'ennemi au lieu de subir le malus.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'steal_buff_on_trait'])];
        $t[] = $base + ['name' => 'Coup Bas', 'description' => 'Si le Voleur agit en dernier dans un tour, ses dégâts sont +40%.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'last_actor_bonus', 'damage_bonus_pct' => 40])];
        $t[] = $base + ['name' => 'Échappatoire', 'description' => 'Si le Voleur fuit, il vole 1 objet à l\'ennemi en partant.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'flee_steal'])];
        $t[] = $base + ['name' => 'Baratineur', 'description' => '10% de chance qu\'un ennemi hésite et skip son tour.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'enemy_skip', 'chance_pct' => 10])];
        $t[] = $base + ['name' => 'Sabotage Discret', 'description' => '-30% DEF d\'un ennemi pendant 3 tours. CD : 5 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'def_debuff', 'debuff_pct' => 30, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Roi des Embrouilles', 'description' => 'Quand le trait se déclenche, un effet positif aléatoire : loot, buff d\'équipe, debuff ennemi, ou soin 20% PV max.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'random_positive', 'pool' => ['loot_bonus', 'team_buff', 'enemy_debuff', 'heal_20'], 'heal_pct_max' => 20])];

        return $t;
    }
}
