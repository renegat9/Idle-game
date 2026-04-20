<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TraitSeeder extends Seeder
{
    public function run(): void
    {
        $traits = [
            [
                'slug' => 'couard',
                'name' => 'Couard',
                'description' => '15% de chance de fuir un combat',
                'flavor_text' => '"La fuite, c\'est une stratégie."',
                'trigger_moment' => 'turn_start',
                'base_chance' => 15,
                'chance_level_26' => 13, // GDD §2.1 : réduit avec le niveau
                'chance_level_51' => 11,
                'chance_level_76' => 10,
                'effect_data' => json_encode(['action' => 'flee', 'skip_turn' => true]),
                'scaling_data' => null,
                'out_of_combat_effect' => null,
            ],
            [
                'slug' => 'narcoleptique',
                'name' => 'Narcoleptique',
                'description' => '10% de chance de s\'endormir pendant un combat (skip un tour)',
                'flavor_text' => '"Zzz... Quoi ? On est attaqués ?"',
                'trigger_moment' => 'turn_start',
                'base_chance' => 10,
                'chance_level_26' => 9,  // GDD §2.2
                'chance_level_51' => 8,
                'chance_level_76' => 7,
                'effect_data' => json_encode([
                    'action' => 'sleep',
                    'skip_turn' => true,
                    'duration' => 2,
                    'wake_chance' => 50,
                    'wake_vit_bonus_pct' => 10, // bonus VIT au réveil
                ]),
                'scaling_data' => null,
                'out_of_combat_effect' => null,
            ],
            [
                'slug' => 'kleptomane',
                'name' => 'Kleptomane',
                'description' => 'Vole parfois le loot des alliés',
                'flavor_text' => '"C\'était dans ma poche depuis le début."',
                'trigger_moment' => 'after_combat',
                'base_chance' => 20,
                'chance_level_26' => 18, // GDD §2.3 : décroît
                'chance_level_51' => 16,
                'chance_level_76' => 15,
                'effect_data' => json_encode([
                    'action' => 'steal_loot',
                    'skip_turn' => false,
                    'xp_steal_pct' => 10,    // % XP volé à un allié
                    'loot_steal_chance' => 30, // % de chance de s'attribuer un loot Rare+
                    'synergy_class' => 'voleur',
                ]),
                'scaling_data' => null,
                'out_of_combat_effect' => '+5% de chance de trouver de l\'or bonus en exploration',
            ],
            [
                'slug' => 'pyromane',
                'name' => 'Pyromane',
                'description' => '20% de chance de mettre le feu au décor (dégâts de zone)',
                'flavor_text' => '"Le feu, ça résout tout."',
                'trigger_moment' => 'after_attack',
                'base_chance' => 20,
                'chance_level_26' => 20, // GDD §2.4 : chance CONSTANTE, seuls les dégâts augmentent
                'chance_level_51' => 20,
                'chance_level_76' => 20,
                'effect_data' => json_encode([
                    'action' => 'aoe_fire',
                    'skip_turn' => false,
                    'damage_percent' => 8,
                    'friendly_fire' => true,
                    'ignite_chance_pct' => 30, // chance d'appliquer "en_feu" sur chaque cible
                ]),
                'scaling_data' => json_encode([
                    // Dégâts augmentent par palier de niveau (GDD §2.4)
                    'damage_percent_l1'  => 8,
                    'damage_percent_l26' => 10,
                    'damage_percent_l51' => 12,
                    'damage_percent_l76' => 15,
                ]),
                'out_of_combat_effect' => '5% de chance de brûler un objet Commun de l\'inventaire après chaque donjon',
            ],
            [
                'slug' => 'allergique',
                'name' => 'Allergique à la Magie',
                'description' => 'Malus en zone magique, éternue et révèle la position',
                'flavor_text' => '"ATCHOUM — ah, le boss nous a vus."',
                'trigger_moment' => 'turn_start',
                'base_chance' => 25,
                'chance_level_26' => 22, // GDD §2.5
                'chance_level_51' => 20,
                'chance_level_76' => 18,
                'effect_data' => json_encode([
                    'action' => 'sneeze',
                    'skip_turn' => true,
                    'malus_percent' => 20,
                    'only_in_magic_zone' => true,
                    'enemy_hit_bonus_pct' => 10,  // ennemis +10% chance de toucher
                    'sneeze_cumulative_threshold' => 3, // malus permanent après 3 éternuements
                ]),
                'scaling_data' => null,
                'out_of_combat_effect' => 'Potions magiques : 15% de chance de ne pas fonctionner. Malus de -20% stats en zones magiques.',
            ],
            [
                'slug' => 'philosophe',
                'name' => 'Philosophe',
                'description' => 'S\'arrête en plein combat pour réfléchir (skip un tour)',
                'flavor_text' => '"Mais au fond, pourquoi combattre ?"',
                'trigger_moment' => 'turn_start',
                'base_chance' => 12,
                'chance_level_26' => 12, // GDD §2.6 : chance stable puis descend légèrement
                'chance_level_51' => 11,
                'chance_level_76' => 10,
                'effect_data' => json_encode([
                    'action' => 'ponder',
                    'skip_turn' => true,
                    'int_buff_percent' => 5, // GDD : +5% INT par déclenchement (cumulable)
                ]),
                'scaling_data' => json_encode([
                    // INT buff augmente avec le niveau (GDD §2.6)
                    'int_buff_l1'  => 5,
                    'int_buff_l26' => 6,
                    'int_buff_l51' => 7,
                    'int_buff_l76' => 8,
                ]),
                'out_of_combat_effect' => '+5% XP gagnée (le héros réfléchit à ses expériences)',
            ],
            [
                'slug' => 'gourmand',
                'name' => 'Gourmand',
                'description' => 'Consomme les potions de soin automatiquement même à PV max',
                'flavor_text' => '"C\'était pas du jus de pomme ?"',
                'trigger_moment' => 'turn_start',
                'base_chance' => 25,
                'chance_level_26' => 22, // GDD §2.7 : décroît
                'chance_level_51' => 20,
                'chance_level_76' => 18,
                'effect_data' => json_encode([
                    'action' => 'consume_potion',
                    'skip_turn' => false,    // n'empêche PAS l'action si PV < max
                    'even_at_full_hp' => true,
                    'atq_malus_pct' => 5,    // malus ATQ si bouderie (pas de potion dispo)
                ]),
                'scaling_data' => null,
                'out_of_combat_effect' => '+20% coût des services à la taverne (il commande double)',
            ],
            [
                'slug' => 'superstitieux',
                'name' => 'Superstitieux',
                'description' => 'Refuse d\'entrer dans certains donjons selon le jour',
                'flavor_text' => '"Pas un mardi ! Jamais un mardi !"',
                'trigger_moment' => 'dungeon_entry',
                'base_chance' => 15,
                'chance_level_26' => 13, // GDD §2.8
                'chance_level_51' => 12,
                'chance_level_76' => 10,
                'effect_data' => json_encode([
                    'action' => 'refuse_dungeon',
                    'skip_turn' => false,
                    'conviction_penalty_pct' => 10, // -10% stats si convaincu par paiement
                ]),
                'scaling_data' => json_encode([
                    // Coût de conviction : or × niveau du héros × multiplicateur
                    'conviction_cost_mult_l1'  => 100,
                    'conviction_cost_mult_l26' => 80,
                    'conviction_cost_mult_l51' => 60,
                    'conviction_cost_mult_l76' => 50,
                ]),
                'out_of_combat_effect' => null,
            ],
            [
                'slug' => 'mythomane',
                'name' => 'Mythomane',
                'description' => 'Ses stats affichées sont fausses (±20% aléatoire)',
                'flavor_text' => '"J\'ai déjà tué un dragon. Enfin, un gros lézard."',
                'trigger_moment' => 'permanent',
                'base_chance' => 100,
                'chance_level_26' => 100,
                'chance_level_51' => 100,
                'chance_level_76' => 100,
                'effect_data' => json_encode([
                    'action' => 'display_variance',
                    'skip_turn' => false,
                    'variance_percent' => 20,
                ]),
                'scaling_data' => json_encode([
                    // Variance réduit légèrement avec le niveau (GDD §2.9)
                    'variance_l1'  => 20,
                    'variance_l26' => 18,
                    'variance_l51' => 15,
                    'variance_l76' => 12,
                ]),
                'out_of_combat_effect' => 'Stats affichées ±20% de la vraie valeur (hors combat inclus)',
            ],
            [
                'slug' => 'pacifiste',
                'name' => 'Pacifiste',
                'description' => 'Refuse d\'attaquer certains ennemis "trop mignons"',
                'flavor_text' => '"Non mais regarde sa petite tête !"',
                'trigger_moment' => 'on_target_low_hp',
                'base_chance' => 15,
                'chance_level_26' => 13, // GDD §2.10
                'chance_level_51' => 12,
                'chance_level_76' => 10,
                'effect_data' => json_encode([
                    'action' => 'refuse_attack',
                    'skip_turn' => false, // GDD : ne skip pas, effectue une ACTION alternative
                    'hp_threshold_percent' => 30,
                    // Actions alternatives pondérées (GDD §2.10)
                    'alternatives' => [
                        ['action' => 'defend',          'weight' => 40, 'def_bonus_pct' => 20, 'duration' => 1],
                        ['action' => 'encourage_ally',  'weight' => 30, 'atq_bonus_pct' => 10, 'duration' => 1],
                        ['action' => 'heal_enemy',      'weight' => 20, 'heal_pct' => 5],
                        ['action' => 'nothing',         'weight' => 10],
                    ],
                ]),
                'scaling_data' => json_encode([
                    // Seuil HP diminue avec le niveau (GDD §2.10)
                    'threshold_l1'  => 30,
                    'threshold_l26' => 28,
                    'threshold_l51' => 25,
                    'threshold_l76' => 20,
                ]),
                'out_of_combat_effect' => null,
            ],
        ];

        foreach ($traits as $trait) {
            DB::table('traits')->updateOrInsert(['slug' => $trait['slug']], $trait);
        }
    }
}
