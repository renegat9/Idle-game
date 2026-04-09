<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Manages seasonal events and their gameplay modifiers.
 *
 * Events are date-range based (month+day only, year-agnostic for recurring events).
 * Modifiers stack additively (no floats — all integer percentages).
 */
class SeasonalEventService
{
    /**
     * Return all currently active seasonal events.
     * An event is active if today falls within its [start_month/day, end_month/day] range.
     */
    public function getActiveEvents(?Carbon $date = null): array
    {
        $date  = $date ?? now();
        $month = $date->month;
        $day   = $date->day;

        $events = DB::table('seasonal_events')
            ->where('is_active', true)
            ->get();

        $active = [];
        foreach ($events as $event) {
            if ($this->isDateInRange($month, $day, $event)) {
                $active[] = $event;
            }
        }

        return $active;
    }

    /**
     * Return the aggregated modifiers from all currently active events.
     * @return array{xp_bonus_pct: int, gold_bonus_pct: int, loot_bonus_pct: int, rare_loot_bonus_pct: int}
     */
    public function getActiveModifiers(?Carbon $date = null): array
    {
        $events = $this->getActiveEvents($date);

        $xp       = 0;
        $gold     = 0;
        $loot     = 0;
        $rareLoot = 0;

        foreach ($events as $event) {
            $xp       += (int) $event->xp_bonus_pct;
            $gold     += (int) $event->gold_bonus_pct;
            $loot     += (int) $event->loot_bonus_pct;
            $rareLoot += (int) $event->rare_loot_bonus_pct;
        }

        return [
            'xp_bonus_pct'        => $xp,
            'gold_bonus_pct'      => $gold,
            'loot_bonus_pct'      => $loot,
            'rare_loot_bonus_pct' => $rareLoot,
        ];
    }

    /**
     * Apply seasonal XP modifier to a base XP value.
     * Returns integer (no floats).
     */
    public function applyXpBonus(int $baseXp, ?Carbon $date = null): int
    {
        $mods = $this->getActiveModifiers($date);
        return intdiv($baseXp * (100 + $mods['xp_bonus_pct']), 100);
    }

    /**
     * Apply seasonal gold modifier to a base gold value.
     */
    public function applyGoldBonus(int $baseGold, ?Carbon $date = null): int
    {
        $mods = $this->getActiveModifiers($date);
        return intdiv($baseGold * (100 + $mods['gold_bonus_pct']), 100);
    }

    /**
     * Return the effective loot drop chance after applying seasonal modifier.
     * Caps at 100.
     */
    public function applyLootBonus(int $baseChance, ?Carbon $date = null): int
    {
        $mods = $this->getActiveModifiers($date);
        return min(100, intdiv($baseChance * (100 + $mods['loot_bonus_pct']), 100));
    }

    /**
     * Returns true if a seasonal event granting the given quest type is currently active.
     */
    public function isQuestTypeUnlocked(string $questType, ?Carbon $date = null): bool
    {
        $events = $this->getActiveEvents($date);
        foreach ($events as $event) {
            if ($event->quest_type_unlock === $questType) {
                return true;
            }
        }
        return false;
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function isDateInRange(int $month, int $day, object $event): bool
    {
        $startMonth = (int) $event->start_month;
        $startDay   = (int) $event->start_day;
        $endMonth   = (int) $event->end_month;
        $endDay     = (int) $event->end_day;

        // Encode month/day as a comparable integer (MMDD)
        $current = $month * 100 + $day;
        $start   = $startMonth * 100 + $startDay;
        $end     = $endMonth * 100 + $endDay;

        if ($start <= $end) {
            // Normal range (e.g. Apr 1 – Apr 7)
            return $current >= $start && $current <= $end;
        } else {
            // Wraps around year end (e.g. Dec 20 – Jan 5)
            return $current >= $start || $current <= $end;
        }
    }
}
