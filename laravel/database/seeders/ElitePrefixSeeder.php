<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElitePrefixSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('elite_prefixes')->truncate();

        // Multiplicateurs en centièmes (100 = ×1.0, 180 = ×1.8)
        // XP/gold/loot multipliers depuis BESTIARY.md §4.3
        $prefixes = [
            [
                'slug'           => 'enrage',
                'name'           => 'Enragé',
                'hp_multiplier'  => 100,
                'atq_multiplier' => 180,
                'def_multiplier' => 90,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'trigger'   => 'below_30_percent_hp',
                    'effect'    => 'double_attack',
                    'description' => 'Attaque deux fois quand sous 30% PV',
                ]),
            ],
            [
                'slug'           => 'blinde',
                'name'           => 'Blindé',
                'hp_multiplier'  => 100,
                'atq_multiplier' => 100,
                'def_multiplier' => 200,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'effect'      => 'reduce_crit_received',
                    'value'       => 50,
                    'description' => 'Réduit les critiques reçus de 50%',
                ]),
            ],
            [
                'slug'           => 'rapide',
                'name'           => 'Rapide',
                'hp_multiplier'  => 90,
                'atq_multiplier' => 100,
                'def_multiplier' => 100,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'vit_multiplier' => 200,
                    'effect'         => 'always_first',
                    'dodge_bonus'    => 15,
                    'description'    => 'Agit toujours en premier, 15% esquive bonus',
                ]),
            ],
            [
                'slug'           => 'vampirique',
                'name'           => 'Vampirique',
                'hp_multiplier'  => 130,
                'atq_multiplier' => 130,
                'def_multiplier' => 130,
                'xp_multiplier'  => 200,
                'gold_multiplier'=> 200,
                'loot_multiplier'=> 200,
                'effect_data'    => json_encode([
                    'effect'      => 'lifesteal',
                    'value'       => 20,
                    'description' => 'Soigne 20% des dégâts infligés',
                ]),
            ],
            [
                'slug'           => 'toxique',
                'name'           => 'Toxique',
                'hp_multiplier'  => 120,
                'atq_multiplier' => 120,
                'def_multiplier' => 120,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'effect'      => 'poison_on_hit',
                    'dot_percent' => 2,
                    'duration'    => 3,
                    'description' => 'Empoisonne (2% PV/tour pendant 3 tours)',
                ]),
            ],
            [
                'slug'           => 'geant',
                'name'           => 'Géant',
                'hp_multiplier'  => 250,
                'atq_multiplier' => 100,
                'def_multiplier' => 100,
                'xp_multiplier'  => 200,
                'gold_multiplier'=> 200,
                'loot_multiplier'=> 200,
                'effect_data'    => json_encode([
                    'vit_multiplier' => 70,
                    'effect'         => 'cleave',
                    'targets'        => 2,
                    'description'    => 'Ses attaques touchent 2 héros',
                ]),
            ],
            [
                'slug'           => 'spectral',
                'name'           => 'Spectral',
                'hp_multiplier'  => 110,
                'atq_multiplier' => 110,
                'def_multiplier' => 110,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'effect'      => 'phase_chance',
                    'value'       => 25,
                    'description' => '25% de chance de résister totalement à une attaque',
                ]),
            ],
            [
                'slug'           => 'beni',
                'name'           => 'Béni',
                'hp_multiplier'  => 120,
                'atq_multiplier' => 120,
                'def_multiplier' => 120,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'effect'      => 'regen',
                    'value'       => 3,
                    'description' => 'Régénère 3% PV max par tour',
                ]),
            ],
            [
                'slug'           => 'explosif',
                'name'           => 'Explosif',
                'hp_multiplier'  => 80,
                'atq_multiplier' => 150,
                'def_multiplier' => 100,
                'xp_multiplier'  => 175,
                'gold_multiplier'=> 175,
                'loot_multiplier'=> 175,
                'effect_data'    => json_encode([
                    'effect'       => 'death_explosion',
                    'dmg_percent'  => 100,
                    'description'  => 'À la mort, explose infligeant 100% ATQ à tous les héros',
                ]),
            ],
            [
                'slug'           => 'ancien',
                'name'           => 'Ancien',
                'hp_multiplier'  => 160,
                'atq_multiplier' => 160,
                'def_multiplier' => 160,
                'xp_multiplier'  => 250,
                'gold_multiplier'=> 250,
                'loot_multiplier'=> 250,
                'effect_data'    => json_encode([
                    'effect'      => 'extra_skill',
                    'description' => 'Possède une compétence supplémentaire',
                ]),
            ],
        ];

        DB::table('elite_prefixes')->insert($prefixes);
    }
}
