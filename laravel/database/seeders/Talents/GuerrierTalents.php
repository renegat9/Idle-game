<?php

namespace Database\Seeders\Talents;

/**
 * Guerrier — 21 talents (3 branches × 7).
 * Branche A : Rempart (défensif) → 'defensive'
 * Branche B : Bras Armé (DPS)   → 'offensive'
 * Branche C : Calamité (Défaut) → 'defaut'
 * Source : TALENT_TREES.md §1
 */
class GuerrierTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Rempart (defensive) ──────────────────────────────
            ['class_id' => $classId, 'name' => 'Peau Épaisse',        'description' => 'DEF +10%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"def","percent":10}',                  'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Provocation',         'description' => 'Force tous les ennemis à cibler le Guerrier pendant 2 tours. CD : 5 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"taunt_turns":2}',               'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Endurance',           'description' => 'PV max +15%.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"hp","percent":15}',                   'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Mur de Bouclier',     'description' => 'Quand un allié tombe sous 20% PV, le Guerrier absorbe 50% des dégâts suivants à sa place pendant 1 tour.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"ally_low_hp","threshold":20,"absorb_pct":50,"turns":1}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Représailles',        'description' => 'Chaque fois que le Guerrier est touché, riposte pour 30% de son ATQ.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_hit","counter_atq_pct":30}',    'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Cri Intimidant',      'description' => 'Tous les ennemis ont -20% ATQ pendant 3 tours. CD : 6 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"enemy_atq_debuff_pct":20,"turns":3}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Forteresse Vivante',  'description' => 'DEF +25%. Quand le Guerrier est au-dessus de 50% PV, toute l\'équipe gagne +10% DEF.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"def","percent":25,"aura_def_pct":10,"aura_condition":"hp_above_50"}', 'prerequisite_talent_id' => null],

            // ── Branche B : Bras Armé (offensive) ────────────────────────────
            ['class_id' => $classId, 'name' => 'Force Brute',         'description' => 'ATQ +10%.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":10}',                  'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Coup Puissant',       'description' => 'Attaque à 180% de l\'ATQ. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"dmg_pct":180}',                 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Entraînement',        'description' => 'Chance de critique +8%.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"crit_chance","percent":8}',            'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Exécution',           'description' => 'Si la cible est sous 25% PV, les dégâts sont doublés.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"target_low_hp","threshold":25,"dmg_mult":200}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Frénésie',            'description' => 'Chaque kill donne +10% ATQ cumulable, max 5 stacks. Reset chaque combat.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"trigger":"on_kill","atq_stack_pct":10,"max_stacks":5}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Charge Dévastatrice', 'description' => 'Attaque tous les ennemis à 80% de l\'ATQ. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"dmg_pct":80,"aoe":true}',        'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Maître d\'Armes',     'description' => 'ATQ +20%. Les critiques infligent 200% au lieu de 150%.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":20,"crit_dmg_pct":200}', 'prerequisite_talent_id' => null],

            // ── Branche C : Calamité (defaut) ─────────────────────────────────
            ['class_id' => $classId, 'name' => 'Maladresse Calculée', 'description' => 'Quand un trait négatif se déclenche, le Guerrier gagne +15% ATQ pendant 2 tours.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","buff_atq_pct":15,"turns":2}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Casse-Tout',          'description' => '20% de chance de détruire l\'arme de l\'ennemi (ATQ ennemi -30% pour le combat).',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_hit","chance":20,"enemy_atq_debuff_pct":30,"permanent":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Dommages Collatéraux','description' => 'Quand le Guerrier rate (esquive ennemi), inflige 50% des dégâts à un ennemi adjacent aléatoire.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"trigger":"on_miss","splash_dmg_pct":50}',    'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Tête Dure',           'description' => 'Les effets de statut négatifs durent 1 tour de moins sur le Guerrier.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"debuff_duration_reduction":1}',               'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Chaos Tactique',      'description' => 'Quand le trait se déclenche 2 fois dans le même combat, le Guerrier entre en Rage : +30% ATQ et VIT pendant 3 tours.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait_x2","rage_atq_pct":30,"rage_vit_pct":30,"turns":3}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Fier Incompétent',    'description' => 'Le % de déclenchement du trait est réduit de 5 points mais chaque déclenchement donne +5% de toutes les stats pour le reste du combat.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"trait_chance_reduction":5,"on_trait_all_stats_pct":5}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Catastrophe Ambulante','description' => 'Les effets négatifs du trait affectent AUSSI l\'ennemi le plus proche.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"trait_mirror_to_enemy":true}',                'prerequisite_talent_id' => null],
        ];
    }
}
