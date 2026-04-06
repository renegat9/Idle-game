<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    private static array $slots    = ['arme', 'armure', 'casque', 'bottes', 'accessoire'];
    private static array $rarities = ['commun', 'peu_commun', 'rare'];
    private static array $elements = ['physique', 'feu', 'glace', 'foudre', 'poison'];

    public function definition(): array
    {
        $rarity = fake()->randomElement(self::$rarities);
        $slot   = fake()->randomElement(self::$slots);
        $level  = fake()->numberBetween(1, 20);
        $mult   = ['commun' => 1, 'peu_commun' => 2, 'rare' => 3][$rarity];

        return [
            'user_id'             => \App\Models\User::factory(),
            'name'                => ucfirst(fake()->word()) . ' ' . ucfirst(fake()->word()),
            'description'         => fake()->sentence(),
            'rarity'              => $rarity,
            'slot'                => $slot,
            'element'             => fake()->randomElement(self::$elements),
            'item_level'          => $level,
            'atq'                 => $slot === 'arme' ? $level * $mult : 0,
            'def'                 => in_array($slot, ['armure', 'casque']) ? $level * $mult : 0,
            'hp'                  => 0,
            'vit'                 => $slot === 'bottes' ? $level * $mult : 0,
            'cha'                 => 0,
            'int'                 => 0,
            'sell_value'          => $level * $mult * 3,
            'equipped_by_hero_id' => null,
            'is_ai_generated'     => false,
            'durability_current'  => 100,
            'durability_max'      => 100,
            'enchant_count'       => 0,
        ];
    }
}
