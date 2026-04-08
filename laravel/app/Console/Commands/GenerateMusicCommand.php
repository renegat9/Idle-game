<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use App\Services\SettingsService;
use Illuminate\Console\Command;

class GenerateMusicCommand extends Command
{
    protected $signature = 'images:music
                            {--force : Régénérer même si le fichier existe déjà}
                            {--delay=3 : Délai en secondes entre chaque génération}
                            {--style= : Générer un seul style (victoire_epique, defaite, exploration, taverne, boss, repos)}
                            {--list : Lister les styles sans générer}';

    protected $description = 'Génère les pistes musicales via Lyria (Vertex AI ou Gemini)';

    private const STYLES = [
        'victoire_epique',
        'defaite',
        'exploration',
        'taverne',
        'boss',
        'repos',
    ];

    public function handle(GeminiService $gemini, SettingsService $settings): int
    {
        $aiEnabled = $settings->get('AI_ENABLED', 0);
        $geminiKey = config('services.gemini.api_key');
        $vertexKey = config('services.vertex_ai.api_key');
        $projectId = config('services.vertex_ai.project_id');
        $location  = config('services.vertex_ai.location', 'us-central1');
        $budget    = $settings->get('AI_DAILY_BUDGET_LIMIT', 1000);

        $this->line('');
        $this->line('<fg=cyan>── Configuration ──────────────────────────────</fg=cyan>');
        $this->line('  AI_ENABLED       : ' . ($aiEnabled ? '<fg=green>OUI</>' : '<fg=red>NON</>'));
        $this->line('  GEMINI_API_KEY   : ' . (!empty($geminiKey) ? '<fg=green>configuré</>' : '<fg=red>manquant</>'));
        $this->line('  VERTEX_AI_KEY    : ' . (!empty($vertexKey) ? '<fg=green>configuré</>' : '<fg=yellow>absent (Lyria 3 sera utilisé)</>'));
        $this->line('  VERTEX_PROJECT   : ' . (!empty($projectId) ? "<fg=green>{$projectId}</>" : '<fg=yellow>absent</>'));
        $this->line('  VERTEX_LOCATION  : ' . $location);
        $this->line('  Budget journalier: ' . $budget);
        $this->line('  Backend musique  : ' . (!empty($vertexKey) ? '<fg=green>Vertex AI Lyria 2 (WAV)</>' : '<fg=cyan>Gemini API Lyria 3 (MP3)</>'));
        $this->line('');

        if (!$aiEnabled) {
            $this->error('AI_ENABLED = 0 dans game_settings. Mets-le à 1 pour générer.');
            return 1;
        }

        if (empty($vertexKey) && empty($geminiKey)) {
            $this->error('Aucune clé API configurée (VERTEX_AI_API_KEY ou GEMINI_API_KEY).');
            return 1;
        }

        $styles = self::STYLES;

        if ($this->option('style')) {
            $style = $this->option('style');
            if (!in_array($style, self::STYLES)) {
                $this->error("Style inconnu : {$style}. Styles disponibles : " . implode(', ', self::STYLES));
                return 1;
            }
            $styles = [$style];
        }

        if ($this->option('list')) {
            $dir = storage_path('app/public/music/generated');
            $this->table(['Style', 'Fichier'], array_map(function ($s) use ($dir) {
                $files = array_merge(
                    glob("{$dir}/music_{$s}_*.wav") ?: [],
                    glob("{$dir}/music_{$s}_*.mp3") ?: []
                );
                return [$s, $files ? basename(end($files)) . ' ✓' : '— (manquant)'];
            }, $styles));
            return 0;
        }

        $delay = max(1, (int) $this->option('delay'));
        $force = $this->option('force');
        $dir   = storage_path('app/public/music/generated');

        $this->line("<fg=cyan>Génération de " . count($styles) . " style(s), délai {$delay}s entre chaque</>");

        foreach ($styles as $i => $style) {
            $existing = array_merge(
                glob("{$dir}/music_{$style}_*.wav") ?: [],
                glob("{$dir}/music_{$style}_*.mp3") ?: []
            );

            if (!empty($existing) && !$force) {
                $this->line("  <fg=gray>⏩ {$style} — déjà généré (" . basename(end($existing)) . ")</>");
                continue;
            }

            $this->line("  🎵 Génération : <fg=cyan>{$style}</> ...");

            try {
                $result = $gemini->generateTavernMusic($style);
            } catch (\Throwable $e) {
                $this->error("  EXCEPTION : " . $e->getMessage());
                $this->error("  " . $e->getFile() . ':' . $e->getLine());
                continue;
            }

            if (str_starts_with($result['file_path'], 'storage/music/generated/')) {
                $this->line("  <fg=green>✓ {$style}</> → {$result['file_path']}");
            } else {
                $this->line("  <fg=yellow>⚠ fallback</> → {$result['file_path']}");
                $this->warn("  Voir laravel.log pour les détails de l'erreur.");
            }

            if ($i < count($styles) - 1) {
                sleep($delay);
            }
        }

        $this->line('');
        $this->info('Terminé.');
        return 0;
    }
}
