<?php

namespace Database\Seeders\Talents;

/**
 * Ranger — 21 talents (3 branches × 7).
 * Branche A : Tireur d'Élite (DPS distance) → 'offensive'
 * Branche B : Survivaliste (Survie/Utilitaire) → 'defensive'
 * Branche C : Distrait (Défaut)              → 'defaut'
 * Source : TALENT_TREES.md §4
 */
class RangerTalents
{
    public static function talents(int $classId): array
    {
        $t = date('Y-m-d H:i:s');

        return [
            // ── Branche A : Tireur d'Élite (offensive) ───────────────────────
            ['class_id' => $classId, 'name' => 'Visée Stable',       'description' => 'ATQ +10%.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":10}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Tir Précis',         'description' => 'Attaque à 150% de l\'ATQ. CD : 3 tours.',
             'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":3,"dmg_pct":150}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Point Faible',       'description' => 'Chance de critique +12%.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"crit_chance","percent":12}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Tir Perforant',      'description' => 'Ignore 25% de la DEF ennemie.',
             'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"armor_pen_pct":25}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Concentration Absolue', 'description' => 'Skip un tour pour tripler les dégâts du prochain tir. CD : 5 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"skip_turn":true,"next_atk_dmg_mult":300}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Marque du Chasseur', 'description' => 'Marque une cible : elle reçoit +20% de dégâts de toute l\'équipe pendant 3 tours. CD : 4 tours.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"mark_dmg_taken_pct":20,"turns":3}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Oeil de Faucon',     'description' => 'ATQ +20%. Les critiques ont 50% de chance de toucher un second ennemi aléatoire à 70% des dégâts.',
             'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"atq","percent":20,"crit_chain_chance":50,"crit_chain_pct":70}', 'prerequisite_talent_id' => null],

            // ── Branche B : Survivaliste (defensive) ─────────────────────────
            ['class_id' => $classId, 'name' => 'Peau Tannée',        'description' => 'DEF +8%.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"def","percent":8}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Piège à Ours',       'description' => 'Place un piège. Le prochain attaquant est ralenti 2 tours + prend 80% ATQ. CD : 5 tours.',
             'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":5,"trap_slow_turns":2,"trap_dmg_pct":80}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Herboristerie',      'description' => 'Se soigne de 10% des PV max en début de chaque combat.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"regen_hp_pct_per_combat":10}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Compagnon Faucon',   'description' => 'Un faucon attaque un ennemi aléatoire pour 40% ATQ chaque tour.',
             'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"pet_falcon_dmg_pct":40}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Terrain Connu',      'description' => 'VIT +20%. Le Ranger agit toujours en premier dans la zone actuelle.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"vit","percent":20,"always_first":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Pluie de Flèches',   'description' => 'Attaque tous les ennemis à 60% ATQ. CD : 4 tours.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"dmg_pct":60,"aoe":true}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Seigneur des Bêtes', 'description' => 'Le faucon devient une meute : 3 attaques à 30% ATQ chaque tour. Herboristerie soigne 20% au lieu de 10%.',
             'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'passif',  'effect_data' => '{"pet_pack_count":3,"pet_pack_dmg_pct":30,"herboristerie_mult":2}', 'prerequisite_talent_id' => null],

            // ── Branche C : Distrait (defaut) ────────────────────────────────
            ['class_id' => $classId, 'name' => 'Tir Chanceux',       'description' => 'Quand le trait se déclenche, le prochain tir a 50% de chance d\'être un critique automatique.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait","next_atk_crit_chance":50}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Instinct',           'description' => 'Esquive +6%. Si le trait se déclenche pendant un combat, l\'esquive augmente de +4% supplémentaires.',
             'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"dodge","percent":6,"on_trait_dodge_bonus":4}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Rebond',             'description' => 'Les tirs manqués rebondissent sur un autre ennemi aléatoire à 50% des dégâts.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"miss_bounce_pct":50}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Observation Passive','description' => '+5% de loot supplémentaire. Le Ranger remarque les objets même quand il est distrait.',
             'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3,
             'talent_type' => 'passif',  'effect_data' => '{"stat":"loot_chance","percent":5}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Tir dans le Noir',   'description' => 'Tir aléatoire sur un ennemi à 200% ATQ. CD : 4 tours. Le Ranger ne sait pas non plus sur qui il a tiré.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'actif',   'effect_data' => '{"cooldown":4,"dmg_pct":200,"target":"random"}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Flèche Égarée',      'description' => 'Quand un allié est touché, 30% de chance que la flèche égarée du Ranger intercepte et redirige 50% des dégâts sur l\'attaquant.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_ally_hit","redirect_chance":30,"redirect_pct":50}', 'prerequisite_talent_id' => null],

            ['class_id' => $classId, 'name' => 'Sniper Somnambule',  'description' => 'Quand le trait endort le Ranger, il tire quand même en dormant. 2 tirs automatiques à 80% ATQ sur des cibles aléatoires.',
             'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6,
             'talent_type' => 'reactif', 'effect_data' => '{"trigger":"on_trait_sleep","auto_shots":2,"auto_shot_pct":80,"target":"random"}', 'prerequisite_talent_id' => null],
        ];
    }
}
