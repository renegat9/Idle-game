<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ShopService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Purge expired shop inventory entries.
 * Does NOT pre-generate stock — shop items are generated lazily on first visit.
 * This job only cleans up expired rows to keep the table lean.
 *
 * Schedule: every 6 hours via `php artisan shop:refresh`
 */
class RefreshShop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        $deleted = DB::table('shop_inventories')
            ->where('expires_at', '<', now())
            ->where('is_sold', false)
            ->delete();

        Log::info("RefreshShop: {$deleted} expired items purged.");
    }
}
