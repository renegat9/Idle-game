<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Barde — 3 branches × 7 talents.
 * GDD §6. Branches : Virtuose (offensive), Provocateur (defensive), Faux Artiste (defaut).
 */
class BardeTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Virtuose — coûts 1,2 | 2,1 | 2,2,3 ────────────────────
        $t[] = $base + ['name' => 'Mélodie Entraînante', 'description' => 'Les buffs du Barde durent 1 tour de plus.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'buff_duration', 'bonus_turns' => 1])];
        $t[] = $base + ['name' => 'Hymne de Guerre', 'description' => 'Équipe +15% ATQ pendant 3 tours. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'team_atq_buff', 'bonus_pct' => 15, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Ballade Protectrice', 'description' => 'Équipe +15% DEF pendant 3 tours. CD : 5 tours.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'team_def_buff', 'bonus_pct' => 15, 'duration' => 3, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Tempo', 'description' => 'Équipe +10% VIT permanent.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'team_vit_aura', 'bonus_pct' => 10])];
        $t[] = $base + ['name' => 'Rappel Héroïque', 'description' => 'Réinitialise le cooldown d\'une compétence d\'un allié. CD : 8 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'cd_reset', 'cooldown' => 8])];
        $t[] = $base + ['name' => 'Symphonie', 'description' => 'Si le Barde buff 3 alliés dans le même combat, tous les buffs +10% efficacité.', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'symphony_bonus', 'trigger_buffs' => 3, 'efficiency_pct' => 10])];
        $t[] = $base + ['name' => 'Maestro', 'description' => 'Joue auto un buff aléatoire gratuit chaque tour (ATQ, DEF, VIT, ou soin 5% PV max).', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'auto_buff', 'pool' => ['atq', 'def', 'vit', 'heal_5']])];

        // ── Branche B : Provocateur — coûts 2,1 | 1,2 | 2,2,3 ────────────────
        $t[] = $base + ['name' => 'Insulte Cinglante', 'description' => 'Cible -15% ATQ pendant 3 tours. CD : 3 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'enemy_atq_debuff', 'debuff_pct' => 15, 'duration' => 3, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Fausse Note', 'description' => '30% de chance d\'étourdir une cible 1 tour. CD : 3 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'stun_chance', 'chance_pct' => 30, 'duration' => 1, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Complainte', 'description' => 'Ennemis debuffés par le Barde subissent -10% DEF en plus.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'debuff_synergy', 'def_debuff_pct' => 10])];
        $t[] = $base + ['name' => 'Cacophonie', 'description' => 'Tous les ennemis -10% toutes stats pendant 2 tours. CD : 6 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'aoe_debuff', 'debuff_pct' => 10, 'duration' => 2, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Solo Dévastateur', 'description' => 'Dégâts soniques à 150% CHA + Étourdi 1 tour. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'sonic_solo', 'damage_pct_cha' => 150, 'stun_duration' => 1, 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Chanson Maudite', 'description' => 'Les ennemis debuffés ont 15% de chance de s\'entre-attaquer.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'friendly_fire_enemy', 'chance_pct' => 15])];
        $t[] = $base + ['name' => 'Maître de la Discorde', 'description' => 'Les debuffs se propagent : quand un ennemi debuffé meurt, son debuff passe à l\'ennemi le plus proche.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'debuff_spread'])];

        // ── Branche C : Faux Artiste (Défaut) — coûts 2,1 | 2,1 | 2,2,3 ──────
        $t[] = $base + ['name' => 'Public Captif', 'description' => 'Quand le trait se déclenche, ennemis -10% VIT pendant 1 tour.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'on_trait_debuff', 'vit_debuff_pct' => 10, 'duration' => 1])];
        $t[] = $base + ['name' => 'Bis Repetita', 'description' => 'Quand le Barde rate un buff (trait), il le relance au tour suivant sans cooldown.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'redo_buff'])];
        $t[] = $base + ['name' => 'Impro Désastreuse', 'description' => 'Buff ou debuff aléatoire sur cible aléatoire. 60% favorable. CD : 3 tours.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'improv', 'favorable_pct' => 60, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Auto-dérision', 'description' => 'Chaque déclenchement du trait donne +10% CHA pour le combat.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'self_deprecation', 'cha_per_trigger_pct' => 10])];
        $t[] = $base + ['name' => 'Berceuse', 'description' => 'Endort un ennemi 2 tours. Si Narcoleptique, endort TOUS les ennemis 1 tour. CD : 7 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'lullaby', 'sleep_duration' => 2, 'narco_aoe_duration' => 1, 'cooldown' => 7])];
        $t[] = $base + ['name' => 'Standing Ovation', 'description' => 'Si le trait se déclenche 3 fois dans un combat, équipe soignée à 50% PV max.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'ovation_heal', 'trigger_count' => 3, 'heal_pct_max' => 50])];
        $t[] = $base + ['name' => 'Génie Incompris', 'description' => 'Le trait donne un buff aléatoire à toute l\'équipe à chaque déclenchement. +5% par stack dans le même combat.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'misunderstood_genius', 'stack_pct' => 5])];

        return $t;
    }
}
