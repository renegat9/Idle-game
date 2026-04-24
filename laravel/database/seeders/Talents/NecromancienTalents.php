<?php

namespace Database\Seeders\Talents;

/**
 * Nécromancien — 21 talents (3 branches × 7).
 * Branche A : Maître des Morts (Invocations) → 'defensive'
 * Branche B : Flétrisseur (DPS/Debuffs)     → 'offensive'
 * Branche C : Nécromancie Ratée (Défaut)    → 'defaut'
 * Source : TALENT_TREES.md §8
 */
class NecromancienTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Maître des Morts (defensive) ─────────────────────
            ['class_id' => $classId, 'name' => 'Invoquer Squelette',  'description' => 'Invoque un squelette (ATQ = 30% INT, DEF = 20% INT). CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"summon":"squelette","atq_int_pct":30,"def_int_pct":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Armée des Morts',    'description' => 'Les squelettes invoqués comptent comme un second « héros » qui encaisse les coups.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"undead_tanking":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Renforcement Osseux', 'description' => 'Les invocations gagnent +25% PV et +25% DEF.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"summon_hp_bonus_pct":25,"summon_def_bonus_pct":25}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Relève des Cendres', 'description' => 'Quand une invocation meurt, 50% de chance d\'en invoquer une nouvelle immédiatement.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_summon_death","respawn_chance":50}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Liche Temporaire',   'description' => 'Invoque une Liche (ATQ = 60% INT, sorts AoE). Dure 3 tours. CD : 8 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":8,"summon":"liche","atq_int_pct":60,"aoe":true,"duration_turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Lien Vital',         'description' => 'Quand une invocation inflige des dégâts, le Nécromancien récupère 20% de ces dégâts en PV.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"summon_lifesteal_pct":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Seigneur des Morts', 'description' => 'Peut avoir 3 invocations simultanées. INT +20%. Les invocations gagnent 50% des stats du Nécromancien.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"max_summons":3,"stat":"int","percent":20,"summon_stat_inherit_pct":50}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche B : Flétrisseur (offensive) ──────────────────────────
            ['class_id' => $classId, 'name' => 'Toucher Nécrotique', 'description' => 'Inflige 100% INT en dégâts et applique "Flétrissure" : -10% PV max pendant 3 tours. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"dmg_int_pct":100,"wither_hp_pct":10,"turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Drain de Vie',       'description' => 'Vol 15% des PV max de la cible. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"drain_hp_pct":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Malédiction',        'description' => 'INT +12%. Les sorts réduisent la résistance ennemie de 5% (cumulable, max 30%).',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"int","percent":12,"spell_resist_shred_pct":5,"shred_max":30}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Pestilence',         'description' => 'Empoisonne tous les ennemis : 2% PV max/tour pendant 5 tours. CD : 6 tours.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":6,"aoe_poison_hp_pct":2,"turns":5}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Explosion Cadavérique','description' => 'Fait exploser une invocation : dégâts à 150% INT sur tous les ennemis. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"consume_summon":true,"aoe_dmg_int_pct":150}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Suçeur d\'Âme',      'description' => 'Quand un ennemi meurt, le Nécromancien vole son essence : +5% à toutes les stats (max 5 charges).',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_enemy_death","all_stats_stack_pct":5,"max_stacks":5}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Avatar de la Mort',  'description' => 'INT +25%. Le Nécromancien devient un conduit de la mort : chaque tour, tous les ennemis perdent 3% de leurs PV max (ne peut pas tuer, arrête à 1 PV).',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"int","percent":25,"aoe_dot_hp_pct":3,"dot_min_hp":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche C : Nécromancie Ratée (defaut) ───────────────────────
            ['class_id' => $classId, 'name' => 'Squelette Récalcitrant','description' => 'Quand le trait se déclenche, le squelette invoqué refuse d\'obéir pendant 1 tour, puis attaque pour 100% ATQ.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","delayed_summon_atk_pct":100,"delay_turns":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Invocation Imprévisible','description' => 'Quand un sort rate, invoque aléatoirement une créature : squelette, zombie, ou cafard géant (40% ATQ, très énervant).',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_spell_fail","random_summon":["squelette","zombie","cafard_geant"]}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Recyclage Nécromantique','description' => 'Quand une invocation meurt, récupère 20% des PV du Nécromancien et +5% INT pour le combat.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_summon_death","self_heal_pct":20,"int_stack_pct":5}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Mort Simulée',        'description' => 'Une fois par combat, quand le Nécromancien tomberait à 0 PV, simule sa mort et revient avec 20% PV au tour suivant.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_lethal_hit","once_per_combat":true,"revive_hp_pct":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Bureaucratie des Morts','description' => 'Le Nécromancien doit remplir un formulaire pour invoquer. Skip 1 tour mais la prochaine invocation a +100% stats.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"summon_delay_turns":1,"summon_empowered_pct":100}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Contagion du Trait',  'description' => 'Quand le trait se déclenche sur le Nécromancien, il se propage à un ennemi aléatoire (même effet).',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","spread_to_random_enemy":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Liche Accidentelle',  'description' => 'Quand le trait se déclenche 3 fois dans le même combat, le Nécromancien devient accidentellement une Liche pendant 2 tours : immunité aux dégâts, INT ×3.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait_x3","liche_form_turns":2,"invincible":true,"int_mult":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],
        ];
    }
}
