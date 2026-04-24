<?php

namespace App\Jobs;

use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateBossImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly int    $bossId,
        private readonly string $bossName,
        private readonly string $bossSlug,
    ) {}

    public function handle(GeminiService $gemini): void
    {
        $row = DB::table('world_bosses')->where('id', $this->bossId)->first(['id', 'image_path']);
        if (!$row || !empty($row->image_path)) {
            return;
        }

        $imagePath = $gemini->generateBossImage($this->bossId, $this->bossName, $this->bossSlug);

        DB::table('world_bosses')
            ->where('id', $this->bossId)
            ->update(['image_path' => $imagePath]);

        Log::info("GenerateBossImage: boss#{$this->bossId} → {$imagePath}");
    }
}
