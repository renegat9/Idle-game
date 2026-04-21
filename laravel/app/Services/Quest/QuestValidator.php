<?php

namespace App\Services\Quest;

/**
 * Validates Gemini-generated quest data before injecting it into the game.
 * QUESTS_EFFECTS.md §7.2 — server-side validation pipeline.
 *
 * Rule: if ANY step fails validation, the entire quest is rejected and
 * the caller must fall back to a static pre-written template.
 */
class QuestValidator
{
    private const ALLOWED_BUFFS   = ['B01','B02','B03','B04','B05','B06','B07','B08','B09','B10','B11','B12','B13','B14','B15'];
    private const ALLOWED_DEBUFFS = ['D01','D02','D03','D04','D05','D06','D07','D08','D09','D10'];
    private const ALLOWED_EQ      = ['EQ01','EQ02','EQ03','EQ04','EQ05','EQ06','EQ07','EQ08'];
    private const ALLOWED_WORLD   = ['M01','M02','M03','M04','M05','M06','M07','M08','M09','M10'];
    private const MAX_DEBUFF_DUR  = 20;
    private const STEP_REQUIRED   = ['step_id', 'narration', 'choices'];
    private const DIFFICULTY_MIN  = 20;
    private const DIFFICULTY_MAX  = 100;

    /**
     * Validate + sanitize a full Gemini quest response.
     * Returns cleaned quest data on success, null on failure (→ use fallback).
     */
    public function validateAndSanitize(array $questData): ?array
    {
        $steps = $questData['steps'] ?? [];
        if (empty($steps) || !is_array($steps)) {
            return null;
        }

        $sanitized = [];
        foreach ($steps as $step) {
            if (!$this->validateStep($step)) {
                return null;
            }
            $sanitized[] = $this->sanitizeStep($step);
        }

        return array_merge($questData, ['steps' => $sanitized]);
    }

    /**
     * Validate a single step structure.
     */
    public function validateStep(array $step): bool
    {
        // 1. Required keys present
        foreach (self::STEP_REQUIRED as $key) {
            if (!array_key_exists($key, $step)) {
                return false;
            }
        }

        // 2. Choices is non-empty array
        if (!is_array($step['choices']) || empty($step['choices'])) {
            return false;
        }

        // 3. Validate each choice
        foreach ($step['choices'] as $choice) {
            // stat test must have coherent difficulty
            if (!empty($choice['test']) && ($choice['test']['type'] ?? '') !== 'combat') {
                if (!$this->isValidStatTest($choice['test'])) {
                    return false;
                }
            }

            foreach (['success', 'failure'] as $branch) {
                $b = $choice[$branch] ?? null;
                if (!$b) continue;

                foreach ($b['effects'] ?? [] as $effect) {
                    // 4. Effect must be in allowed pool
                    if (!$this->isAllowedEffect($effect)) {
                        return false;
                    }
                    // 5. Debuff duration must not exceed max
                    if (($effect['type'] ?? '') === 'debuff' && ($effect['duration'] ?? 0) > self::MAX_DEBUFF_DUR) {
                        return false;
                    }
                    // 6. No permanent negative effects
                    if ($this->isPermanentNegative($effect)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check stat test difficulty is in coherent range [20, 100].
     */
    public function isValidStatTest(array $test): bool
    {
        $difficulty = $test['difficulty'] ?? 0;
        return $difficulty >= self::DIFFICULTY_MIN && $difficulty <= self::DIFFICULTY_MAX;
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function isAllowedEffect(array $effect): bool
    {
        $type = $effect['type'] ?? '';
        $id   = $effect['id'] ?? '';

        return match ($type) {
            'buff'         => in_array($id, self::ALLOWED_BUFFS, true),
            'debuff'       => in_array($id, self::ALLOWED_DEBUFFS, true),
            'team_effect'  => in_array($id, self::ALLOWED_EQ, true),
            'world_effect' => in_array($id, self::ALLOWED_WORLD, true),
            'gold'         => is_int($effect['amount'] ?? null),
            'reputation'   => is_int($effect['amount'] ?? null),
            'loot'         => !empty($effect['rarity_min']),
            default        => false,
        };
    }

    private function isPermanentNegative(array $effect): bool
    {
        return ($effect['type'] ?? '') === 'debuff' && !empty($effect['permanent']);
    }

    private function sanitizeStep(array $step): array
    {
        $step['choices'] = array_map(function (array $choice) {
            foreach (['success', 'failure'] as $branch) {
                if (!isset($choice[$branch]['effects'])) continue;

                $choice[$branch]['effects'] = array_values(
                    array_filter($choice[$branch]['effects'], fn($e) => $this->isAllowedEffect($e))
                );
            }
            return $choice;
        }, $step['choices']);

        return $step;
    }
}
