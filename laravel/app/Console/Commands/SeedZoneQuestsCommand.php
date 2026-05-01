<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Generates and seeds permanent zone quests for one or all zones.
 *
 * Usage:
 *   php artisan quests:seed-zone --zone=marais_bureaucratie --count=3 --rarity=rare
 *   php artisan quests:seed-zone --all --count=3
 *   php artisan quests:seed-zone --zone=mines_nain --count=1 --steps=4 --rarity=epique --force
 */
class SeedZoneQuestsCommand extends Command
{
    protected $signature = 'quests:seed-zone
                            {--zone=         : Slug de la zone cible (ex: marais_bureaucratie)}
                            {--all           : Générer pour toutes les zones}
                            {--count=3       : Nombre de quêtes à générer par zone}
                            {--steps=3       : Nombre d\'étapes par quête (3 ou 4)}
                            {--rarity=rare   : Rareté du loot (commun|peu_commun|rare|epique|legendaire)}
                            {--force         : Regénère même si des quêtes existent déjà pour cette zone}';

    protected $description = 'Génère et insère des quêtes de zone permanentes via Gemini (ou templates statiques si IA indisponible).';

    private const VALID_RARITIES = ['commun', 'peu_commun', 'rare', 'epique', 'legendaire'];

    public function handle(GeminiService $gemini): int
    {
        $zoneSlug  = $this->option('zone');
        $doAll     = $this->option('all');
        $count     = max(1, min(10, (int) $this->option('count')));
        $steps     = max(2, min(5, (int) $this->option('steps')));
        $rarity    = in_array($this->option('rarity'), self::VALID_RARITIES) ? $this->option('rarity') : 'rare';
        $force     = $this->option('force');

        if (!$zoneSlug && !$doAll) {
            $this->error('Spécifiez --zone=<slug> ou --all.');
            return self::FAILURE;
        }

        $zones = $doAll
            ? DB::table('zones')->orderBy('order_index')->get(['id', 'slug', 'name', 'level_min', 'level_max'])
            : DB::table('zones')->where('slug', $zoneSlug)->get(['id', 'slug', 'name', 'level_min', 'level_max']);

        if ($zones->isEmpty()) {
            $this->error("Zone introuvable" . ($zoneSlug ? " : {$zoneSlug}" : '') . ". Vérifiez le slug ou lancez ZoneSeeder.");
            return self::FAILURE;
        }

        $totalInserted = 0;
        $totalSkipped  = 0;

        foreach ($zones as $zone) {
            $this->info("Zone : {$zone->name} (slug: {$zone->slug}, niv. {$zone->level_min}-{$zone->level_max})");

            $existing = DB::table('quests')
                ->where('zone_id', $zone->id)
                ->where('type', 'zone')
                ->count();

            if ($existing > 0 && !$force) {
                $this->line("  → {$existing} quête(s) de zone existante(s). Utilisez --force pour regénérer.");
                $totalSkipped += $count;
                continue;
            }

            $rewardXp   = $this->calcXp((int) $zone->level_min);
            $rewardGold = $this->calcGold((int) $zone->level_min);

            for ($i = 0; $i < $count; $i++) {
                $this->line("  Génération quête " . ($i + 1) . "/{$count}...");

                $questData = $gemini->generateZoneQuestFull(
                    $zone->slug,
                    $zone->name,
                    (int) $zone->level_min,
                    $steps,
                    $existing + $i
                );

                $exists = DB::table('quests')
                    ->where('zone_id', $zone->id)
                    ->where('title', $questData['title'])
                    ->exists();

                if ($exists) {
                    $this->line("  → '{$questData['title']}' existe déjà, ignorée.");
                    $totalSkipped++;
                    continue;
                }

                $questId = DB::table('quests')->insertGetId([
                    'zone_id'            => $zone->id,
                    'title'              => $questData['title'],
                    'description'        => $questData['description'],
                    'type'               => 'zone',
                    'steps_count'        => count($questData['steps']),
                    'order_index'        => $existing + $i + 1,
                    'reward_xp'          => $rewardXp,
                    'reward_gold'        => $rewardGold,
                    'reward_loot_rarity' => $rarity,
                    'is_repeatable'      => true,
                    'is_ai_generated'    => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                foreach ($questData['steps'] as $idx => $step) {
                    DB::table('quest_steps')->insert([
                        'quest_id'   => $questId,
                        'step_index' => $idx + 1,
                        'content'    => json_encode($step, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->line("  ✓ '{$questData['title']}' insérée ({$steps} étapes).");
                $totalInserted++;
            }
        }

        $this->info("Terminé — {$totalInserted} quête(s) insérée(s), {$totalSkipped} ignorée(s).");
        return self::SUCCESS;
    }

    private function calcXp(int $levelMin): int
    {
        return (int) DB::table('game_settings')
            ->where('setting_key', 'QUEST_XP_BASE')
            ->value('setting_value') ?: $levelMin * 30 + 200;
    }

    private function calcGold(int $levelMin): int
    {
        return (int) DB::table('game_settings')
            ->where('setting_key', 'GOLD_QUEST_ZONE_MULT')
            ->value('setting_value') ?: $levelMin * 18 + 150;
    }
}
