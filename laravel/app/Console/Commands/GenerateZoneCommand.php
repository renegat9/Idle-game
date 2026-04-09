<?php

namespace App\Console\Commands;

use App\Services\ZoneGeneratorService;
use Illuminate\Console\Command;

class GenerateZoneCommand extends Command
{
    protected $signature   = 'zones:generate {--count=1 : Number of zones to generate}';
    protected $description = 'Génère une ou plusieurs nouvelles zones procédurales via Gemini (zone 9+).';

    public function handle(ZoneGeneratorService $generator): int
    {
        $count     = max(1, (int) $this->option('count'));
        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            $this->info("Génération de la zone " . ($i + 1) . "/{$count}...");
            $zoneId = $generator->generate();

            if ($zoneId !== null) {
                $this->info("  Zone créée (id={$zoneId}).");
                $generated++;
            } else {
                $this->warn("  Échec de génération de la zone " . ($i + 1) . ".");
            }
        }

        $this->info("Terminé : {$generated}/{$count} zone(s) générée(s).");
        return $generated > 0 ? self::SUCCESS : self::FAILURE;
    }
}
