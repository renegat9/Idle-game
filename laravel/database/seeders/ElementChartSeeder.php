<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElementChartSeeder extends Seeder
{
    public function run(): void
    {
        $elements = ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'];

        // Multiplicateurs: 100=neutre, 150=efficace, 50=résistant, 200=super efficace
        $chart = [
            // attaquant => [défenseur => multiplicateur]
            'physique' => ['physique' => 100, 'feu' => 100, 'glace' => 100, 'foudre' => 100, 'poison' => 100, 'sacre' => 100, 'ombre' => 100],
            'feu'      => ['physique' => 100, 'feu' => 50,  'glace' => 200, 'foudre' => 100, 'poison' => 100, 'sacre' => 100, 'ombre' => 150],
            'glace'    => ['physique' => 100, 'feu' => 50,  'glace' => 50,  'foudre' => 150, 'poison' => 100, 'sacre' => 100, 'ombre' => 100],
            'foudre'   => ['physique' => 100, 'feu' => 100, 'glace' => 150, 'foudre' => 50,  'poison' => 150, 'sacre' => 100, 'ombre' => 100],
            'poison'   => ['physique' => 100, 'feu' => 100, 'glace' => 100, 'foudre' => 50,  'poison' => 50,  'sacre' => 50,  'ombre' => 150],
            'sacre'    => ['physique' => 100, 'feu' => 100, 'glace' => 100, 'foudre' => 100, 'poison' => 200, 'sacre' => 50,  'ombre' => 200],
            'ombre'    => ['physique' => 100, 'feu' => 50,  'glace' => 100, 'foudre' => 100, 'poison' => 100, 'sacre' => 200, 'ombre' => 50],
        ];

        foreach ($elements as $attacker) {
            foreach ($elements as $defender) {
                DB::table('element_chart')->updateOrInsert(
                    ['attacker_element' => $attacker, 'defender_element' => $defender],
                    ['damage_multiplier' => $chart[$attacker][$defender]]
                );
            }
        }
    }
}
