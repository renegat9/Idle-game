<?php

namespace Database\Factories;

use App\Models\Hero;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => \App\Models\User::factory(),
            'race_id'          => fn() => \DB::table('races')->inRandomOrder()->value('id') ?? 1,
            'class_id'         => fn() => \DB::table('classes')->inRandomOrder()->value('id') ?? 1,
            'trait_id'         => fn() => \DB::table('traits')->inRandomOrder()->value('id') ?? 1,
            'name'             => fake()->firstName() . ' ' . fake()->lastName(),
            'level'            => 1,
            'xp'               => 0,
            'xp_to_next_level' => 100,
            'current_hp'       => 100,
            'max_hp'           => 100,
            'talent_points'    => 0,
            'slot_index'       => 1,
            'is_active'        => true,
            'deaths'           => 0,
        ];
    }
}
