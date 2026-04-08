<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;

class GenerateMusicCommand extends Command
{
    protected $signature = 'images:music
                            {--force : Régénérer même si le fichier existe déjà}
                            {--delay=3 : Délai en secondes entre chaque génération}
                            {--style= : Générer un seul style (victoire_epique, defaite, exploration, taverne, boss, repos)}
                            {--list : Lister les styles sans générer}';

    protected $description = 'Génère les pistes musicales via Lyria 3 (Gemini API)';

    private const STYLES = [
        'victoire_epique',
        'defaite',
        'exploration',
        'taverne',
        'boss',
        'repos',
    ];

    public function handle(GeminiService $gemini): int
    {
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
            $this->table(['Style', 'Fichier généré'], array_map(function ($s) {
                $dir = storage_path('app/public/music/generated');
                $exists = glob("{$dir}/music_{$s}_*.mp3");
                return [$s, $exists ? basename(end($exists)) . ' ✓' : '— (manquant)'];
            }, $styles));
            return 0;
        }

        $delay = max(1, (int) $this->option('delay'));
        $force = $this->option('force');

        $this->info("Génération musicale Lyria 3 — " . count($styles) . " style(s), délai {$delay}s");

        $dir = storage_path('app/public/music/generated');

        foreach ($styles as $i => $style) {
            // Vérifier si une piste existe déjà pour ce style
            $existing = glob("{$dir}/music_{$style}_*.mp3") ?: [];

            if (!empty($existing) && !$force) {
                $this->line("  <fg=gray>⏩ {$style} — déjà généré (" . basename(end($existing)) . ")</>");
                continue;
            }

            $this->line("  🎵 Génération : <fg=cyan>{$style}</> ...");

            $result = $gemini->generateTavernMusic($style);

            if (str_starts_with($result['file_path'], 'storage/music/generated/')) {
                $this->line("  <fg=green>✓ {$style}</> → {$result['file_path']}");
            } else {
                $this->line("  <fg=yellow>⚠ {$style}</> → fallback ({$result['file_path']})");
            }

            // Délai entre les appels (sauf pour le dernier)
            if ($i < count($styles) - 1) {
                sleep($delay);
            }
        }

        $this->info('Terminé.');
        return 0;
    }
}
