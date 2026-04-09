<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GameSettingsSeeder::class,
            ElementChartSeeder::class,
            RaceSeeder::class,
            ClassSeeder::class,
            TraitSeeder::class,
            ZoneSeeder::class,
            MonsterSeeder::class,
            ElitePrefixSeeder::class,
            EncounterGroupSeeder::class,
            MaterialSeeder::class,
            ItemTemplateSeeder::class,
            QuestSeeder::class,
            RecipeSeeder::class,
            TalentSeeder::class,
            SeasonalEventSeeder::class,
            ConsumableSeeder::class,
        ]);
    }
}
