<?php

namespace Database\Seeders\Talents;

/**
 * Barbare — 21 talents (3 branches × 7).
 * Branche A : Rage (DPS burst)           → 'offensive'
 * Branche B : Brute (Tank/Sustain)       → 'defensive'
 * Branche C : Destruction (Défaut)       → 'defaut'
 * Source : TALENT_TREES.md §7
 */
class BarbareTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Rage (offensive) ─────────────────────────────────
            ['class_id' => $classId, 'name' => 'Furie',              'description' => 'ATQ +12%.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":12}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Frappe Dévastatrice','description' => 'Attaque à 200% de l\'ATQ. Si cela touche, étourdit 1 tour. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"dmg_pct":200,"stun_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Soif de Sang',       'description' => 'Chaque kill augmente l\'ATQ de 15% pour le reste du combat (max 5 stacks).',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_kill","atq_stack_pct":15,"max_stacks":5}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Tremblement de Terre','description' => 'Attaque tous les ennemis à 70% ATQ. Ceux touchés ont -15% VIT pendant 2 tours. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"dmg_pct":70,"aoe":true,"slow_vit_pct":15,"slow_turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Transe Guerrière',   'description' => '+40% ATQ pendant 4 tours mais le Barbare ne peut pas défendre. CD : 7 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":7,"atq_bonus_pct":40,"turns":4,"no_defend":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Déchaînement',       'description' => 'Attaque 4 fois à 80% ATQ sur des cibles aléatoires. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"hits":4,"dmg_pct":80,"target":"random"}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Roi des Barbares',   'description' => 'ATQ +25%. Quand le Barbare est sous 30% PV, son ATQ double.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":25,"low_hp_atq_mult":200,"low_hp_threshold":30}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche B : Brute (defensive) ────────────────────────────────
            ['class_id' => $classId, 'name' => 'Corps Massif',       'description' => 'PV max +20%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"hp","percent":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Peau d\'Acier',      'description' => 'DEF +12%. Les dégâts subis dépassant 15% des PV max sont réduits de 30%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"def","percent":12,"spike_dmg_reduction_pct":30,"spike_threshold":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Stoïcisme',          'description' => 'Les effets de statut négatifs durent 1 tour de moins. Immunité aux effets de peur/terreur.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"debuff_duration_reduction":1,"immune_fear":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Coup de Bouclier',   'description' => 'Bloque la prochaine attaque et riposte à 120% ATQ. CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"block_next_atk":true,"counter_dmg_pct":120}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Régénération Brutale','description' => 'Récupère 8% des PV max au début de chaque tour.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"regen_hp_pct_per_turn":8}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Invulnérabilité',    'description' => 'Immunisé aux dégâts pendant 1 tour. CD : 8 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":8,"invincible_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Léviathan',          'description' => 'PV max +30%. Quand le Barbare tombe sous 10% PV, entre en mode Berserk : +50% ATQ, +30% DEF pendant 3 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"hp","percent":30,"berserk_threshold":10,"berserk_atq_pct":50,"berserk_def_pct":30,"berserk_turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche C : Destruction (defaut) ─────────────────────────────
            ['class_id' => $classId, 'name' => 'Rage Incontrôlée',   'description' => 'Quand le trait se déclenche, le Barbare entre en rage : +25% ATQ pendant 2 tours mais peut accidentellement frapper un allié.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","atq_bonus_pct":25,"turns":2,"friendly_fire_chance":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Démolisseur',        'description' => '25% de chance que chaque attaque détruise un équipement ennemi (DEF ennemi -20% permanent).',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_hit","chance":25,"enemy_def_debuff_pct":20,"permanent":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Dommages Structurels','description' => 'Quand le Barbare manque, son arme s\'abîme mais l\'élan inflige 40% des dégâts à l\'ennemi quand même.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"on_miss_dmg_pct":40}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Tête dans le Mur',   'description' => 'Le Barbare peut attaquer les obstacles de donjon pour +50% d\'or et matériaux.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"obstacle_loot_bonus_pct":50}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Tourbillon',         'description' => 'Attaque tous les ennemis ET alliés à 100% ATQ. Bonus : +30% de loot pour les ennemis tués ainsi.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"dmg_pct":100,"aoe_all":true,"kill_loot_bonus_pct":30}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Berserker Involontaire','description' => 'Chaque déclenchement du trait pendant un combat augmente les dégâts de 12% (cumulable, pas de cap).',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"on_trait_dmg_stack_pct":12}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Apocalypse Personnelle','description' => 'Quand le trait se déclenche, le Barbare lance une attaque incontrôlée sur TOUT ce qui se trouve à portée : ennemis, alliés, décor, peut-être lui-même. Dégâts = 150% ATQ sur tout le monde.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","dmg_pct":150,"targets":"everyone_including_self"}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],
        ];
    }
}
