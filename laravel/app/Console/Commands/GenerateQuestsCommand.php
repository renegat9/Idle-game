<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDailyQuests;
use Illuminate\Console\Command;

class GenerateQuestsCommand extends Command
{
    protected $signature   = 'quests:generate';
    protected $description = 'Génère un pool de quêtes quotidiennes pour toutes les zones via Gemini (ou templates statiques si IA indisponible).';

    public function handle(): int
    {
        $this->info('Génération des quêtes quotidiennes...');
        GenerateDailyQuests::dispatchSync();
        $this->info('Terminé.');
        return self::SUCCESS;
    }
}
