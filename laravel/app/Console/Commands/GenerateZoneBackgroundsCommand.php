<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateZoneBackgroundsCommand extends Command
{
    protected $signature = 'images:zones
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=5 : Délai en secondes entre chaque génération}
                            {--list : Afficher l\'état sans générer}';

    protected $description = 'Génère un background illustré par zone via Gemini.';

    public function handle(GeminiService $gemini): int
    {
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));
        $listOnly = (bool) $this->option('list');

        $query = DB::table('zones')->select('id', 'slug', 'name', 'dominant_element', 'description', 'background_image_path');

        if ($listOnly) {
            $zones = $query->get();
            $rows = $zones->map(fn($z) => [
                $z->slug,
                $z->dominant_element,
                match (true) {
                    empty($z->background_image_path)                                      => '❌ manquante',
                    str_starts_with($z->background_image_path, 'images/placeholders/')   => '⚠️  placeholder',
                    default                                                               => '✅ ' . $z->background_image_path,
                },
            ])->toArray();
            $this->table(['Zone', 'Élément', 'Background'], $rows);
            return self::SUCCESS;
        }

        if (!$gemini->canCall('zone_bg')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé).');
            return self::FAILURE;
        }

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('background_image_path')
                  ->orWhere('background_image_path', 'like', 'images/placeholders/%');
            });
        }

        $zones = $query->get();

        if ($zones->isEmpty()) {
            $this->info('Toutes les zones ont un background. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $budget = $gemini->budgetStatus();
        $this->info("Zones à traiter : {$zones->count()} | Budget : {$budget['used']}/{$budget['limit']}");

        $done = $failed = 0;
        $bar  = $this->output->createProgressBar($zones->count());
        $bar->start();

        foreach ($zones as $i => $zone) {
            if (!$gemini->canCall('zone_bg')) {
                $bar->finish();
                $this->newLine();
                $this->warn('Budget dépassé, arrêt anticipé.');
                break;
            }

            $path = $gemini->generateZoneBackground($zone->id, $zone->slug, $zone->dominant_element, $zone->description ?? '');

            DB::table('zones')->where('id', $zone->id)->update(['background_image_path' => $path]);

            str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

            $bar->advance();
            if ($delay > 0 && $i < $zones->count() - 1) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();

        $budget = $gemini->budgetStatus();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s). Budget : {$budget['used']}/{$budget['limit']}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
