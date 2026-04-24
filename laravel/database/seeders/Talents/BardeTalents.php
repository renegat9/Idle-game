<?php

namespace Database\Seeders\Talents;

/**
 * Barde — 21 talents (3 branches × 7).
 * Branche A : Virtuose (Buff/Support)       → 'defensive'
 * Branche B : Provocateur (Contrôle/DPS)   → 'offensive'
 * Branche C : Faux Artiste (Défaut)         → 'defaut'
 * Source : TALENT_TREES.md §6
 */
class BardeTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Virtuose (defensive) ─────────────────────────────
            ['class_id' => $classId, 'name' => 'Mélodie Inspirante', 'description' => 'Toute l\'équipe gagne +10% ATQ pendant 3 tours. CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"team_atq_pct":10,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Chanson de Courage', 'description' => 'L\'allié avec le moins de PV gagne +20% DEF et régénère 5% PV max pendant 3 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"target":"lowest_hp","def_bonus_pct":20,"regen_hp_pct":5,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Harmonie',           'description' => 'CHA +15%. Chaque buff actif sur l\'équipe augmente les soins reçus de 5%.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"cha","percent":15,"heal_per_buff_pct":5}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Contrechant',        'description' => 'Annule le prochain débuff appliqué à l\'équipe. CD : 6 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"negate_next_debuff":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Symphonie de Combat','description' => 'Toute l\'équipe gagne +15% ATQ, +15% DEF, +15% VIT pendant 4 tours. CD : 8 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":8,"team_atq_pct":15,"team_def_pct":15,"team_vit_pct":15,"turns":4}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Ballade de l\'Espoir','description' => 'Quand un allié est K.O., le Barde joue une ballade qui soigne tous les alliés vivants de 25% de leurs PV max.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_ally_death","team_heal_hp_pct":25}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Maître Barde',       'description' => 'CHA +25%. Tous les buffs d\'équipe durent 2 tours de plus. Le Barde est immunisé aux debuffs pendant ses propres performances.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"cha","percent":25,"buff_duration_bonus":2,"immune_debuff_while_active":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche B : Provocateur (offensive) ──────────────────────────
            ['class_id' => $classId, 'name' => 'Insulte Poétique',   'description' => 'Réduit l\'ATQ d\'un ennemi de 20% pendant 3 tours. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"enemy_atq_debuff_pct":20,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Dissonance',         'description' => 'Dégâts soniques à 110% CHA. L\'ennemi est confus et attaque un allié aléatoire au prochain tour. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"dmg_cha_pct":110,"confuse_1turn":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Satire',             'description' => 'Chaque ennemi debuffé augmente les dégâts du Barde de 8%.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"dmg_per_debuffed_enemy_pct":8}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Cacophonie',         'description' => 'Tous les ennemis sont étoudis 1 tour. CD : 7 tours.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":7,"aoe_stun_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Ode au Chaos',       'description' => 'Les ennemis s\'attaquent entre eux pendant 2 tours. CD : 8 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":8,"enemy_friendly_fire_turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Riff Final',         'description' => 'Dégâts soniques à 300% CHA sur une cible. Double si l\'ennemi est debuffé. CD : 6 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"dmg_cha_pct":300,"debuffed_dmg_cha_pct":600}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Légende Vivante',    'description' => 'CHA +25%. Le Barde devient une légende : +30% XP gagné. Les ennemis ont 15% de chance de fuir avant le combat.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"cha","percent":25,"xp_bonus_pct":30,"enemy_flee_chance":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche C : Faux Artiste (defaut) ────────────────────────────
            ['class_id' => $classId, 'name' => 'Fausse Note',        'description' => 'Quand le trait se déclenche, le Barde joue une fausse note : 30% chance d\'étourdir un ennemi aléatoire 1 tour.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","stun_chance":30,"target":"random_enemy","stun_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Inspiration Accidentelle','description' => 'Quand le trait rate un effet, un allié aléatoire gagne +15% ATQ pendant 2 tours.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","random_ally_atq_pct":15,"turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Plagiat',            'description' => 'Copie le dernier effet utilisé par un allié ou ennemi. CD : 5 tours.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"copy_last_effect":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Public Captif',      'description' => 'Quand le trait se déclenche, les ennemis sont si choqués par la médiocrité qu\'ils perdent 1 tour.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","enemy_skip_turn_chance":40}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Improvisation',      'description' => 'Une fois par combat, retente n\'importe quelle action ratée (sort raté, soin raté, attaque manquée).',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"retry_failed_action_once":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Critique Artistique','description' => 'Quand le Barde déclanche son trait, +10% à toutes les stats pour le reste du combat.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","all_stats_pct":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Chef-d\'Œuvre Maudit','description' => 'Quand le trait se déclenche, crée un chef-d\'œuvre maudit : effet aléatoire épique qui affecte tout le monde (alliés et ennemis) de façon imprévisible.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","chaotic_epic_effect":true,"targets":"all"}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],
        ];
    }
}
