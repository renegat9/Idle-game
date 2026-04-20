<?php

namespace Database\Seeders;

use Database\Seeders\Talents\BarbareTalents;
use Database\Seeders\Talents\BardeTalents;
use Database\Seeders\Talents\GuerrierTalents;
use Database\Seeders\Talents\MageTalents;
use Database\Seeders\Talents\NecromancienTalents;
use Database\Seeders\Talents\PretreTalents;
use Database\Seeders\Talents\RangerTalents;
use Database\Seeders\Talents\VoleurTalents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder orchestrateur : délègue à 8 fichiers par classe (talents/*.php).
 * Chaque classe = 21 talents (3 branches × 7). Total = 168 talents.
 * GDD : TALENT_TREES.md.
 */
class TalentSeeder extends Seeder
{
    private const CLASS_TALENT_MAP = [
        'guerrier'     => GuerrierTalents::class,
        'mage'         => MageTalents::class,
        'voleur'       => VoleurTalents::class,
        'ranger'       => RangerTalents::class,
        'pretre'       => PretreTalents::class,
        'barde'        => BardeTalents::class,
        'barbare'      => BarbareTalents::class,
        'necromancien' => NecromancienTalents::class,
    ];

    public function run(): void
    {
        $ids = DB::table('classes')->pluck('id', 'slug');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('hero_talents')->truncate();
        DB::table('talents')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $all = [];
        foreach (self::CLASS_TALENT_MAP as $slug => $seederClass) {
            if (!isset($ids[$slug])) {
                continue;
            }
            $all = array_merge($all, $seederClass::talents((int) $ids[$slug]));
        }

        foreach (array_chunk($all, 50) as $chunk) {
            DB::table('talents')->insert($chunk);
        }
    }
}
