<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Nécromancien — 3 branches × 7 talents.
 * GDD §8. Branches : Maître des Morts (offensive), Flétrisseur (defensive), Nécromancie Ratée (defaut).
 */
class NecromancienTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Maître des Morts — coûts 2,1 | 1,2 | 2,3,3 ───────────
        $t[] = $base + ['name' => 'Squelette Serviteur', 'description' => 'Invoque un squelette (PV 30% du Nécro, ATQ 30% INT). Max 1. CD : 4 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'summon_skeleton', 'hp_pct' => 30, 'atq_pct_int' => 30, 'max_count' => 1, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Os Renforcés', 'description' => 'Invocations +20% PV.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'minion_hp_bonus', 'bonus_pct' => 20])];
        $t[] = $base + ['name' => 'Armée Grandissante', 'description' => 'Max invocations +1 (max 2).', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'max_minions', 'increase' => 1])];
        $t[] = $base + ['name' => 'Golem d\'Os', 'description' => 'Invoque un golem (PV 60% Nécro, ATQ 40% INT, provoque). Remplace les squelettes. CD : 8 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'summon_golem', 'hp_pct' => 60, 'atq_pct_int' => 40, 'taunt' => true, 'cooldown' => 8])];
        $t[] = $base + ['name' => 'Lien Vital', 'description' => 'Quand une invocation meurt, le Nécro récupère 15% PV max.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'soul_bond', 'heal_pct_max' => 15])];
        $t[] = $base + ['name' => 'Sacrifice', 'description' => 'Détruit une invocation pour infliger 200% de ses PV en dégâts. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'sacrifice_minion', 'damage_pct_hp' => 200, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Seigneur Liche', 'description' => 'Max invocations +1 (max 3). +10% ATQ par tour en vie. Quand un ennemi meurt, invoque auto.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'lich_lord', 'max_minions_bonus' => 1, 'atq_per_turn_pct' => 10, 'auto_summon_on_kill' => true])];

        // ── Branche B : Flétrisseur — coûts 2,1 | 2,1 | 2,2,3 ────────────────
        $t[] = $base + ['name' => 'Drain de Vie', 'description' => '100% INT en dégâts et soigne du même montant. CD : 3 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'life_drain', 'damage_pct_int' => 100, 'heal_pct_damage' => 100, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Malédiction', 'description' => 'Soins reçus par la cible -50% pendant 3 tours. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'curse_heal', 'heal_reduction_pct' => 50, 'duration' => 3, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Aura de Mort', 'description' => 'Tous les ennemis perdent 2% PV max par tour.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'death_aura', 'dot_pct_max' => 2])];
        $t[] = $base + ['name' => 'Flétrissure', 'description' => '140% INT + cible -15% DEF pendant 3 tours. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'wither', 'damage_pct_int' => 140, 'def_debuff_pct' => 15, 'duration' => 3, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Pacte de Sang', 'description' => 'Sacrifie 20% PV pour +40% INT pendant 3 tours. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'blood_pact', 'self_damage_pct_max' => 20, 'int_bonus_pct' => 40, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Peste', 'description' => 'Les ennemis empoisonnés transmettent le poison aux adjacents.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'plague_spread'])];
        $t[] = $base + ['name' => 'Faucheur', 'description' => 'Chaque kill +10% INT pour le combat. À 3 kills, les sorts drainent (50% dégâts → soins).', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'reaper', 'int_per_kill_pct' => 10, 'drain_threshold' => 3, 'drain_pct' => 50])];

        // ── Branche C : Nécromancie Ratée (Défaut) — coûts 1,2 | 2,1 | 2,2,3 ─
        $t[] = $base + ['name' => 'Squelette Rebelle', 'description' => 'Quand le trait se déclenche et qu\'une invocation est active, elle attaque un ennemi aléatoire à 150% ATQ.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'rebel_skeleton', 'damage_pct' => 150])];
        $t[] = $base + ['name' => 'Énergie Résiduelle', 'description' => 'Quand le trait se déclenche, bouclier de 15% PV max.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'residual_shield', 'shield_pct_max' => 15])];
        $t[] = $base + ['name' => 'Invocation Instable', 'description' => '10% de chance par tour qu\'une invocation explose : mort + 150% PV en dégâts aux ennemis.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'unstable_minion', 'chance_pct' => 10, 'damage_pct_hp' => 150])];
        $t[] = $base + ['name' => 'Nécro-Accident', 'description' => 'Quand un allié meurt, 40% de chance de le relever comme mort-vivant (20% PV, +30% ATQ, 3 tours).', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'accidental_necro', 'chance_pct' => 40, 'hp_pct' => 20, 'atq_bonus_pct' => 30, 'duration' => 3])];
        $t[] = $base + ['name' => 'Mort Temporaire', 'description' => 'Le Nécro se tue 2 tours. Invocations +50% toutes stats. Revient avec 50% PV. CD : 10 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'temp_death', 'duration' => 2, 'minion_bonus_pct' => 50, 'return_hp_pct' => 50, 'cooldown' => 10])];
        $t[] = $base + ['name' => 'Chaos Nécrotique', 'description' => 'Trait +5% chance. Chaque déclenchement crée un squelette gratuit.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'necrotic_chaos', 'trait_chance_bonus' => 5, 'free_skeleton_per_trigger' => true])];
        $t[] = $base + ['name' => 'Maître de l\'Erreur', 'description' => 'Chaque déclenchement invoque un mort-vivant aléatoire. À 3 déclenchements : "Armée de l\'Incompétence" (5 morts-vivants faibles).', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'error_master', 'army_threshold' => 3, 'army_size' => 5])];

        return $t;
    }
}
