<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonalEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'slug'                => 'noel_incompetent',
                'name'               => 'Noël des Incompétents',
                'description'        => 'Les monstres portent des bonnets de Noël. Ça ne les rend pas moins dangereux. Enfin, presque pas.',
                'flavor_text'        => 'Le Narrateur a commandé une dinde. Elle s\'est échappée.',
                'start_month'        => 12,
                'start_day'          => 20,
                'end_month'          => 1,
                'end_day'            => 5,
                'xp_bonus_pct'       => 25,
                'gold_bonus_pct'     => 15,
                'loot_bonus_pct'     => 20,
                'rare_loot_bonus_pct'=> 10,
                'quest_type_unlock'  => 'event',
                'is_active'          => true,
            ],
            [
                'slug'                => 'halloween_raté',
                'name'               => 'Halloween Raté',
                'description'        => 'Les monstres se déguisent en aventuriers. Les aventuriers ne savent pas si c\'est une menace ou une opportunité.',
                'flavor_text'        => 'Des bonbons ou un sort ? Gérard a choisi le sort. Par accident.',
                'start_month'        => 10,
                'start_day'          => 28,
                'end_month'          => 11,
                'end_day'            => 2,
                'xp_bonus_pct'       => 0,
                'gold_bonus_pct'     => 0,
                'loot_bonus_pct'     => 30,
                'rare_loot_bonus_pct'=> 25,
                'quest_type_unlock'  => 'wtf',
                'is_active'          => true,
            ],
            [
                'slug'                => 'saint_valentin_maudite',
                'name'               => 'Saint-Valentin Maudite',
                'description'        => 'Les monstres sont amoureux. Ça les rend distraits. Profitez-en pendant que vous pouvez.',
                'flavor_text'        => 'Le Narrateur a reçu une carte de vœux. De la part du Lich. Il est perturbé.',
                'start_month'        => 2,
                'start_day'          => 13,
                'end_month'          => 2,
                'end_day'            => 16,
                'xp_bonus_pct'       => 10,
                'gold_bonus_pct'     => 50,
                'loot_bonus_pct'     => 0,
                'rare_loot_bonus_pct'=> 5,
                'quest_type_unlock'  => null,
                'is_active'          => true,
            ],
            [
                'slug'                => 'semaine_forge',
                'name'               => 'Semaine de la Forge',
                'description'        => 'Gérard a décidé de travailler deux fois plus vite. Ça reste lent.',
                'flavor_text'        => 'Le marteau de Gérard a une émotion. De la culpabilité.',
                'start_month'        => 4,
                'start_day'          => 1,
                'end_month'          => 4,
                'end_day'            => 7,
                'xp_bonus_pct'       => 0,
                'gold_bonus_pct'     => 0,
                'loot_bonus_pct'     => 10,
                'rare_loot_bonus_pct'=> 15,
                'quest_type_unlock'  => null,
                'is_active'          => true,
            ],
            [
                'slug'                => 'anniversaire_donjon',
                'name'               => 'Anniversaire du Donjon',
                'description'        => 'Le Donjon des Incompétents fête ses x années d\'existence. Personne ne sait combien exactement.',
                'flavor_text'        => 'Le gâteau était un piège. Évidemment.',
                'start_month'        => 7,
                'start_day'          => 14,
                'end_month'          => 7,
                'end_day'            => 21,
                'xp_bonus_pct'       => 50,
                'gold_bonus_pct'     => 50,
                'loot_bonus_pct'     => 50,
                'rare_loot_bonus_pct'=> 20,
                'quest_type_unlock'  => 'wtf',
                'is_active'          => true,
            ],
        ];

        foreach ($events as $event) {
            DB::table('seasonal_events')->updateOrInsert(
                ['slug' => $event['slug']],
                $event
            );
        }
    }
}
