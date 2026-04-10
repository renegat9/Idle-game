<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneratePageBackgroundsCommand extends Command
{
    protected $signature = 'images:pages
                            {--page= : Générer uniquement cette page (slug)}
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=5 : Délai en secondes entre chaque génération}
                            {--list : Afficher l\'état sans générer}';

    protected $description = 'Génère une image de fond illustrée par page de l\'interface via Gemini.';

    /**
     * Pages with their display names.
     * Keys must match route slugs used in the frontend.
     */
    private const PAGES = [
        'dashboard'   => 'Tableau de Bord',
        'team'        => 'Équipe',
        'map'         => 'Carte du Monde',
        'inventory'   => 'Inventaire',
        'forge'       => 'Forge de Gérard',
        'quests'      => 'Quêtes',
        'dungeon'     => 'Le Donjon',
        'tavern'      => 'La Taverne',
        'shop'        => 'Boutique',
        'consumables' => 'Consommables',
        'world-boss'  => 'Boss Mondial',
        'talents'     => 'Arbres de Talents',
        'profile'     => 'Profil',
    ];

    /** Path relative to storage/app/public/ where page backgrounds are stored */
    private const STORAGE_DIR = 'pages';

    public function handle(GeminiService $gemini): int
    {
        $pageFilter = $this->option('page');
        $force      = (bool) $this->option('force');
        $delay      = max(0, (int) $this->option('delay'));
        $listOnly   = (bool) $this->option('list');

        if ($pageFilter && !array_key_exists($pageFilter, self::PAGES)) {
            $this->error("Page inconnue : {$pageFilter}. Pages valides : " . implode(', ', array_keys(self::PAGES)));
            return self::FAILURE;
        }

        $pages = $pageFilter
            ? [$pageFilter => self::PAGES[$pageFilter]]
            : self::PAGES;

        $dir = storage_path('app/public/' . self::STORAGE_DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Build status for each page
        $statuses = [];
        foreach ($pages as $slug => $label) {
            $existing = $this->findExistingFile($dir, $slug);
            $statuses[$slug] = [
                'label'    => $label,
                'existing' => $existing,
                'needs'    => $force || $existing === null,
            ];
        }

        if ($listOnly) {
            $rows = array_map(fn($slug, $info) => [
                $slug,
                $info['label'],
                $info['existing'] !== null
                    ? '✅ storage/' . self::STORAGE_DIR . '/' . basename($info['existing'])
                    : '❌ manquante',
            ], array_keys($statuses), $statuses);

            $this->table(['Slug', 'Page', 'Image'], $rows);
            $missing = count(array_filter($statuses, fn($s) => $s['existing'] === null));
            $this->info("{$missing} image(s) manquante(s) sur " . count($statuses) . " pages.");
            return self::SUCCESS;
        }

        if (!$gemini->canCall('page_bg')) {
            $this->error('Génération désactivée (AI_ENABLED=0 ou budget dépassé ou clé API manquante).');
            return self::FAILURE;
        }

        $toGenerate = array_filter($statuses, fn($s) => $s['needs']);

        if (empty($toGenerate)) {
            $this->info('Toutes les pages ont déjà une image. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $budget = $gemini->budgetStatus();
        $this->info(sprintf(
            '%d page(s) à générer | Budget IA : %d/%d | Délai : %ds entre chaque',
            count($toGenerate),
            $budget['used'],
            $budget['limit'],
            $delay,
        ));

        $done = $failed = 0;
        $i    = 0;

        foreach ($toGenerate as $slug => $info) {
            if (!$gemini->canCall('page_bg')) {
                $this->warn("Budget IA dépassé après {$done} générée(s). Relancez demain.");
                break;
            }

            $this->line("  [{$slug}] {$info['label']}…");

            // Delete old file if force-regenerating
            if ($force && $info['existing'] !== null) {
                @unlink($dir . '/' . basename($info['existing']));
            }

            $path = $gemini->generatePageBackground($slug);

            if (!str_starts_with($path, 'images/placeholders/')) {
                $this->line("    ✅ {$path}");
                $done++;
            } else {
                $this->warn("    ❌ Échec pour {$slug} (placeholder retourné)");
                $failed++;
            }

            $i++;
            if ($delay > 0 && $i < count($toGenerate)) {
                sleep($delay);
            }
        }

        $budget = $gemini->budgetStatus();
        $this->newLine();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s). Budget : {$budget['used']}/{$budget['limit']}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Find an existing page background file (any extension) for the given slug.
     * Returns the filename (not full path) or null if not found.
     */
    private function findExistingFile(string $dir, string $slug): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            // Match both "page_{slug}.{ext}" and "page_{slug}_{timestamp}.{ext}"
            $pattern = "{$dir}/page_{$slug}*.{$ext}";
            $files   = glob($pattern);
            if (!empty($files)) {
                return basename($files[0]);
            }
        }
        return null;
    }
}
