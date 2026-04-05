<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'slug' => 'prairie',
                'name' => 'La Prairie des Débutants',
                'description' => 'Une vaste prairie verdoyante où les dangers sont... relatifs. Le narrateur se moque de toi depuis le début.',
                'level_min' => 1,
                'level_max' => 5,
                'dominant_element' => 'physique',
                'is_magical' => 0,
                'unlock_requirement' => null,
                'order_index' => 1,
                'avg_combat_duration' => 60,
                'ai_generated' => 0,
            ],
            [
                'slug' => 'foret_elfes',
                'name' => 'La Forêt des Elfes Vexés',
                'description' => 'Les PNJ elfes sont hautains et inutiles. Les arbres vous jugent. La végétation est poissonneuse — le Narrateur est certain que ce mot n\'existe pas.',
                'level_min' => 5,
                'level_max' => 12,
                'dominant_element' => 'poison',
                'is_magical' => 1,
                'unlock_requirement' => 'boss_prairie',
                'order_index' => 2,
                'avg_combat_duration' => 75,
                'ai_generated' => 0,
            ],
            [
                'slug' => 'mines_nain',
                'name' => 'Les Mines du Nain Ivre',
                'description' => 'Thorin le Nain Ivre vous accueille avec une chope. Refuser serait impoli. Accepter aussi, d\'ailleurs.',
                'level_min' => 12,
                'level_max' => 20,
                'dominant_element' => 'physique',
                'is_magical' => 0,
                'unlock_requirement' => 'boss_foret_elfes',
                'order_index' => 3,
                'avg_combat_duration' => 90,
                'ai_generated' => 0,
            ],
            [
                'slug' => 'marais_bureaucratie',
                'name' => 'Le Marais de la Bureaucratie',
                'description' => 'Chaque ennemi exige un formulaire en triple exemplaire avant de vous attaquer. Le Narrateur a lui-même rempli sa déclaration d\'impôts ici.',
                'level_min' => 20,
                'level_max' => 30,
                'dominant_element' => 'ombre',
                'is_magical' => 1,
                'unlock_requirement' => 'boss_mines_nain',
                'order_index' => 4,
                'avg_combat_duration' => 105,
                'ai_generated' => 0,
            ],
        ];

        foreach ($zones as $zone) {
            DB::table('zones')->updateOrInsert(['slug' => $zone['slug']], $zone);
        }
    }
}
