<?php

namespace App\Jobs;

use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Generates a loot item illustration via Imagen and stores the path on the item.
 *
 * Dispatched async by LootService after item creation (only for Rare+ items).
 */
class GenerateLootImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        private readonly int    $itemId,
        private readonly string $slot,
        private readonly string $rarity,
    ) {}

    public function handle(GeminiService $gemini): void
    {
        // Skip if item no longer exists
        $item = DB::table('items')->where('id', $this->itemId)->first(['id', 'image_url']);
        if (!$item) {
            return;
        }

        // Skip if already has an image
        if (!empty($item->image_url)) {
            return;
        }

        $imagePath = $gemini->generateLootImage($this->itemId, $this->slot, $this->rarity);

        DB::table('items')
            ->where('id', $this->itemId)
            ->update([
                'image_url'       => $imagePath,
                'is_ai_generated' => 1,
            ]);

        Log::info("GenerateLootImage: item {$this->itemId} → {$imagePath}");
    }
}
