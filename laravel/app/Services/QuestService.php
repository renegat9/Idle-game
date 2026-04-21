<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestStep;
use App\Models\UserQuest;
use App\Models\User;
use App\Models\Hero;
use App\Services\Quest\QuestEffectService;
use App\Services\Quest\QuestValidator;
use App\Services\Quest\SurpriseEventService;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates quest flow: available quests, start, choice resolution,
 * daily quests, and end-of-quest rewards.
 *
 * Effect application is delegated to QuestEffectService.
 * Surprise events to SurpriseEventService.
 * Gemini validation to QuestValidator.
 */
class QuestService
{
    public function __construct(
        private readonly SettingsService    $settings,
        private readonly LootService        $loot,
        private readonly NarratorService    $narrator,
        private readonly QuestEffectService $effectService,
        private readonly SurpriseEventService $surpriseService,
        private readonly QuestValidator     $validator,
    ) {}

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Return quests available to the user for their current zone.
     */
    public function availableQuests(User $user): array
    {
        $zoneId = $user->current_zone_id;
        if (!$zoneId) return [];

        $quests = Quest::where('zone_id', $zoneId)
            ->where('type', 'zone')
            ->orderBy('order_index')
            ->get();

        $result = [];
        foreach ($quests as $quest) {
            $userQuest = UserQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->first();

            $status = 'available';
            if ($userQuest) {
                $status = $userQuest->status;
                if ($status === 'completed' && !$quest->is_repeatable) {
                    continue;
                }
            }

            $result[] = [
                'id'                 => $quest->id,
                'title'              => $quest->title,
                'description'        => $quest->description,
                'type'               => $quest->type,
                'steps_count'        => $quest->steps_count,
                'order_index'        => $quest->order_index,
                'reward_xp'          => $quest->reward_xp,
                'reward_gold'        => $quest->reward_gold,
                'reward_loot_rarity' => $quest->reward_loot_rarity,
                'status'             => $status,
                'current_step'       => $userQuest?->current_step ?? 1,
                'user_quest_id'      => $userQuest?->id,
            ];
        }

        return $result;
    }

    /**
     * Start a quest (or resume an in_progress one).
     */
    public function startQuest(User $user, int $questId): array
    {
        $quest = Quest::with('steps')->findOrFail($questId);

        $existing = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $questId)
            ->first();

        if ($existing?->status === 'in_progress') {
            return $this->buildStepResponse($quest, $existing);
        }

        if ($existing?->status === 'completed' && !$quest->is_repeatable) {
            return ['error' => 'Cette quête est déjà terminée.'];
        }

        $userQuest = DB::transaction(function () use ($user, $quest, $existing) {
            if ($existing) {
                $existing->update([
                    'status'        => 'in_progress',
                    'current_step'  => 1,
                    'step_results'  => null,
                    'heroic_score'  => 0, 'cunning_score' => 0,
                    'comic_score'   => 0, 'cautious_score' => 0,
                    'started_at'    => now(),
                    'completed_at'  => null,
                ]);
                return $existing->fresh();
            }
            return UserQuest::create([
                'user_id'       => $user->id,
                'quest_id'      => $quest->id,
                'status'        => 'in_progress',
                'current_step'  => 1,
                'started_at'    => now(),
            ]);
        });

