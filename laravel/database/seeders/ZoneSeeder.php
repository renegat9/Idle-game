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
                'description' => 'Les PNJ elfes sont hautains et inutiles. Les arbres vous jugent.',
                'level_min' => 5,
                'level_max' => 12,
                'dominant_element' => 'poison',
                'is_magical' => 1,
                'unlock_requirement' => 'boss_prairie',
                'order_index' => 2,
                'avg_combat_duration' => 75,
                'ai_generated' => 0,
            ],
        ];

        foreach ($zones as $zone) {
            DB::table('zones')->updateOrInsert(['slug' => $zone['slug']], $zone);
        }
    }
}
