<?php

namespace App\Jobs;

use App\Models\Quest;
use App\Services\GeminiService;
use App\Services\SettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pre-generate a pool of daily quests for each zone using Gemini.
 *
 * Daily quests are generated once per day as a pool, then assigned to players
 * when they log in. If AI is unavailable, existing zone quests are used as
 * a fallback pool.
 *
 * Schedule: daily at 00:05 via `php artisan quests:generate`
 */
class GenerateDailyQuests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;
    public int $timeout = 120;

    /** Number of daily quests to pre-generate per zone */
    private const POOL_SIZE = 15;

    public function handle(GeminiService $gemini, SettingsService $settings): void
    {
        // Clean up yesterday's daily quest assignments
        DB::table('user_daily_quests')
            ->where('date', '<', today()->subDay())
            ->delete();

        // Get all active zones
        $zones = DB::table('zones')
            ->orderBy('order_index')
            ->get(['id', 'slug', 'name', 'level_min']);

        $totalGenerated = 0;

        foreach ($zones as $zone) {
            // Check if we already have enough daily quests for this zone today
            $existing = Quest::where('zone_id', $zone->id)
                ->where('type', 'daily')
                ->whereDate('created_at', today())
                ->count();

            if ($existing >= self::POOL_SIZE) {
                continue;
            }

            $needed = self::POOL_SIZE - $existing;

            for ($i = 0; $i < $needed; $i++) {
                if (!$gemini->canCall('quest')) {
                    // Budget exhausted — stop generating
                    Log::info("GenerateDailyQuests: AI budget exhausted after {$totalGenerated} quests.");
                    return;
                }

                try {
                    $questData = $gemini->generateQuestText($zone->slug, (int) $zone->level_min);
                    $this->insertDailyQuest($zone->id, $questData, (int) $zone->level_min);
                    $totalGenerated++;
                } catch (\Throwable $e) {
                    Log::warning("GenerateDailyQuests: failed for zone {$zone->slug}", ['error' => $e->getMessage()]);
                }
            }
        }

        Log::info("GenerateDailyQuests: {$totalGenerated} daily quests generated.");
    }

    private function insertDailyQuest(int $zoneId, array $data, int $level): void
    {
        $rewardGold = $level * $this->getGoldMult();

        DB::table('quests')->insert([
            'zone_id'         => $zoneId,
            'title'           => $data['title'],
            'description'     => $data['description'],
            'type'            => 'daily',
            'steps_count'     => count($data['steps'] ?? []) ?: 1,
            'reward_gold'     => $rewardGold,
            'reward_xp'       => intdiv($rewardGold, 2),
            'is_ai_generated' => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function getGoldMult(): int
    {
        return (int) DB::table('game_settings')
            ->where('setting_key', 'GOLD_QUEST_DAILY_MULT')
            ->value('setting_value') ?: 20;
    }
}
