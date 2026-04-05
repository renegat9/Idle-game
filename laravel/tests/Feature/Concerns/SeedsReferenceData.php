<?php

namespace Tests\Feature\Concerns;

use Database\Seeders\GameSettingsSeeder;
use Database\Seeders\RaceSeeder;
use Database\Seeders\ClassSeeder;
use Database\Seeders\TraitSeeder;
use Database\Seeders\ZoneSeeder;
use Database\Seeders\MonsterSeeder;
use Database\Seeders\EncounterGroupSeeder;
use Database\Seeders\MaterialSeeder;
use Database\Seeders\ItemTemplateSeeder;
use Database\Seeders\QuestSeeder;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ElementChartSeeder;

trait SeedsReferenceData
{
    protected function seedReferenceData(): void
    {
        $this->seed([
            GameSettingsSeeder::class,
            ElementChartSeeder::class,
            RaceSeeder::class,
            ClassSeeder::class,
            TraitSeeder::class,
            ZoneSeeder::class,
            MonsterSeeder::class,
            EncounterGroupSeeder::class,
            MaterialSeeder::class,
            ItemTemplateSeeder::class,
        ]);
    }

    protected function seedAll(): void
    {
        $this->seedReferenceData();
        $this->seed([
            QuestSeeder::class,
            RecipeSeeder::class,
        ]);
    }
}
