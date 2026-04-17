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
 * Generates a hero portrait via Imagen (with green-screen chroma key)
 * and stores the resulting path on either the heroes or tavern_recruits table.
 *
 * Dispatched async by HeroController::store() and TavernController::generateRecruits().
 */
class GenerateHeroImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries   = 2;
    public int $timeout = 90;

    public function __construct(
        private readonly int     $targetId,
        private readonly string  $raceName,
        private readonly string  $classSlug,
        private readonly ?string $traitSlug,
        private readonly string  $targetTable = 'heroes',
    ) {}

    public function handle(GeminiService $gemini): void
    {
        $row = DB::table($this->targetTable)->where('id', $this->targetId)->first(['id', 'image_path']);
        if (!$row) {
            return;
        }

        // Skip if image already generated
        if (!empty($row->image_path)) {
            return;
        }

        $imagePath = $gemini->generateHeroImage(
            $this->targetId,
            $this->raceName,
            $this->classSlug,
            $this->traitSlug,
        );

        DB::table($this->targetTable)
            ->where('id', $this->targetId)
            ->update(['image_path' => $imagePath]);

        // If this was a tavern recruit that was already hired, propagate image to the hero
        if ($this->targetTable === 'tavern_recruits') {
            $recruit = DB::table('tavern_recruits')->where('id', $this->targetId)->first(['hired_hero_id']);
            if ($recruit && $recruit->hired_hero_id) {
                DB::table('heroes')
                    ->where('id', $recruit->hired_hero_id)
                    ->whereNull('image_path')
                    ->update(['image_path' => $imagePath]);
            }
        }

        Log::info("GenerateHeroImage: {$this->targetTable}#{$this->targetId} → {$imagePath}");
    }
}
