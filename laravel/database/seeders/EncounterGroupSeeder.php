<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EncounterGroupSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $ratId = DB::table('monsters')->where('slug', 'rat_peureux')->value('id');
        $slimeId = DB::table('monsters')->where('slug', 'slime_vert')->value('id');
        $gobId = DB::table('monsters')->where('slug', 'gobelin_chapardeur')->value('id');
        $loupId = DB::table('monsters')->where('slug', 'loup_solitaire')->value('id');
        $epouvId = DB::table('monsters')->where('slug', 'epouvantail_anime')->value('id');
        $abeilleId = DB::table('monsters')->where('slug', 'abeille_geante')->value('id');
        $fermierId = DB::table('monsters')->where('slug', 'fermier_possede')->value('id');
        $taureauId = DB::table('monsters')->where('slug', 'taureau_pre_maudit')->value('id');

        $groups = [
            // Niveau 1-2
            ['zone_id' => $prairieId, 'name' => 'Un rat', 'monster_ids' => json_encode([$ratId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 30, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Deux rats', 'monster_ids' => json_encode([$ratId, $ratId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Un slime', 'monster_ids' => json_encode([$slimeId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Rat et Slime', 'monster_ids' => json_encode([$ratId, $slimeId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 25, 'is_boss_encounter' => 0],
            // Niveau 2-3
            ['zone_id' => $prairieId, 'name' => 'Gobelin seul', 'monster_ids' => json_encode([$gobId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Loup solitaire', 'monster_ids' => json_encode([$loupId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Gobelin et Rat', 'monster_ids' => json_encode([$gobId, $ratId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Deux Gobelins', 'monster_ids' => json_encode([$gobId, $gobId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            // Niveau 3-5
            ['zone_id' => $prairieId, 'name' => 'Épouvantail', 'monster_ids' => json_encode([$epouvId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Abeilles furieuses', 'monster_ids' => json_encode([$abeilleId, $abeilleId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Loup et Gobelin', 'monster_ids' => json_encode([$loupId, $gobId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Trio de gobelins', 'monster_ids' => json_encode([$gobId, $gobId, $gobId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Épouvantail et Abeilles', 'monster_ids' => json_encode([$epouvId, $abeilleId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 25, 'is_boss_encounter' => 0],
            // Mini-boss (poids faible = rare)
            ['zone_id' => $prairieId, 'name' => 'Le Fermier Possédé', 'monster_ids' => json_encode([$fermierId]), 'level_min' => 4, 'level_max' => 5, 'weight' => 5, 'is_boss_encounter' => 0],
            // Boss (déclenché manuellement)
            ['zone_id' => $prairieId, 'name' => 'Le Taureau du Pré Maudit', 'monster_ids' => json_encode([$taureauId]), 'level_min' => 5, 'level_max' => 5, 'weight' => 0, 'is_boss_encounter' => 1],
        ];

        foreach ($groups as $group) {
            DB::table('encounter_groups')->insert($group);
        }
    }
}
