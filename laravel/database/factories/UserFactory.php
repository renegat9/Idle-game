<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username'         => fake()->unique()->userName(),
            'email'            => fake()->unique()->safeEmail(),
            'password'         => static::$password ??= Hash::make('password'),
            'gold'             => 500,
            'level'            => 1,
            'xp'               => 0,
            'xp_to_next_level' => 100,
            'narrator_frequency' => 'normal',
        ];
    }
}
