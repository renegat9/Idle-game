<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableSeeder extends Seeder
{
    public function run(): void
    {
        $consumables = [
            [
                'slug'        => 'potion_soin_petit',
                'name'        => 'Petite Potion de Soin',
                'description' => 'Restaure 50 HP à toute l\'équipe.',
                'flavor_text' => '"Goût framboise. Ou peut-être de chaussette. Difficile à dire." — Gérard',
                'effect_type' => 'heal_hp',
                'effect_value' => 50,
                'duration_turns' => 0,
                'rarity'      => 'commun',
                'buy_price'   => 15,
                'sell_value'  => 5,
                'stack_max'   => 99,
            ],
            [
                'slug'        => 'potion_soin_grand',
                'name'        => 'Grande Potion de Soin',
                'description' => 'Restaure 200 HP à toute l\'équipe.',
                'flavor_text' => '"Cette fois c\'est du sérieux. Enfin, autant qu\'on peut l\'être." — Gérard',
                'effect_type' => 'heal_hp',
                'effect_value' => 200,
                'duration_turns' => 0,
                'rarity'      => 'peu_commun',
                'buy_price'   => 50,
                'sell_value'  => 20,
                'stack_max'   => 99,
            ],
            [
                'slug'        => 'elixir_vigueur',
                'name'        => 'Élixir de Vigueur',
                'description' => 'Restaure 50% des HP maximum de toute l\'équipe.',
                'flavor_text' => '"Ça pue mais ça marche. Plus ou moins." — Gérard',
                'effect_type' => 'restore_hp_pct',
                'effect_value' => 50,
                'duration_turns' => 0,
                'rarity'      => 'rare',
                'buy_price'   => 120,
                'sell_value'  => 50,
                'stack_max'   => 20,
            ],
            [
                'slug'        => 'parchemin_experience',
                'name'        => 'Parchemin d\'Expérience',
                'description' => 'Accorde 100 XP à tous les héros.',
                'flavor_text' => '"Lu et approuvé par quelqu\'un qui savait lire." — sceau illisible',
                'effect_type' => 'xp_boost',
                'effect_value' => 100,
                'duration_turns' => 0,
                'rarity'      => 'peu_commun',
                'buy_price'   => 80,
                'sell_value'  => 30,
                'stack_max'   => 10,
            ],
            [
                'slug'        => 'potion_or',
                'name'        => 'Potion d\'Avarice',
                'description' => 'Accorde 50 pièces d\'or. Oui, c\'est une potion.',
                'flavor_text' => '"Ne demandez pas comment ça fonctionne." — Gérard',
                'effect_type' => 'gold_boost',
                'effect_value' => 50,
                'duration_turns' => 0,
                'rarity'      => 'commun',
                'buy_price'   => 30,
                'sell_value'  => 10,
                'stack_max'   => 99,
            ],
            [
                'slug'        => 'antidote_incompetence',
                'name'        => 'Antidote d\'Incompétence',
                'description' => 'Supprime tous les debuffs actifs de l\'équipe.',
                'flavor_text' => '"Efficace contre les malédictions, les empoisonnements, et les lundis matin." — étiquette',
                'effect_type' => 'cure_debuff',
                'effect_value' => 0,
                'duration_turns' => 0,
                'rarity'      => 'rare',
                'buy_price'   => 200,
                'sell_value'  => 80,
                'stack_max'   => 5,
            ],
        ];

        foreach ($consumables as $c) {
            DB::table('consumables')->updateOrInsert(['slug' => $c['slug']], $c);
        }
    }
}
