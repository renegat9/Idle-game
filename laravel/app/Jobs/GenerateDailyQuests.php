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

        // Repair daily quests that exist without steps
        $this->repairMissingSteps();

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

        $questId = DB::table('quests')->insertGetId([
            'zone_id'         => $zoneId,
            'title'           => $data['title'],
            'description'     => $data['description'],
            'type'            => 'daily',
            'steps_count'     => 1,
            'reward_gold'     => $rewardGold,
            'reward_xp'       => intdiv($rewardGold, 2),
            'is_ai_generated' => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $stats = ['atq', 'def', 'vit', 'int', 'cha'];
        $stat  = $stats[array_rand($stats)];
        $diff  = max(20, min(45, $level + 15));

        $step = [
            'narration'        => $data['description'],
            'narrator_comment' => $data['flavor'] ?? 'Le Narrateur observe. Sans trop s\'impliquer.',
            'is_final'         => true,
            'choices'          => [
                [
                    'id'      => 'A',
                    'text'    => 'Relever le défi (test ' . strtoupper($stat) . ')',
                    'test'    => ['stat' => $stat, 'difficulty' => $diff],
                    'success' => ['next_step' => null, 'effects' => [], 'narration' => 'Bien joué. Vos héros s\'en sortent avec panache relatif.'],
                    'failure' => ['next_step' => null, 'effects' => [['type' => 'debuff', 'id' => 'D01', 'target' => 'party']], 'narration' => 'Raté, mais la récompense est là quand même. Le Narrateur est généreux aujourd\'hui.'],
                ],
                [
                    'id'      => 'B',
                    'text'    => 'Improviser (pas de test)',
                    'test'    => null,
                    'success' => ['next_step' => null, 'effects' => [], 'narration' => 'L\'improvisation fonctionne. Personne ne comprend pourquoi.'],
                    'failure' => null,
                ],
            ],
        ];

        DB::table('quest_steps')->insert([
            'quest_id'   => $questId,
            'step_index' => 1,
            'content'    => json_encode($step, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function repairMissingSteps(): void
    {
        $broken = DB::table('quests as q')
            ->leftJoin('quest_steps as qs', 'qs.quest_id', '=', 'q.id')
            ->where('q.type', 'daily')
            ->whereNull('qs.id')
            ->select('q.id', 'q.title', 'q.description')
            ->get();

        foreach ($broken as $quest) {
            $stats = ['atq', 'def', 'vit', 'int', 'cha'];
            $stat  = $stats[array_rand($stats)];

            $step = [
                'narration'        => $quest->description,
                'narrator_comment' => 'Le Narrateur observe. Sans trop s\'impliquer.',
                'is_final'         => true,
                'choices'          => [
                    [
                        'id'      => 'A',
                        'text'    => 'Relever le défi (test ' . strtoupper($stat) . ')',
                        'test'    => ['stat' => $stat, 'difficulty' => 30],
                        'success' => ['next_step' => null, 'effects' => [], 'narration' => 'Réussi. Le Narrateur hoche la tête.'],
                        'failure' => ['next_step' => null, 'effects' => [['type' => 'debuff', 'id' => 'D01', 'target' => 'party']], 'narration' => 'Raté, mais la récompense est là quand même.'],
                    ],
                    [
                        'id'      => 'B',
                        'text'    => 'Improviser (pas de test)',
                        'test'    => null,
                        'success' => ['next_step' => null, 'effects' => [], 'narration' => 'L\'improvisation fonctionne. Personne ne comprend pourquoi.'],
                        'failure' => null,
                    ],
                ],
            ];

            DB::table('quest_steps')->insert([
                'quest_id'   => $quest->id,
                'step_index' => 1,
                'content'    => json_encode($step, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($broken->count() > 0) {
            Log::info("GenerateDailyQuests: repaired {$broken->count()} daily quest(s) missing steps.");
        }
    }

    private function getGoldMult(): int
    {
        return (int) DB::table('game_settings')
            ->where('setting_key', 'GOLD_QUEST_DAILY_MULT')
            ->value('setting_value') ?: 20;
    }
}
