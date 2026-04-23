<?php

namespace Database\Seeders\Talents;

/**
 * Voleur — 21 talents (3 branches × 7).
 * Branche A : Assassin (DPS burst)        → 'offensive'
 * Branche B : Ombre (Esquive/Utilitaire)  → 'defensive'
 * Branche C : Filou (Défaut)              → 'defaut'
 * Source : TALENT_TREES.md §3
 */
class VoleurTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Assassin (offensive) ─────────────────────────────
            ['class_id' => $classId, 'name' => 'Lames Aiguisées',  'description' => 'ATQ +10%.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Embuscade',        'description' => 'La première attaque du combat inflige 200% des dégâts.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"first_attack","dmg_pct":200}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Points Vitaux',    'description' => 'Chance de critique +10%.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"crit_chance","percent":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Poison',           'description' => 'La prochaine attaque empoisonne (3% PV max/tour pendant 4 tours). CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"poison_hp_pct":3,"turns":4}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Ombre Mortelle',   'description' => 'Invisible 1 tour (non ciblable). Prochaine attaque à 250%. CD : 6 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"invisible_turns":1,"next_atk_pct":250}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Hémorragie',       'description' => 'Les critiques infligent un saignement : 4% PV max/tour pendant 3 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"on_crit_bleed_pct":4,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Coup Fatal',       'description' => 'Les attaques contre un ennemi sous 15% PV sont des kills automatiques. Ne fonctionne pas sur les boss.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"target_below_15hp","instakill":true,"no_boss":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche B : Ombre (defensive) ────────────────────────────────
            ['class_id' => $classId, 'name' => 'Agilité',          'description' => 'VIT +15%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"vit","percent":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Évasion',          'description' => 'Esquive +8%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"dodge","percent":8}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Feinte',           'description' => 'L\'ennemi ciblé rate automatiquement sa prochaine attaque. CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"enemy_miss_next":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Pas de l\'Ombre',  'description' => 'Après une esquive, +30% ATQ sur la prochaine attaque.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_dodge","next_atk_bonus_pct":30}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Double Lame',      'description' => 'Le Voleur attaque deux fois par tour, la seconde à 50% des dégâts.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"double_attack":true,"second_atk_pct":50}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Piège',            'description' => 'Piège invisible. Le prochain attaquant est étourdi 1 tour + prend 100% ATQ. CD : 6 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"trap":true,"stun_turns":1,"dmg_pct":100}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Fantôme',          'description' => 'Esquive +15%. Chaque esquive a 30% de chance de rendre le Voleur invisible 1 tour.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"dodge","percent":15,"dodge_invisible_chance":30,"invisible_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche C : Filou (defaut) ────────────────────────────────────
            ['class_id' => $classId, 'name' => 'Doigts Agiles',    'description' => '+15% de chance de loot supplémentaire après chaque combat.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"loot_chance","percent":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Pickpocket',       'description' => 'Quand le trait se déclenche, vole un buff de l\'ennemi au lieu de subir le malus.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","steal_enemy_buff":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Coup Bas',         'description' => 'Si le Voleur agit en dernier dans un tour, ses dégâts sont +40%.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"last_in_turn_dmg_bonus_pct":40}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Échappatoire',     'description' => 'Si le Voleur fuit (Couard ou autre), il vole 1 objet à l\'ennemi en partant.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_flee","steal_item":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Baratineur',       'description' => '10% de chance qu\'un ennemi hésite et skip son tour.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"enemy_skip_chance":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Sabotage Discret', 'description' => '-30% DEF d\'un ennemi pendant 3 tours. CD : 5 tours.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"enemy_def_debuff_pct":30,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Roi des Embrouilles','description' => 'Quand le trait se déclenche, un effet positif aléatoire se produit en plus : loot bonus, buff d\'équipe, debuff ennemi, ou soin à 20% PV max.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","random_bonus":["loot_bonus","team_buff","enemy_debuff","heal_20_pct"]}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],
        ];
    }
}