        return $this->buildStepResponse($quest, $userQuest);
    }

    /**
     * Process a player's choice for the current step.
     */
    public function chooseOption(User $user, int $userQuestId, string $choiceId, ?int $heroId): array
    {
        $userQuest = UserQuest::with(['quest.steps'])->where('id', $userQuestId)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $quest  = $userQuest->quest;
        $step   = $quest->steps->firstWhere('step_index', $userQuest->current_step);

        if (!$step) return ['error' => 'Étape introuvable.'];

        $choices = $step->content['choices'] ?? [];
        $choice  = collect($choices)->firstWhere('id', $choiceId);
        if (!$choice) return ['error' => 'Choix invalide.'];

        // Resolve stat test
        $success   = true;
        $rollResult = null;
        if (!empty($choice['test']) && ($choice['test']['type'] ?? '') !== 'combat') {
            [$success, $rollResult] = $this->resolveStatTest($user, $choice['test'], $heroId);
        }

        $branch = $success ? ($choice['success'] ?? null) : ($choice['failure'] ?? null);
        if (!$branch) {
            $branch  = $choice['success'];
            $success = true;
        }

        // Apply branch effects
        $appliedEffects = [];
        foreach ($branch['effects'] ?? [] as $effect) {
            $applied = $this->effectService->apply($user, $effect);
            if ($applied) $appliedEffects[] = $applied;
        }

        // Roll for surprise event
        $surprise = $this->surpriseService->maybeTriggered($user, $userQuest);
        if ($surprise) {
            $appliedEffects[] = $surprise;
        }

        // Update voice score
        $voiceField = $this->detectVoice($choiceId, $choice['text'] ?? '');

        $stepResults   = $userQuest->step_results ?? [];
        $stepResults[] = [
            'step'    => $userQuest->current_step,
            'choice'  => $choiceId,
            'success' => $success,
            'roll'    => $rollResult,
            'effects' => $appliedEffects,
        ];

        $nextStep = $branch['next_step'] ?? null;
        $isFinal  = ($nextStep === null) || ($step->content['is_final'] ?? false);

        DB::transaction(function () use ($userQuest, $nextStep, $stepResults, $voiceField, $isFinal) {
            $updates = [
                'step_results' => $stepResults,
                $voiceField    => $userQuest->$voiceField + 1,
            ];
            $isFinal
                ? ($updates += ['status' => 'completed', 'completed_at' => now()])
                : ($updates['current_step'] = $nextStep);

            $userQuest->update($updates);
        });

        $response = [
            'success'         => $success,
            'narration'       => $branch['narration'] ?? '',
            'effects_applied' => $appliedEffects,
            'roll'            => $rollResult,
            'is_final'        => $isFinal,
        ];

        if ($isFinal) {
            $response['rewards']          = $this->grantRewards($user, $quest, $userQuest->fresh());
            $response['narrator_comment'] = $this->narrator->getComment(
                'quest_completed', ['quest_title' => $quest->title]
            );
        } else {
            $nextStepModel = $quest->steps->firstWhere('step_index', $nextStep);
            if ($nextStepModel) {
                $response['next_step'] = $this->sanitizeStep($nextStepModel);
            }
        }

        return $response;
    }

    /**
     * Validate a Gemini-generated quest before storing it.
     * Returns sanitized quest data or null (→ caller must use static fallback).
     */
    public function validateAiQuest(array $questData): ?array
    {
        return $this->validator->validateAndSanitize($questData);
    }

    // ── Daily quests ──────────────────────────────────────────────────────────

    /**
     * Return the user's daily quests for today, assigning from pool if needed.
     */
    public function dailyQuests(User $user): array
    {
        $today  = today()->toDateString();
        $zoneId = $user->current_zone_id;
        $limit  = (int) $this->settings->get('DAILY_QUEST_COUNT', 3);

        $existing = DB::table('user_daily_quests as udq')
            ->join('quests as q', 'q.id', '=', 'udq.quest_id')
            ->where('udq.user_id', $user->id)
            ->whereDate('udq.date', $today)
            ->select('q.*', 'udq.id as user_daily_id', 'udq.status as daily_status')
            ->get();

        if ($existing->count() >= $limit) {
            return $this->formatDailyResponse($existing, $today);
        }

        // Pull from AI pool for today
        $pool = Quest::where('type', 'daily')
            ->where('zone_id', $zoneId)
            ->whereDate('created_at', $today)
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        // Fallback: static zone quests
        if ($pool->isEmpty()) {
            $pool = Quest::where('zone_id', $zoneId)
                ->where('type', 'zone')
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        $assigned = collect();
        foreach ($pool as $quest) {
            $alreadyAssigned = DB::table('user_daily_quests')
                ->where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->whereDate('date', $today)
                ->exists();

            if ($alreadyAssigned) continue;

            $udqId = DB::table('user_daily_quests')->insertGetId([
                'user_id'  => $user->id,
                'quest_id' => $quest->id,
                'date'     => $today,
                'status'   => 'available',
            ]);

            $assigned->push((object) array_merge(
                (array) $quest->getAttributes(),
                ['user_daily_id' => $udqId, 'daily_status' => 'available']
            ));
        }

        return $this->formatDailyResponse($existing->concat($assigned)->take($limit), $today);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function resolveStatTest(User $user, array $test, ?int $heroId): array
    {
        $stat       = $test['stat'] ?? 'atq';
        $difficulty = $test['difficulty'] ?? 30;

        $heroes = $user->activeHeroes()->get();
        $hero   = $heroId
            ? $heroes->firstWhere('id', $heroId)
            : $heroes->sortByDesc(fn($h) => $h->computedStats()[$stat] ?? 0)->first();

        if (!$hero) return [false, null];

        $stats   = $hero->computedStats();
        $statVal = $stats[$stat] ?? 0;
        $roll    = rand(1, 20);
        $total   = $statVal + $roll;

        // Trait modifiers
        $trait = $hero->trait_;
        if ($trait) {
            $total += (int) ($test['trait_bonus'][$trait->name] ?? 0);
            $total += (int) ($test['trait_malus'][$trait->name] ?? 0);
        }

        return [$total >= $difficulty, ['roll' => $roll, 'stat' => $statVal, 'total' => $total, 'difficulty' => $difficulty]];
    }

    private function grantRewards(User $user, Quest $quest, UserQuest $userQuest): array
    {
        $xp   = $quest->reward_xp;
        $gold = $quest->reward_gold;

        $scores        = ['heroic' => $userQuest->heroic_score, 'cunning' => $userQuest->cunning_score, 'comic' => $userQuest->comic_score, 'cautious' => $userQuest->cautious_score];
        $dominantVoice = array_search(max($scores), $scores);

        $xpBonus   = 0;
        $goldBonus = 0;
        switch ($dominantVoice) {
            case 'heroic':
                $xpBonus = intdiv($xp * (int) $this->settings->get('QUEST_VOICE_HEROIC_XP_BONUS', 25), 100);
                break;
            case 'cunning':
                $goldBonus = intdiv($gold * (int) $this->settings->get('QUEST_VOICE_CUNNING_GOLD_BONUS', 25), 100);
                break;
            case 'comic':
                $b         = (int) $this->settings->get('QUEST_VOICE_COMIC_ALL_BONUS', 10);
                $xpBonus   = intdiv($xp * $b, 100);
                $goldBonus = intdiv($gold * $b, 100);
                break;
        }

        $totalXp   = $xp + $xpBonus;
        $totalGold = $gold + $goldBonus;

        DB::transaction(function () use ($user, $totalXp, $totalGold) {
            $user->increment('gold', $totalGold);
            $heroCount = max(1, $user->activeHeroes()->count());
            foreach ($user->activeHeroes()->get() as $hero) {
                $this->grantHeroXp($hero, intdiv($totalXp, $heroCount));
            }
        });

        $lootItem = $quest->reward_loot_rarity
            ? $this->loot->rollQuestLoot($user, $quest->reward_loot_rarity)
            : null;

        return [
            'xp'             => $totalXp,
            'xp_bonus'       => $xpBonus,
            'gold'           => $totalGold,
            'gold_bonus'     => $goldBonus,
            'dominant_voice' => $dominantVoice,
            'loot'           => $lootItem ? ['name' => $lootItem->name, 'rarity' => $lootItem->rarity] : null,
        ];
    }

    private function grantHeroXp(Hero $hero, int $xp): void
    {
        $hero->xp += $xp;
        while ($hero->xp >= $hero->xp_to_next_level) {
            $hero->xp -= $hero->xp_to_next_level;
            $hero->level++;
            $hero->xp_to_next_level = intdiv($hero->xp_to_next_level * 115, 100);
            $hero->talent_points++;
        }
        $hero->save();
    }

    private function buildStepResponse(Quest $quest, UserQuest $userQuest): array
    {
        $step = $quest->steps->firstWhere('step_index', $userQuest->current_step);
        return [
            'user_quest_id' => $userQuest->id,
            'quest_id'      => $quest->id,
            'quest_title'   => $quest->title,
            'current_step'  => $userQuest->current_step,
            'total_steps'   => $quest->steps_count,
            'step'          => $step ? $this->sanitizeStep($step) : null,
        ];
    }

    private function sanitizeStep(QuestStep $step): array
    {
        $content = $step->content;
        $choices = collect($content['choices'] ?? [])->map(function ($c) {
            if (!empty($c['test'])) {
                $c['test'] = ['stat' => $c['test']['stat'] ?? null, 'has_test' => true, 'type' => $c['test']['type'] ?? 'stat'];
            }
            unset($c['success']['effects'], $c['failure']['effects']);
            return $c;
        })->toArray();

        return [
            'step_index'       => $step->step_index,
            'narration'        => $content['narration'] ?? '',
            'narrator_comment' => $content['narrator_comment'] ?? '',
            'is_final'         => $content['is_final'] ?? false,
            'choices'          => $choices,
        ];
    }

    private function detectVoice(string $choiceId, string $text): string
    {
        $text = strtolower($text);
        if (str_contains($text, 'héroïque') || str_contains($text, 'courageuse') || str_contains($text, 'protéger')) {
            return 'heroic_score';
        }
        if (str_contains($text, 'or') || str_contains($text, 'maling') || str_contains($text, 'égoïste')) {
            return 'cunning_score';
        }
        if (str_contains($text, 'comique') || str_contains($text, 'absurde') || str_contains($text, 'hasard')) {
            return 'comic_score';
        }
        return match ($choiceId) {
            'A'     => 'heroic_score',
            'B'     => 'cautious_score',
            'C'     => 'comic_score',
            default => 'cautious_score',
        };
    }

    private function formatDailyResponse(\Illuminate\Support\Collection $rows, string $today): array
    {
        return [
            'quests'     => $rows->map(fn($q) => [
                'user_daily_id' => $q->user_daily_id,
                'quest_id'      => $q->id,
                'title'         => $q->title,
                'description'   => $q->description ?? '',
                'type'          => $q->type,
                'reward_xp'     => $q->reward_xp,
                'reward_gold'   => $q->reward_gold,
                'status'        => $q->daily_status,
            ])->values()->all(),
            'date'       => $today,
            'refresh_at' => today()->addDay()->startOfDay()->toISOString(),
        ];
    }
}
