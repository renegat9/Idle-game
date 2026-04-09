<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateHeroImagesCommand extends Command
{
    protected $signature = 'images:heroes
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=4 : Délai en secondes entre chaque génération}
                            {--list : Afficher l\'état sans générer}';

    protected $description = 'Génère une image unique par héros via Gemini (portrait avec fond transparent).';

    public function handle(GeminiService $gemini): int
    {
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));
        $listOnly = (bool) $this->option('list');

        $query = DB::table('heroes')
            ->join('races', 'heroes.race_id', '=', 'races.id')
            ->join('classes', 'heroes.class_id', '=', 'classes.id')
            ->leftJoin('traits', 'heroes.trait_id', '=', 'traits.id')
            ->select('heroes.id', 'heroes.name', 'heroes.image_path', 'races.name as race_name', 'classes.slug as class_slug', 'traits.slug as trait_slug');

        if ($listOnly) {
            $heroes = $query->get();
            $rows = $heroes->map(fn($h) => [
                $h->id,
                $h->name,
                $h->class_slug,
                match (true) {
                    empty($h->image_path)                                        => '❌ manquante',
                    str_starts_with($h->image_path, 'images/placeholders/')      => '⚠️  placeholder',
                    default                                                       => '✅ ' . $h->image_path,
                },
            ])->toArray();
            $this->table(['ID', 'Héros', 'Classe', 'Image'], $rows);
            return self::SUCCESS;
        }

        if (!$gemini->canCall('hero_image')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé).');
            return self::FAILURE;
        }

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('heroes.image_path')
                  ->orWhere('heroes.image_path', 'like', 'images/placeholders/%');
            });
        }

        $heroes = $query->get();

        if ($heroes->isEmpty()) {
            $this->info('Tous les héros ont une image. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $budget = $gemini->budgetStatus();
        $this->info("Héros à traiter : {$heroes->count()} | Budget : {$budget['used']}/{$budget['limit']}");

        $done = $failed = 0;
        $bar  = $this->output->createProgressBar($heroes->count());
        $bar->start();

        foreach ($heroes as $i => $hero) {
            if (!$gemini->canCall('hero_image')) {
                $bar->finish();
                $this->newLine();
                $this->warn('Budget dépassé, arrêt anticipé.');
                break;
            }

            $path = $gemini->generateHeroImage($hero->id, $hero->race_name, $hero->class_slug, $hero->trait_slug);

            DB::table('heroes')->where('id', $hero->id)->update(['image_path' => $path]);

            str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

            $bar->advance();
            if ($delay > 0 && $i < $heroes->count() - 1) {
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
