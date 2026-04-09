<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');
        $foretId   = DB::table('zones')->where('slug', 'foret_elfes')->value('id');
        $minesId   = DB::table('zones')->where('slug', 'mines_nain')->value('id');
        $maraisId  = DB::table('zones')->where('slug', 'marais_bureaucratie')->value('id');

        $recipes = [
            // ── Recettes de base (toujours disponibles) ──
            [
                'name'               => 'Potion de Soin',
                'description'        => 'Soigne 30% des PV max d\'un héros. Gérard dit que ça marche mieux chaud.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 3], ['slug' => 'essence_mineure', 'qty' => 2]]),
                'gold_cost'          => 50,
                'result_type'        => 'consumable',
                'result_slot'        => null,
                'result_rarity'      => 'commun',
                'result_level'       => 1,
                'result_stats'       => json_encode(['heal_percent' => 30]),
                'result_name'        => 'Potion de Soin',
                'result_description' => 'Soigne 30% des PV max. Goût infâme, efficacité prouvée.',
                'is_discoverable'    => false,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Potion de Soin+',
                'description'        => 'Soigne 60% des PV max. Gérard a mis deux fois plus de trucs verts dedans.',
                'ingredients'        => json_encode([['slug' => 'essence_mineure', 'qty' => 2], ['slug' => 'cristal_brut', 'qty' => 1]]),
                'gold_cost'          => 150,
                'result_type'        => 'consumable',
                'result_slot'        => null,
                'result_rarity'      => 'peu_commun',
                'result_level'       => 5,
                'result_stats'       => json_encode(['heal_percent' => 60]),
                'result_name'        => 'Potion de Soin+',
                'result_description' => 'Soigne 60% des PV max. Goût encore pire. Résultat meilleur.',
                'is_discoverable'    => false,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Parchemin de Fuite',
                'description'        => 'Garantit une fuite réussie. Gérard juge. Le Narrateur aussi.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 5]]),
                'gold_cost'          => 30,
                'result_type'        => 'consumable',
                'result_slot'        => null,
                'result_rarity'      => 'commun',
                'result_level'       => 1,
                'result_stats'       => json_encode(['guaranteed_flee' => 1]),
                'result_name'        => 'Parchemin de Fuite',
                'result_description' => 'Fuite garantie. "Stratégie de retraite tactique", si vous préférez.',
                'is_discoverable'    => false,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Pierre d\'Aiguisage',
                'description'        => '+10% ATQ pendant 5 combats. Simple et efficace. Gérard approuve.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 4], ['slug' => 'gemme_brute', 'qty' => 1]]),
                'gold_cost'          => 80,
                'result_type'        => 'consumable',
                'result_slot'        => null,
                'result_rarity'      => 'commun',
                'result_level'       => 1,
                'result_stats'       => json_encode(['atq_bonus_percent' => 10, 'duration_combats' => 5]),
                'result_name'        => 'Pierre d\'Aiguisage',
                'result_description' => '+10% ATQ pour 5 combats. L\'arme coupe mieux. C\'est le but.',
                'is_discoverable'    => false,
                'unlock_zone_id'     => null,
            ],
            [
                'name'               => 'Kit de Réparation',
                'description'        => 'Répare 30 points de durabilité. Gérard est fier de ce produit.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 10]]),
                'gold_cost'          => 100,
                'result_type'        => 'consumable',
                'result_slot'        => null,
                'result_rarity'      => 'commun',
                'result_level'       => 1,
                'result_stats'       => json_encode(['repair_durability' => 30]),
                'result_name'        => 'Kit de Réparation',
                'result_description' => 'Répare 30 durabilité. "Comme neuf" est une expression relative.',
                'is_discoverable'    => false,
                'unlock_zone_id'     => null,
            ],

            // ── Recettes découvrables par zone ──
            [
                'name'               => 'Amulette du Débutant',
                'description'        => '+5% XP en permanence. Idéal pour progresser plus vite et compenser l\'incompétence.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 5], ['slug' => 'cuir', 'qty' => 3]]),
                'gold_cost'          => 120,
                'result_type'        => 'item',
                'result_slot'        => 'accessoire',
                'result_rarity'      => 'peu_commun',
                'result_level'       => 2,
                'result_stats'       => json_encode(['cha' => 5, 'hp' => 10]),
                'result_name'        => 'Amulette du Débutant',
                'result_description' => 'Bonus XP. Pour ceux qui savent qu\'ils partent de loin.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => $prairieId,
            ],
            [
                'name'               => 'Arc des Elfes Vexés (Recette)',
                'description'        => 'L\'arc préféré d\'Elara — mais elle l\'a donné parce qu\'elle était de mauvaise humeur.',
                'ingredients'        => json_encode([['slug' => 'essence_mineure', 'qty' => 3], ['slug' => 'cuir', 'qty' => 5]]),
                'gold_cost'          => 300,
                'result_type'        => 'item',
                'result_slot'        => 'arme',
                'result_rarity'      => 'rare',
                'result_level'       => 8,
                'result_stats'       => json_encode(['atq' => 22, 'vit' => 12, 'int' => 8]),
                'result_name'        => 'Arc des Elfes Vexés',
                'result_description' => 'Un arc elfique. Tire avec précision ET condescendance.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => $foretId,
            ],
            [
                'name'               => 'Casque du Nain Blindé',
                'description'        => 'DEF solide + résistance aux étourdissements. Thorin l\'a forgé en étant ivre. C\'est dit.',
                'ingredients'        => json_encode([['slug' => 'ferraille', 'qty' => 8], ['slug' => 'cristal_brut', 'qty' => 2]]),
                'gold_cost'          => 400,
                'result_type'        => 'item',
                'result_slot'        => 'casque',
                'result_rarity'      => 'rare',
                'result_level'       => 14,
                'result_stats'       => json_encode(['def' => 30, 'hp' => 40]),
                'result_name'        => 'Casque du Nain Blindé',
                'result_description' => 'Forgé ivre. Résiste quand même. Impressionnant.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => $minesId,
            ],
            [
                'name'               => 'Formulaire en Triple Exemplaire',
                'description'        => 'Paralyse un ennemi pendant 2 tours avec de la paperasserie. Légal partout.',
                'ingredients'        => json_encode([['slug' => 'cristal_brut', 'qty' => 3], ['slug' => 'essence_majeure', 'qty' => 1]]),
                'gold_cost'          => 600,
                'result_type'        => 'item',
                'result_slot'        => 'accessoire',
                'result_rarity'      => 'epique',
                'result_level'       => 22,
                'result_stats'       => json_encode(['cha' => 25, 'int' => 20]),
                'result_name'        => 'Formulaire en Triple Exemplaire',
                'result_description' => 'Paperasserie offensive. Paralyse moralement les ennemis.',
                'is_discoverable'    => true,
                'unlock_zone_id'     => $maraisId,
            ],
            [
                'name'               => 'Tablier de Gérard',
                'description'        => 'Armure légendaire. Immunité aux échecs de craft. Gérard pleure un peu quand on lui demande.',
                'ingredients'        => json_encode([['slug' => 'larme_de_gerard', 'qty' => 5], ['slug' => 'ferraille', 'qty' => 10]]),
                'gold_cost'          => 2000,
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
        ];

        foreach ($recipes as $recipe) {
            $recipe['created_at'] = now();
            $recipe['updated_at'] = now();
            DB::table('recipes')->insertOrIgnore($recipe);
        }
    }
}
