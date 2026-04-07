<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonsterImagesCommand extends Command
{
    protected $signature = 'images:monsters
                            {--elite : Générer uniquement les images élites (nécessite image de base)}
                            {--all : Générer base ET élite}
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=4 : Délai en secondes entre chaque génération}
                            {--list : Afficher l\'état sans générer}';

    protected $description = 'Génère les images de monstres via Gemini. Les élites utilisent l\'image de base comme référence.';

    public function handle(GeminiService $gemini): int
    {
        $eliteOnly = (bool) $this->option('elite');
        $all       = (bool) $this->option('all');
        $force     = (bool) $this->option('force');
        $delay     = max(0, (int) $this->option('delay'));
        $listOnly  = (bool) $this->option('list');

        $monsters = DB::table('monsters')->select('id', 'name', 'slug', 'element', 'image_path', 'elite_image_path')->get();

        if ($listOnly) {
            $rows = $monsters->map(fn($m) => [
                $m->slug,
                $m->element,
                match (true) {
                    empty($m->image_path)                                    => '❌',
                    str_starts_with($m->image_path, 'images/placeholders/') => '⚠️',
                    default                                                  => '✅',
                },
                match (true) {
                    empty($m->elite_image_path)                                    => '❌',
                    str_starts_with($m->elite_image_path, 'images/placeholders/') => '⚠️',
                    default                                                        => '✅',
                },
            ])->toArray();
            $this->table(['Monstre', 'Élément', 'Base', 'Élite'], $rows);
            return self::SUCCESS;
        }

        if (!$gemini->canCall('monster_image')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé).');
            return self::FAILURE;
        }

        $budget = $gemini->budgetStatus();
        $this->info("Monstres : {$monsters->count()} | Budget : {$budget['used']}/{$budget['limit']}");

        $done = $failed = 0;

        foreach ($monsters as $i => $monster) {
            if (!$gemini->canCall('monster_image')) {
                $this->warn('Budget dépassé, arrêt anticipé.');
                break;
            }

            $needsBase  = $all || (!$eliteOnly && ($force || empty($monster->image_path) || str_starts_with($monster->image_path ?? '', 'images/placeholders/')));
            $needsElite = $all || $eliteOnly || ($force || empty($monster->elite_image_path) || str_starts_with($monster->elite_image_path ?? '', 'images/placeholders/'));

            // ── Image de base ─────────────────────────────────────────────────
            if ($needsBase) {
                $this->line("  [base] {$monster->name}...");
                $path = $gemini->generateMonsterImage($monster->id, $monster->name, $monster->element ?? 'physique');
                DB::table('monsters')->where('id', $monster->id)->update(['image_path' => $path]);
                str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

                // Mettre à jour pour la version élite
                $monster->image_path = $path;

                if ($delay > 0) {
                    sleep($delay);
                }
            }

            // ── Image élite (utilise l'image de base comme référence) ─────────
            if ($needsElite && $gemini->canCall('monster_image')) {
                $this->line("  [elite] {$monster->name}...");
                $path = $gemini->generateEliteMonsterImage(
                    $monster->id,
                    $monster->name,
                    $monster->element ?? 'physique',
                    $monster->image_path ?: null
                );
                DB::table('monsters')->where('id', $monster->id)->update(['elite_image_path' => $path]);
                str_starts_with($path, 'images/placeholders/') ? $failed++ : $done++;

                if ($delay > 0 && $i < $monsters->count() - 1) {
                    sleep($delay);
                }
            }
        }

        $budget = $gemini->budgetStatus();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s). Budget : {$budget['used']}/{$budget['limit']}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
