<?php

namespace Database\Seeders\Talents;

/**
 * Mage — 21 talents (3 branches × 7).
 * Branche A : Élémentaliste (DPS mono-cible) → 'offensive'
 * Branche B : Arcaniste (AoE/Contrôle)       → 'defensive'
 * Branche C : Instabilité (Défaut)            → 'defaut'
 * Source : TALENT_TREES.md §2
 */
class MageTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Élémentaliste (offensive) ────────────────────────
            ['class_id' => $classId, 'name' => 'Concentration',        'description' => 'INT +10%.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif', 'effect_data' => '{"stat":"int","percent":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Trait de Feu',         'description' => 'Dégâts magiques à 160% de l\'INT sur une cible. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":3,"dmg_int_pct":160}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Pénétration Arcanique','description' => 'Les sorts ignorent 20% supplémentaires de la résistance magique.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif', 'effect_data' => '{"magic_penetration_pct":20}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Résonance',            'description' => 'Si le sort tue la cible, 40% des dégâts excédentaires se propagent à un autre ennemi.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif','effect_data' => '{"trigger":"on_kill","overflow_dmg_pct":40}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Canalisation',         'description' => 'Skip un tour pour doubler les dégâts du prochain sort. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":4,"skip_turn":true,"next_spell_dmg_mult":200}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Embrasement',          'description' => 'Les sorts ont 25% de chance d\'infliger "En feu" (3 tours).',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif', 'effect_data' => '{"on_spell_chance":25,"status":"en_feu","turns":3}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Archimage',            'description' => 'INT +20%. Les sorts critiques lancent automatiquement un second sort gratuit à 50% des dégâts.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif', 'effect_data' => '{"stat":"int","percent":20,"crit_echo_pct":50}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche B : Arcaniste (defensive) ────────────────────────────
            ['class_id' => $classId, 'name' => 'Onde de Choc',         'description' => 'Dégâts magiques à 70% de l\'INT sur tous les ennemis. CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":4,"dmg_int_pct":70,"aoe":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Gel',                  'description' => 'Ralentit une cible pendant 2 tours. CD : 3 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":3,"status":"ralenti","turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Tempête Arcanique',    'description' => 'Les AoE gagnent +15% de dégâts.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif', 'effect_data' => '{"aoe_dmg_bonus_pct":15}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Chaîne d\'Éclairs',   'description' => 'Touche 3 ennemis à 60% puis 40% puis 20% de l\'INT. CD : 5 tours.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":5,"chain":[60,40,20],"stat":"int"}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Silence',              'description' => 'Empêche un ennemi d\'utiliser des compétences pendant 2 tours. CD : 6 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":6,"status":"silence","turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Bouclier de Mana',     'description' => 'Quand le Mage tombe sous 30% PV, gagne un bouclier absorbant égal à 50% de son INT pendant 2 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif','effect_data' => '{"trigger":"hp_below_30","shield_int_pct":50,"turns":2}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Maître du Chaos',      'description' => 'Les AoE ont 15% de chance de déclencher un effet aléatoire : Étourdi, Ralenti, En feu, ou Terrifié.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif', 'effect_data' => '{"aoe_chaos_chance":15,"effects":["etourdi","ralenti","en_feu","terrifie"]}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            // ── Branche C : Instabilité (defaut) ─────────────────────────────
            ['class_id' => $classId, 'name' => 'Magie Instable',       'description' => 'Les sorts ont une variance élargie : 70-140% au lieu de 90-110%.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif', 'effect_data' => '{"variance_min":70,"variance_max":140}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Siphon d\'Erreur',     'description' => 'Quand un trait négatif se déclenche, le Mage récupère 10% de ses PV max.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif','effect_data' => '{"trigger":"on_trait","self_heal_pct":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Explosion Involontaire','description' => 'Quand le Mage est étourdi ou endormi par son trait, une explosion inflige 80% de l\'INT en dégâts à tous les ennemis.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'reactif','effect_data' => '{"trigger":"on_trait_stun_sleep","aoe_dmg_int_pct":80}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Distorsion Réelle',    'description' => '10% de chance que les dégâts reçus soient redirigés vers un ennemi aléatoire.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif', 'effect_data' => '{"redirect_dmg_chance":10}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Faille Temporelle',    'description' => 'Rejoue le dernier tour du Mage (même sort, même cible). CD : 8 tours.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',  'effect_data' => '{"cooldown":8,"replay_last_turn":true}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Éruption de Trait',    'description' => 'Le trait a +10% de chance de se déclencher, mais chaque déclenchement augmente l\'INT de 8% pour le combat.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 1, 'required_points_in_branch' => 6,
             'talent_type' => 'passif', 'effect_data' => '{"trait_chance_bonus":10,"on_trait_int_pct":8}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],

            ['class_id' => $classId, 'name' => 'Bombe à Retardement',  'description' => 'Quand le Mage meurt, explose : 200% INT en dégâts à tous les ennemis. Si ça tue au moins un ennemi, le Mage revient avec 1 PV.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif','effect_data' => '{"trigger":"on_death","aoe_dmg_int_pct":200,"revive_if_kill":1}', 'prerequisite_talent_id' => null, 'created_at' => $t, 'updated_at' => $t],
        ];
    }
}
