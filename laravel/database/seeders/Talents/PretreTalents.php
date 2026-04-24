<?php

namespace Database\Seeders\Talents;

/**
 * Pretre — 21 talents (3 branches × 7).
 * Branche A : Guérisseur (Soin/Support)     → 'defensive'
 * Branche B : Inquisiteur (DPS sacré)       → 'offensive'
 * Branche C : Foi Vacillante (Défaut)       → 'defaut'
 * Source : TALENT_TREES.md §5
 */
class PretreTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Guérisseur (defensive) ───────────────────────────
            ['class_id' => $classId, 'name' => 'Toucher Sacré',      'description' => 'Soigne un allié de 15% de ses PV max. CD : 3 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"heal_target_hp_pct":15}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Bénédiction',        'description' => 'Toute l\'équipe gagne +10% à toutes les stats pendant 3 tours. CD : 6 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"team_all_stats_pct":10,"turns":3}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Régénération',       'description' => 'L\'allié le plus blessé récupère 5% de ses PV max chaque tour pendant 3 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"regen_hp_pct":5,"turns":3,"target":"lowest_hp"}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Purification',       'description' => 'Retire tous les effets négatifs de toute l\'équipe. CD : 5 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"cleanse_team":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Résurrection',       'description' => 'Si un allié meurt, le Prêtre peut le ressusciter à 30% de ses PV. CD : 8 tours (une fois par allié).',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_ally_death","revive_hp_pct":30,"cooldown":8}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Sanctuaire',         'description' => 'Toute l\'équipe gagne un bouclier absorbant 20% de ses PV max. CD : 7 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":7,"team_shield_hp_pct":20}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Grand Prêtre',       'description' => 'Les soins sont doublés. Quand le Prêtre soigne un allié à plus de 80% PV, les soins excédentaires se convertissent en dégâts sacrés sur un ennemi.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"heal_mult":2,"overflow_heal_to_dmg":true,"overflow_threshold":80}', 'prerequisite_talent_id' => null],

            // ── Branche B : Inquisiteur (offensive) ──────────────────────────
            ['class_id' => $classId, 'name' => 'Jugement Sacré',     'description' => 'Dégâts sacrés à 130% de l\'ATQ sur une cible. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"dmg_pct":130,"dmg_type":"sacre"}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Châtiment',          'description' => 'Les ennemis morts-vivants ou démoniaques reçoivent 50% de dégâts supplémentaires.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"bonus_vs_undead_demon_pct":50}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Ferveur',            'description' => 'ATQ +15% après chaque soin effectué (max 3 stacks). Reset chaque combat.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_heal","atq_stack_pct":15,"max_stacks":3}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Malédiction Divine', 'description' => 'Inflige "Maudit" à un ennemi : -20% ATQ et DEF pendant 4 tours. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"status":"maudit","atq_debuff_pct":20,"def_debuff_pct":20,"turns":4}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Lumière Aveuglante', 'description' => 'Aveugle tous les ennemis (taux de toucher -30%) pendant 2 tours. CD : 6 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"enemy_hit_debuff_pct":30,"turns":2,"aoe":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Smite',              'description' => 'Dégâts sacrés à 250% de l\'ATQ sur une cible. Si la cible est maudite, inflige 400%. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"dmg_pct":250,"maudit_dmg_pct":400}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Inquisiteur Suprême','description' => 'ATQ +20%. Châtiment s\'applique à TOUS les types d\'ennemis (+20% dégâts universel).',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":20,"universal_dmg_bonus_pct":20}', 'prerequisite_talent_id' => null],

            // ── Branche C : Foi Vacillante (defaut) ──────────────────────────
            ['class_id' => $classId, 'name' => 'Prière Hasardeuse',  'description' => 'Quand le trait se déclenche, 50% chance : soin à 15% PV max. 50% chance : dégâts à 15% PV max au Prêtre.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","random_heal_or_dmg_pct":15}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Faveur Divine',      'description' => 'Les effets positifs du Prêtre ont 10% de chance de s\'appliquer en double.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"buff_double_chance":10}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Miracle Raté',       'description' => 'Quand le trait rate un soin, dégâts à 50% ATQ sur un ennemi aléatoire (l\'énergie doit bien aller quelque part).',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"on_heal_fail_dmg_pct":50,"target":"random_enemy"}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Martyr',             'description' => 'Quand un allié est à moins de 20% PV, le Prêtre peut lui transférer 20% de ses propres PV.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"ally_below_20hp","self_hp_transfer_pct":20}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Hérésie Productive', 'description' => 'Chaque déclenchement du trait donne +8% INT permanent (cumulable) pour le combat.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"on_trait_int_pct":8}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Stigmates',          'description' => 'Le Prêtre absorbe les dégâts de ses alliés (50% rédirection). Les dégâts absorbés se convertissent en soins distribués à tous.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"absorb_ally_dmg_pct":50,"absorbed_to_heal":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Dieu Incohérent',    'description' => 'Quand le trait se déclenche, un effet divin aléatoire : résurrection équipe, dégâts sacrés massifs, invincibilité 1 tour, ou le Prêtre s\'endort en souriant.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","random_divine":["team_revive_30","aoe_sacre_150","invincible_1turn","priest_sleep_happy"]}', 'prerequisite_talent_id' => null],
        ];
    }
}
