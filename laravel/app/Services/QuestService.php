<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestStep;
use App\Models\UserQuest;
use App\Models\User;
use App\Models\Hero;
use App\Models\HeroBuff;
use Illuminate\Support\Facades\DB;

class QuestService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $loot,
        private readonly NarratorService $narrator,
    ) {}

    /**
     * Return quests available to the user for their current zone.
     */
    public function availableQuests(User $user): array
    {
        $zoneId = $user->current_zone_id;
        if (!$zoneId) {
            return [];
        }

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
                    continue; // hide non-repeatable completed quests
                }
            }

            $result[] = [
                'id'                  => $quest->id,
                'title'               => $quest->title,
                'description'         => $quest->description,
                'type'                => $quest->type,
                'steps_count'         => $quest->steps_count,
                'order_index'         => $quest->order_index,
                'reward_xp'           => $quest->reward_xp,
                'reward_gold'         => $quest->reward_gold,
                'reward_loot_rarity'  => $quest->reward_loot_rarity,
                'status'              => $status,
                'current_step'        => $userQuest?->current_step ?? 1,
                'user_quest_id'       => $userQuest?->id,
            ];
        }

        return $result;
    }

    /**
     * Start a quest (or resume in_progress).
     */
    public function startQuest(User $user, int $questId): array
    {
        $quest = Quest::with('steps')->findOrFail($questId);

        $existing = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $questId)
            ->first();

        if ($existing?->status === 'in_progress') {
            // Resume — return current step
            return $this->buildStepResponse($quest, $existing);
        }

        if ($existing?->status === 'completed' && !$quest->is_repeatable) {
            return ['error' => 'Cette quête est déjà terminée.'];
        }

        $userQuest = DB::transaction(function () use ($user, $quest, $existing) {
            if ($existing) {
                $existing->update([
                    'status'       => 'in_progress',
                    'current_step' => 1,
                    'step_results' => null,
                    'heroic_score' => 0, 'cunning_score' => 0,
                    'comic_score'  => 0, 'cautious_score' => 0,
                    'started_at'   => now(),
                    'completed_at' => null,
                ]);
                return $existing->fresh();
            }
            return UserQuest::create([
                'user_id'    => $user->id,
                'quest_id'   => $quest->id,
                'status'     => 'in_progress',
                'current_step' => 1,
                'started_at' => now(),
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

        $quest = $userQuest->quest;
        $step  = $quest->steps->firstWhere('step_index', $userQuest->current_step);

        if (!$step) {
            return ['error' => 'Étape introuvable.'];
        }

        $choices = $step->content['choices'] ?? [];
        $choice  = collect($choices)->firstWhere('id', $choiceId);
        if (!$choice) {
            return ['error' => 'Choix invalide.'];
        }

        // Resolve test
        $success = true;
        $rollResult = null;
        if (!empty($choice['test']) && $choice['test']['type'] !== 'combat') {
            [$success, $rollResult] = $this->resolveStatTest($user, $choice['test'], $heroId);
        }

        $branch = $success ? ($choice['success'] ?? null) : ($choice['failure'] ?? null);
        if (!$branch) {
            $branch = $choice['success']; // fallback
            $success = true;
        }

        // Apply effects
        $appliedEffects = [];
        foreach ($branch['effects'] ?? [] as $effect) {
            $applied = $this->applyEffect($user, $effect);
            if ($applied) $appliedEffects[] = $applied;
        }

        // Update voice scores
        $voiceField = $this->detectVoice($choiceId, $choice['text'] ?? '');

        // Build step result record
        $stepResults = $userQuest->step_results ?? [];
        $stepResults[] = [
            'step'     => $userQuest->current_step,
            'choice'   => $choiceId,
            'success'  => $success,
            'roll'     => $rollResult,
            'effects'  => $appliedEffects,
        ];

        $nextStep = $branch['next_step'] ?? null;
        $isFinal  = ($nextStep === null) || ($step->content['is_final'] ?? false);

        DB::transaction(function () use ($userQuest, $nextStep, $stepResults, $voiceField, $isFinal) {
            $updates = [
                'step_results' => $stepResults,
                $voiceField    => $userQuest->$voiceField + 1,
            ];
            if ($isFinal) {
                $updates['status']       = 'completed';
                $updates['completed_at'] = now();
            } else {
                $updates['current_step'] = $nextStep;
            }
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
            $response['rewards'] = $this->grantRewards($user, $quest, $userQuest->fresh());
            $response['narrator_comment'] = $this->narrator->getComment(
                'quest_completed',
                ['quest_title' => $quest->title]
            );
        } else {
            // Return next step data
            $nextStepModel = $quest->steps->firstWhere('step_index', $nextStep);
            if ($nextStepModel) {
                $response['next_step'] = $this->sanitizeStep($nextStepModel);
            }
        }

        return $response;
    }

    // ─── Internals ───────────────────────────────────────────────────────────

    private function resolveStatTest(User $user, array $test, ?int $heroId): array
    {
        $stat       = $test['stat'] ?? 'atq';
        $difficulty = $test['difficulty'] ?? 30;

        // Pick hero: requested or best for stat
        $heroes = $user->activeHeroes()->get();
        $hero = $heroId
            ? $heroes->firstWhere('id', $heroId)
            : $heroes->sortByDesc(fn($h) => $h->computedStats()[$stat] ?? 0)->first();

        if (!$hero) {
            return [false, null];
        }

        $stats    = $hero->computedStats();
        $statVal  = $stats[$stat] ?? 0;
        $roll     = rand(1, 20);
        $total    = $statVal + $roll;

        // Trait bonus/malus
        $trait = $hero->trait_;
        if ($trait && isset($test['trait_bonus'][$trait->name])) {
            $total += $test['trait_bonus'][$trait->name];
        }
        if ($trait && isset($test['trait_malus'][$trait->name])) {
            $total += $test['trait_malus'][$trait->name]; // malus is negative
        }

        return [$total >= $difficulty, ['roll' => $roll, 'stat' => $statVal, 'total' => $total, 'difficulty' => $difficulty]];
    }

    private function applyEffect(User $user, array $effect): ?array
    {
        $type = $effect['type'] ?? '';

        switch ($type) {
            case 'buff':
                $this->applyBuff($user, $effect['id'], $effect['target'] ?? 'leader');
                return ['type' => 'buff', 'id' => $effect['id']];

            case 'debuff':
                $this->applyDebuff($user, $effect['id'], $effect['target'] ?? 'leader');
                return ['type' => 'debuff', 'id' => $effect['id']];

            case 'gold':
                $amount = $effect['amount'] ?? 0;
                $user->increment('gold', $amount);
                $user->gold = max(0, $user->fresh()->gold);
                $user->save();
                return ['type' => 'gold', 'amount' => $amount];

            case 'reputation':
                $zone = $effect['zone'] ?? null;
                $rep  = $effect['amount'] ?? 0;
                if ($zone) {
                    $zoneId = \App\Models\Zone::where('slug', $zone)->value('id');
                    if ($zoneId) {
                        DB::table('zone_reputation')->updateOrInsert(
                            ['user_id' => $user->id, 'zone_id' => $zoneId],
                            ['reputation' => DB::raw("reputation + {$rep}"), 'updated_at' => now(), 'created_at' => now()]
                        );
                    }
                }
                return ['type' => 'reputation', 'amount' => $rep];

            case 'loot':
                $item = $this->loot->rollLoot($user, 1, $effect['rarity_min'] ?? 'commun');
                return $item ? ['type' => 'loot', 'item_name' => $item->name, 'rarity' => $item->rarity] : null;
        }

        return null;
    }

    private function applyBuff(User $user, string $buffId, string $target): void
    {
        $buffData   = $this->buffDefinition($buffId);
        $heroes     = $this->selectHeroes($user, $target);

        foreach ($heroes as $hero) {
            HeroBuff::create([
                'hero_id'         => $hero->id,
                'source'          => 'quest_buff_' . $buffId,
                'stat_affected'   => $buffData['stat'] ?? 'all',
                'modifier_percent'=> $buffData['percent'] ?? 0,
                'remaining_combats' => $buffData['duration'] ?? $this->settings->get('QUEST_BUFF_DURATION_MEDIUM'),
                'is_debuff'       => false,
            ]);
        }
    }

    private function applyDebuff(User $user, string $debuffId, string $target): void
    {
        $debuffData = $this->debuffDefinition($debuffId);
        $heroes     = $this->selectHeroes($user, $target);
        $maxDur     = $this->settings->get('QUEST_DEBUFF_DURATION_MAX', 20);

        foreach ($heroes as $hero) {
            HeroBuff::create([
                'hero_id'           => $hero->id,
                'source'            => 'quest_debuff_' . $debuffId,
                'stat_affected'     => $debuffData['stat'] ?? 'all',
                'modifier_percent'  => -abs($debuffData['percent'] ?? 10),
                'remaining_combats' => min($debuffData['duration'] ?? 10, $maxDur),
                'is_debuff'         => true,
            ]);
        }
    }

    private function selectHeroes(User $user, string $target): \Illuminate\Support\Collection
    {
        $heroes = $user->activeHeroes()->get();
        return match ($target) {
            'party'    => $heroes,
            'leader'   => $heroes->take(1),
            'attacker' => $heroes->sortByDesc(fn($h) => $h->computedStats()['atq'] ?? 0)->take(1),
            default    => $heroes->take(1),
        };
    }

    private function grantRewards(User $user, Quest $quest, UserQuest $userQuest): array
    {
        $xp   = $quest->reward_xp;
        $gold = $quest->reward_gold;

        // Voice bonus
        $scores = [
            'heroic'   => $userQuest->heroic_score,
            'cunning'  => $userQuest->cunning_score,
            'comic'    => $userQuest->comic_score,
            'cautious' => $userQuest->cautious_score,
        ];
        $dominantVoice = array_search(max($scores), $scores);

        $xpBonus   = 0;
        $goldBonus = 0;
        switch ($dominantVoice) {
            case 'heroic':
                $xpBonus = intdiv($xp * $this->settings->get('QUEST_VOICE_HEROIC_XP_BONUS', 25), 100);
                break;
            case 'cunning':
                $goldBonus = intdiv($gold * $this->settings->get('QUEST_VOICE_CUNNING_GOLD_BONUS', 25), 100);
                break;
            case 'comic':
                $bonus = $this->settings->get('QUEST_VOICE_COMIC_ALL_BONUS', 10);
                $xpBonus   = intdiv($xp * $bonus, 100);
                $goldBonus = intdiv($gold * $bonus, 100);
                break;
        }

        $totalXp   = $xp + $xpBonus;
        $totalGold = $gold + $goldBonus;

        DB::transaction(function () use ($user, $totalXp, $totalGold) {
            $user->increment('gold', $totalGold);
            // Distribute XP to active heroes
            foreach ($user->activeHeroes()->get() as $hero) {
                $heroXp = intdiv($totalXp, max(1, $user->activeHeroes()->count()));
                $this->grantHeroXp($hero, $heroXp);
            }
        });

        $lootItem = null;
        if ($quest->reward_loot_rarity) {
            $lootItem = $this->loot->rollLoot($user, 1, $quest->reward_loot_rarity);
        }

        return [
            'xp'           => $totalXp,
            'xp_bonus'     => $xpBonus,
            'gold'         => $totalGold,
            'gold_bonus'   => $goldBonus,
            'dominant_voice' => $dominantVoice,
            'loot'         => $lootItem ? ['name' => $lootItem->name, 'rarity' => $lootItem->rarity] : null,
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
        // Remove hidden test difficulty details from client
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
        // Default: map choice IDs loosely
        return match ($choiceId) {
            'A' => 'heroic_score',
            'B' => 'cautious_score',
            'C' => 'comic_score',
            default => 'cautious_score',
        };
    }

    private function buffDefinition(string $id): array
    {
        $buffs = [
            'B01' => ['stat' => 'all', 'percent' => 10, 'duration' => 30],
            'B02' => ['stat' => 'atq', 'percent' => 15, 'duration' => 10],
            'B03' => ['stat' => 'int', 'percent' => 15, 'duration' => 30],
            'B04' => ['stat' => 'vit', 'percent' => 20, 'duration' => 10],
            'B05' => ['stat' => 'def', 'percent' => 20, 'duration' => 10],
            'B06' => ['stat' => 'cha', 'percent' => 10, 'duration' => 30],
            'B07' => ['stat' => 'all', 'percent' => 10, 'duration' => 100],
            'B08' => ['stat' => 'hp', 'percent' => 15, 'duration' => 30],
            'B09' => ['stat' => 'def', 'percent' => 15, 'duration' => 10],
            'B10' => ['stat' => 'atq', 'percent' => 20, 'duration' => 30],
            'B11' => ['stat' => 'vit', 'percent' => 10, 'duration' => 10],
            'B12' => ['stat' => 'all', 'percent' => 5, 'duration' => 30],
            'B13' => ['stat' => 'vit', 'percent' => 15, 'duration' => 10],
            'B14' => ['stat' => 'int', 'percent' => 15, 'duration' => 30],
            'B15' => ['stat' => 'cha', 'percent' => 20, 'duration' => 100],
        ];
        return $buffs[$id] ?? ['stat' => 'all', 'percent' => 10, 'duration' => 10];
    }

    private function debuffDefinition(string $id): array
    {
        $debuffs = [
            'D01' => ['stat' => 'all', 'percent' => 10, 'duration' => 20],
            'D02' => ['stat' => 'vit', 'percent' => 15, 'duration' => 10],
            'D03' => ['stat' => 'atq', 'percent' => 10, 'duration' => 15],
            'D04' => ['stat' => 'hp',  'percent' => 5,  'duration' => 10],
            'D05' => ['stat' => 'cha', 'percent' => 15, 'duration' => 20],
            'D06' => ['stat' => 'all', 'percent' => 10, 'duration' => 10],
            'D07' => ['stat' => 'int', 'percent' => 10, 'duration' => 15],
            'D08' => ['stat' => 'vit', 'percent' => 15, 'duration' => 10],
            'D09' => ['stat' => 'cha', 'percent' => 10, 'duration' => 15],
            'D10' => ['stat' => 'all', 'percent' => 5,  'duration' => 10],
        ];
        return $debuffs[$id] ?? ['stat' => 'all', 'percent' => 5, 'duration' => 10];
    }
}
