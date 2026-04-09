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
                            {--list : Afficher l\'état sans générer}
                            {--tavern : Traiter aussi les recrues de la taverne (non-recrutées)}';

    protected $description = 'Génère une image unique par héros via Gemini (portrait avec fond transparent).';

    public function handle(GeminiService $gemini): int
    {
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));
        $listOnly = (bool) $this->option('list');
        $tavern   = (bool) $this->option('tavern');

        $rows = array_merge(
            $this->fetchRows('heroes'),
            $tavern ? $this->fetchRows('tavern_recruits') : [],
        );

        if ($listOnly) {
            $tableRows = array_map(fn($r) => [
                $r['table'] === 'tavern_recruits' ? "T#{$r['id']}" : $r['id'],
                $r['name'],
                $r['class_slug'],
                match (true) {
                    empty($r['image_path'])                                        => '❌ manquante',
                    str_starts_with($r['image_path'], 'images/placeholders/')     => '⚠️  placeholder',
                    default                                                        => '✅ ' . $r['image_path'],
                },
            ], $rows);
            $this->table(['ID', 'Nom', 'Classe', 'Image'], $tableRows);
            return self::SUCCESS;
        }

        if (!$gemini->canCall('hero_image')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé).');
            return self::FAILURE;
        }

        if (!$force) {
            $rows = array_filter($rows, fn($r) =>
                empty($r['image_path']) || str_starts_with($r['image_path'], 'images/placeholders/')
            );
        }

        $rows = array_values($rows);

        if (empty($rows)) {
            $this->info('Toutes les images existent. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $budget = $gemini->budgetStatus();
        $this->info(sprintf('%d à traiter (heroes + %s recrues taverne) | Budget : %d/%d',
            count($rows),
            $tavern ? 'avec' : 'sans',
            $budget['used'], $budget['limit'],
        ));

        $done = $failed = 0;
        $bar  = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $i => $row) {
            if (!$gemini->canCall('hero_image')) {
                $bar->finish();
                $this->newLine();
                $this->warn('Budget dépassé, arrêt anticipé.');
                break;
            }

            $path = $gemini->generateHeroImage($row['id'], $row['race_name'], $row['class_slug'], $row['trait_slug']);

            DB::table($row['table'])->where('id', $row['id'])->update(['image_path' => $path]);

            str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

            $bar->advance();
            if ($delay > 0 && $i < count($rows) - 1) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();

        $budget = $gemini->budgetStatus();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s). Budget : {$budget['used']}/{$budget['limit']}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function fetchRows(string $table): array
    {
        $query = DB::table($table)
            ->join('races', "{$table}.race_id", '=', 'races.id')
            ->join('classes', "{$table}.class_id", '=', 'classes.id')
            ->leftJoin('traits', "{$table}.trait_id", '=', 'traits.id')
            ->select(
                "{$table}.id",
                "{$table}.name",
                "{$table}.image_path",
                'races.name as race_name',
                'classes.slug as class_slug',
                'traits.slug as trait_slug',
            );

        if ($table === 'tavern_recruits') {
            $query->where("{$table}.is_hired", false)
                  ->where("{$table}.expires_at", '>', now());
        }

        return $query->get()->map(fn($r) => [
            'table'      => $table,
            'id'         => $r->id,
            'name'       => $r->name,
            'image_path' => $r->image_path,
            'race_name'  => $r->race_name,
            'class_slug' => $r->class_slug,
            'trait_slug' => $r->trait_slug,
        ])->toArray();
    }
}
