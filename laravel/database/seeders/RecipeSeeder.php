<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RecipeBaseSeeder::class,
            RecipeZoneSeeder::class,
            RecipeCrossZoneSeeder::class,
        ]);
    }
}
