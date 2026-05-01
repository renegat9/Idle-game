<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Purge old log entries to keep the database from growing unbounded.
 *
 * Schedule: daily via `php artisan logs:cleanup`
 *
 * Retention rules (from DATABASE.md):
 *  - idle_event_log (read): 7 days
 *  - combat_log:            30 days
 *  - economy_log:           90 days
 *  - ai_generation_log:     30 days
 *  - narrator_cache (unused): 30 days
 */
class CleanupLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        $deleted = [];

        // idle_event_log: delete read entries older than 7 days
        $deleted['idle_event_log'] = DB::table('idle_event_log')
            ->where('is_read', true)
            ->where('occurred_at', '<', now()->subDays(7))
            ->delete();

        // combat_log: delete entries older than 30 days
        $deleted['combat_log'] = DB::table('combat_log')
            ->where('occurred_at', '<', now()->subDays(30))
            ->delete();

        // economy_log: delete entries older than 90 days
        $deleted['economy_log'] = DB::table('economy_log')
            ->where('occurred_at', '<', now()->subDays(90))
            ->delete();

        // ai_generation_log: archive entries older than 30 days
        $deleted['ai_generation_log'] = DB::table('ai_generation_log')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        // narrator_cache: delete unused entries older than 30 days
        $deleted['narrator_cache'] = DB::table('narrator_cache')
            ->where('usage_count', 0)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        $total = array_sum($deleted);
        Log::info("CleanupLogs: {$total} rows deleted", $deleted);
    }
}
