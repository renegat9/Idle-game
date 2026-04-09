<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaceSeeder extends Seeder
{
    public function run(): void
    {
        $races = [
            [
                'slug' => 'humain',
                'name' => 'Humain',
                'base_hp' => 100,
                'base_atq' => 12,
                'base_def' => 10,
                'base_vit' => 10,
                'base_cha' => 10,
                'base_int' => 10,
                'passive_bonus_description' => '+10% XP (seule qualité)',
                'passive_bonus_key' => 'xp_gain_percent',
                'passive_bonus_value' => 10,
            ],
            [
                'slug' => 'elfe',
                'name' => 'Elfe',
                'base_hp' => 80,
                'base_atq' => 14,
                'base_def' => 8,
                'base_vit' => 14,
                'base_cha' => 12,
                'base_int' => 14,
                'passive_bonus_description' => '+15% précision (quand il daigne se battre)',
                'passive_bonus_key' => 'dodge_reduction_percent',
                'passive_bonus_value' => 15,
            ],
            [
                'slug' => 'nain',
                'name' => 'Nain',
                'base_hp' => 120,
                'base_atq' => 14,
                'base_def' => 14,
                'base_vit' => 6,
                'base_cha' => 8,
                'base_int' => 6,
                'passive_bonus_description' => '+20% loot en mine (motivation par la bière)',
                'passive_bonus_key' => 'loot_bonus_mine',
                'passive_bonus_value' => 20,
            ],
            [
                'slug' => 'gobelin',
                'name' => 'Gobelin',
                'base_hp' => 70,
                'base_atq' => 10,
                'base_def' => 6,
                'base_vit' => 16,
                'base_cha' => 14,
                'base_int' => 8,
                'passive_bonus_description' => '+25% or trouvé (chapardage instinctif)',
                'passive_bonus_key' => 'gold_gain_percent',
                'passive_bonus_value' => 25,
            ],
            [
                'slug' => 'orc',
                'name' => 'Orc',
                'base_hp' => 140,
                'base_atq' => 16,
                'base_def' => 8,
                'base_vit' => 8,
                'base_cha' => 6,
                'base_int' => 4,
                'passive_bonus_description' => '+10% dégâts critiques (manque de subtilité)',
                'passive_bonus_key' => 'crit_damage_bonus_percent',
                'passive_bonus_value' => 10,
            ],
            [
                'slug' => 'demi_troll',
                'name' => 'Demi-Troll',
                'base_hp' => 160,
                'base_atq' => 10,
                'base_def' => 16,
                'base_vit' => 4,
                'base_cha' => 4,
                'base_int' => 4,
                'passive_bonus_description' => 'Régénération passive (cerveau en option)',
                'passive_bonus_key' => 'hp_regen_percent',
                'passive_bonus_value' => 5,
            ],
        ];

        foreach ($races as $race) {
            DB::table('races')->updateOrInsert(['slug' => $race['slug']], $race);
        }
    }
}
