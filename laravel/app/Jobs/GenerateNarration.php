<?php

namespace App\Jobs;

use App\Models\NarratorCache;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Asynchronously generate AI narration for a game event and cache it.
 *
 * Usage:
 *   GenerateNarration::dispatch($eventType, $context, $contextHash);
 *
 * The hash must be pre-computed by NarratorService to avoid duplicate generation.
 */
class GenerateNarration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(
        private readonly string $eventType,
        private readonly array  $context,
        private readonly string $contextHash,
    ) {}

    public function handle(GeminiService $gemini): void
    {
        // Skip if already cached (race condition guard)
        if (NarratorCache::where('context_hash', $this->contextHash)->exists()) {
            return;
        }

        if (!$gemini->canCall('narration')) {
            Log::debug("GenerateNarration: AI disabled or budget exceeded for {$this->eventType}");
            return;
        }

        try {
            $text = $gemini->generateNarration($this->eventType, $this->context);

            NarratorCache::updateOrCreate(
                ['context_hash' => $this->contextHash],
                [
                    'event_type'      => $this->eventType,
                    'text'            => $text,
                    'is_ai_generated' => true,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("GenerateNarration failed for {$this->eventType}", ['error' => $e->getMessage()]);
        }
    }
}
