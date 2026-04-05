<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $monsters = [
            // Monstres normaux Zone 1
            [
                'zone_id' => $prairieId,
                'name' => 'Rat Peureux',
                'slug' => 'rat_peureux',
                'monster_type' => 'normal',
                'level' => 1,
                'base_hp' => 15,
                'base_atq' => 3,
                'base_def' => 1,
                'base_vit' => 8,
                'base_int' => 0,
                'base_cha' => 0,
                'element' => 'physique',
                'xp_reward' => 12,
                'gold_min' => 0,
                'gold_max' => 2,
                'loot_bonus' => 0,
                'behavior_data' => json_encode(['priority' => 'random']),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            [
                'zone_id' => $prairieId,
                'name' => 'Slime Vert',
                'slug' => 'slime_vert',
                'monster_type' => 'normal',
                'level' => 1,
                'base_hp' => 25,
                'base_atq' => 2,
                'base_def' => 3,
                'base_vit' => 2,
                'base_int' => 0,
                'base_cha' => 0,
                'element' => 'poison',
                'xp_reward' => 10,
                'gold_min' => 1,
                'gold_max' => 3,
                'loot_bonus' => 5,
                'behavior_data' => json_encode(['priority' => 'lowest_hp']),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            [
                'zone_id' => $prairieId,
                'name' => 'Gobelin Chapardeur',
                'slug' => 'gobelin_chapardeur',
                'monster_type' => 'normal',
                'level' => 2,
                'base_hp' => 20,
                'base_atq' => 5,
                'base_def' => 2,
                'base_vit' => 10,
                'base_int' => 0,
                'base_cha' => 0,
                'element' => 'physique',
                'xp_reward' => 18,
                'gold_min' => 3,
                'gold_max' => 8,
                'loot_bonus' => 10,
                'behavior_data' => json_encode(['priority' => 'lowest_hp', 'steals_gold' => true]),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            [
                'zone_id' => $prairieId,
                'name' => 'Loup Solitaire',
                'slug' => 'loup_solitaire',
                'monster_type' => 'normal',
                'level' => 2,
                'base_hp' => 30,
                'base_atq' => 7,
                'base_def' => 2,
                'base_vit' => 12,
                'base_int' => 0,
                'base_cha' => 0,
                'element' => 'physique',
                'xp_reward' => 22,
                'gold_min' => 0,
                'gold_max' => 5,
                'loot_bonus' => 5,
                'behavior_data' => json_encode(['priority' => 'random']),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            [
                'zone_id' => $prairieId,
                'name' => 'Épouvantail Animé',
                'slug' => 'epouvantail_anime',
                'monster_type' => 'normal',
                'level' => 3,
                'base_hp' => 35,
                'base_atq' => 6,
                'base_def' => 4,
                'base_vit' => 4,
                'base_int' => 2,
                'base_cha' => 0,
                'element' => 'physique',
                'xp_reward' => 28,
                'gold_min' => 2,
                'gold_max' => 6,
                'loot_bonus' => 0,
                'behavior_data' => json_encode(['priority' => 'random', 'fear_chance' => 15]),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            [
                'zone_id' => $prairieId,
                'name' => 'Abeille Géante',
                'slug' => 'abeille_geante',
                'monster_type' => 'normal',
                'level' => 3,
                'base_hp' => 20,
                'base_atq' => 8,
                'base_def' => 1,
                'base_vit' => 15,
                'base_int' => 0,
                'base_cha' => 0,
                'element' => 'poison',
                'xp_reward' => 25,
                'gold_min' => 0,
                'gold_max' => 3,
                'loot_bonus' => 0,
                'behavior_data' => json_encode(['priority' => 'random', 'poison_chance' => 25]),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            // Mini-boss Zone 1
            [
                'zone_id' => $prairieId,
                'name' => 'Le Fermier Possédé',
                'slug' => 'fermier_possede',
                'monster_type' => 'mini_boss',
                'level' => 4,
                'base_hp' => 150,
                'base_atq' => 20,
                'base_def' => 12,
                'base_vit' => 8,
                'base_int' => 5,
                'base_cha' => 0,
                'element' => 'ombre',
                'xp_reward' => 120,
                'gold_min' => 20,
                'gold_max' => 40,
                'loot_bonus' => 25,
                'behavior_data' => json_encode(['priority' => 'highest_atq', 'enrage_at_50_percent' => true]),
                'phase2_data' => null,
                'is_active' => 1,
            ],
            // Boss Zone 1
            [
                'zone_id' => $prairieId,
                'name' => 'Le Taureau du Pré Maudit',
                'slug' => 'taureau_pre_maudit',
                'monster_type' => 'boss',
                'level' => 5,
                'base_hp' => 400,
                'base_atq' => 25,
                'base_def' => 15,
                'base_vit' => 10,
                'base_int' => 3,
                'base_cha' => 0,
                'element' => 'physique',
                'xp_reward' => 300,
                'gold_min' => 50,
                'gold_max' => 100,
                'loot_bonus' => 50,
                'behavior_data' => json_encode(['priority' => 'random', 'charge_every_3_turns' => true]),
                'phase2_data' => json_encode([
                    'hp_threshold' => 50,
                    'stat_multiplier' => 130,
                    'new_skill' => 'Charge Frénétique',
                    'narrator_text' => 'Le taureau est en colère. Plus qu\'avant. C\'est possible.',
                ]),
                'is_active' => 1,
            ],
        ];

        foreach ($monsters as $monster) {
            DB::table('monsters')->updateOrInsert(['slug' => $monster['slug']], $monster);
        }

        // Compétences des monstres
        $this->seedMonsterSkills();
    }

    private function seedMonsterSkills(): void
    {
        $skills = [
            ['monster_slug' => 'gobelin_chapardeur', 'name' => 'Vol Éclair', 'description' => 'Vole de l\'or au joueur', 'skill_type' => 'special', 'damage_percent' => 0, 'cooldown_turns' => 3, 'use_chance' => 30, 'effect_data' => json_encode(['gold_steal_percent' => 10])],
            ['monster_slug' => 'abeille_geante', 'name' => 'Dard Vénéneux', 'description' => 'Inflige du poison pour 3 tours', 'skill_type' => 'debuff', 'damage_percent' => 80, 'cooldown_turns' => 2, 'use_chance' => 40, 'effect_data' => json_encode(['poison_damage_per_turn' => 3, 'duration' => 3])],
            ['monster_slug' => 'fermier_possede', 'name' => 'Coup de Fourche Démoniaque', 'description' => 'Attaque lourde qui réduit la DEF', 'skill_type' => 'attaque', 'damage_percent' => 150, 'cooldown_turns' => 3, 'use_chance' => 40, 'effect_data' => json_encode(['def_reduce_percent' => 20, 'duration' => 2])],
            ['monster_slug' => 'taureau_pre_maudit', 'name' => 'Charge Bestiale', 'description' => 'Frappe tout le monde', 'skill_type' => 'attaque', 'damage_percent' => 120, 'cooldown_turns' => 3, 'use_chance' => 35, 'effect_data' => json_encode(['targets' => 'all_heroes'])],
        ];

        foreach ($skills as $skill) {
            $monsterId = DB::table('monsters')->where('slug', $skill['monster_slug'])->value('id');
            if ($monsterId) {
                unset($skill['monster_slug']);
                $skill['monster_id'] = $monsterId;
                DB::table('monster_skills')->insert($skill);
            }
        }
    }
}
