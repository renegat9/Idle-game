<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan music:seed
 *
 * Inserts fallback entries in tavern_music for all 6 styles.
 * Run once after migrations. Each style gets one row pointing to
 * public/music/fallback/<style>.ogg — place the actual OGG files there.
 */
class SeedMusicCommand extends Command
{
    protected $signature   = 'music:seed {--force : Overwrite existing entries}';
    protected $description = 'Seed tavern_music table with fallback track entries';

    private const TRACKS = [
        'taverne'         => 'music/fallback/taverne.ogg',
        'victoire_epique' => 'music/fallback/victoire_epique.ogg',
        'defaite'         => 'music/fallback/defaite.ogg',
        'exploration'     => 'music/fallback/exploration.ogg',
        'boss'            => 'music/fallback/boss.ogg',
        'repos'           => 'music/fallback/repos.ogg',
    ];

    public function handle(): int
    {
        $force = $this->option('force');

        foreach (self::TRACKS as $style => $path) {
            $exists = DB::table('tavern_music')->where('style', $style)->exists();

            if ($exists && !$force) {
                $this->line("  skip  {$style} (already seeded — use --force to overwrite)");
                continue;
            }

            if ($exists && $force) {
                DB::table('tavern_music')->where('style', $style)->delete();
            }

            DB::table('tavern_music')->insert([
                'style'       => $style,
                'prompt_used' => "fallback:{$style}",
                'file_path'   => $path,
                'play_count'  => 0,
                'created_at'  => now(),
            ]);

            $fullPath = public_path($path);
            $fileOk   = file_exists($fullPath) ? '✓ fichier présent' : '⚠ fichier MANQUANT → ' . $fullPath;
            $this->line("  <info>ok</info>    {$style} → {$path}  [{$fileOk}]");
        }

        $this->newLine();
        $this->info('Pour que la musique fonctionne, placez les fichiers OGG dans :');
        $this->line('  ' . public_path('music/fallback/'));

        return 0;
    }
}
