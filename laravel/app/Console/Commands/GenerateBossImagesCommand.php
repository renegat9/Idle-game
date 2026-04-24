<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateBossImagesCommand extends Command
{
    protected $signature = 'images:bosses
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=4 : Délai en secondes entre chaque génération}
                            {--list : Afficher l\'état sans générer}';

    protected $description = 'Génère les portraits de boss mondiaux via Gemini Imagen.';

    public function handle(GeminiService $gemini): int
    {
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));
        $listOnly = (bool) $this->option('list');

        $bosses = DB::table('world_bosses')->select('id', 'name', 'slug', 'image_path')->get();

        if ($listOnly) {
            $rows = $bosses->map(fn($b) => [
                $b->name,
                $b->slug,
                match (true) {
                    empty($b->image_path)                                    => '❌',
                    str_starts_with($b->image_path, 'images/placeholders/') => '⚠️',
                    default                                                  => '✅',
                },
            ])->toArray();
            $this->table(['Boss', 'Slug', 'Image'], $rows);
            return self::SUCCESS;
        }

        if ($bosses->isEmpty()) {
            $this->warn('Aucun boss mondial en base. Spawner un boss d\'abord (php artisan world-boss:spawn).');
            return self::SUCCESS;
        }

        if (!$gemini->canCall('boss_image')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé).');
            return self::FAILURE;
        }

        $budget = $gemini->budgetStatus();
        $this->info("Boss : {$bosses->count()} | Budget : {$budget['used']}/{$budget['limit']}");

        $done = $failed = $skipped = 0;

        foreach ($bosses as $i => $boss) {
            $needs = $force
                || empty($boss->image_path)
                || str_starts_with($boss->image_path ?? '', 'images/placeholders/');

            if (!$needs) {
                $this->line("  [skip] {$boss->name} (image déjà présente)");
                $skipped++;
                continue;
            }

            if (!$gemini->canCall('boss_image')) {
                $this->warn('Budget dépassé, arrêt anticipé.');
                break;
            }

            $this->line("  [gen]  {$boss->name}...");
            $path = $gemini->generateBossImage($boss->id, $boss->name, $boss->slug);
            DB::table('world_bosses')->where('id', $boss->id)->update(['image_path' => $path]);
            str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

            if ($delay > 0 && $i < $bosses->count() - 1) {
                sleep($delay);
            }
        }

        $budget = $gemini->budgetStatus();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s), {$skipped} ignorée(s). Budget : {$budget['used']}/{$budget['limit']}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
