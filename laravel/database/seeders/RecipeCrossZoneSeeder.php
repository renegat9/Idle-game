<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Recettes cross-zone avec matériaux rares.
 * Sources : ECONOMY.md §8.3
 * Ces recettes nécessitent des matériaux ultra-rares qui droppent dans toutes les zones.
 */
class RecipeCrossZoneSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            [
                'name'               => 'Amulette du Destin',
                'description'        => 'Légendaire ultime. +10% à toutes les stats, +10% loot, +10% XP.',
                'ingredients'        => json_encode([
                    ['slug' => 'etoile_tombee',     'qty' => 3],
                    ['slug' => 'fil_destin',         'qty' => 2],
                    ['slug' => 'fragment_stellaire', 'qty' => 1],
                ]),
                'gold_cost'          => 20000,
                'result_type'        => 'item',
                'result_slot'        => 'accessoire',
                'result_rarity'      => 'legendaire',
                'result_level'       => 40,
                'result_stats'       => json_encode(['cha' => 80, 'int' => 60, 'hp' => 100]),
                'result_name'        => 'Amulette du Destin',
                'result_description' => 'Le destin décide de vous aider. Profitez-en, ça ne dure pas.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Lame du Héros Déchu',
                'description'        => 'Forgée du sang de ceux qui sont tombés. +15% dégâts quand un allié est K.O.',
                'ingredients'        => json_encode([
                    ['slug' => 'sang_heros',    'qty' => 5],
                    ['slug' => 'essence_majeure', 'qty' => 3],
                    ['slug' => 'obsidienne',     'qty' => 2],
                ]),
                'gold_cost'          => 18000,
                'result_type'        => 'item',
                'result_slot'        => 'arme',
                'result_rarity'      => 'legendaire',
                'result_level'       => 40,
                'result_stats'       => json_encode(['atq' => 200, 'hp' => 60]),
                'result_name'        => 'Lame du Héros Déchu',
                'result_description' => 'Elle pleure. Vraiment. Regardez bien la lame. Ce sont des larmes.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Couronne des Incompétents',
                'description'        => 'Casque légendaire. +8% toutes stats. Les traits négatifs se déclenchent 5% plus souvent MAIS les effets de la Branche du Défaut sont doublés.',
                'ingredients'        => json_encode([
                    ['slug' => 'larme_monde',         'qty' => 3],
                    ['slug' => 'poussiere_narrateur', 'qty' => 3],
                    ['slug' => 'bout_ficelle_cosmique', 'qty' => 1],
                ]),
                'gold_cost'          => 25000,
                'result_type'        => 'item',
                'result_slot'        => 'casque',
                'result_rarity'      => 'legendaire',
                'result_level'       => 40,
                'result_stats'       => json_encode(['int' => 100, 'cha' => 80, 'def' => 60]),
                'result_name'        => 'Couronne des Incompétents',
                'result_description' => 'Porter cette couronne, c\'est accepter que vos défauts soient votre plus grande force. Et aussi votre plus grand problème.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Tablier de Gérard',
                'description'        => 'Armure légendaire. Immunité aux échecs de craft. Gérard pleure un peu quand on lui demande.',
                'ingredients'        => json_encode([
                    ['slug' => 'larme_de_gerard', 'qty' => 5],
                    ['slug' => 'ferraille',        'qty' => 10],
                ]),
                'gold_cost'          => 5000,
                'result_type'        => 'item',
                'result_slot'        => 'armure',
                'result_rarity'      => 'legendaire',
                'result_level'       => 20,
                'result_stats'       => json_encode(['def' => 80, 'hp' => 120]),
                'result_name'        => 'Tablier de Gérard',
                'result_description' => 'L\'armure de forge légendaire. Immune aux échecs. Comme Gérard lui-même (il le prétend).',
                'is_discoverable'    => true,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Micro du Narrateur',
                'description'        => 'Accessoire légendaire. +20% XP, améliore la qualité des commentaires du Narrateur.',
                'ingredients'        => json_encode([
                    ['slug' => 'poussiere_narrateur', 'qty' => 3],
                    ['slug' => 'fragment_stellaire',  'qty' => 1],
                ]),
                'gold_cost'          => 8000,
                'result_type'        => 'item',
                'result_slot'        => 'accessoire',
                'result_rarity'      => 'legendaire',
                'result_level'       => 35,
                'result_stats'       => json_encode(['cha' => 100, 'int' => 80]),
                'result_name'        => 'Micro du Narrateur',
                'result_description' => 'Le Narrateur commente déjà tout. Avec ça, il commente mieux. Vous n\'étiez pas sûr que c\'était possible.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => null,
            ],
        ];

        foreach ($recipes as $recipe) {
            $recipe['created_at'] = now();
            $recipe['updated_at'] = now();
            DB::table('recipes')->insertOrIgnore($recipe);
        }
    }
}
