<?php

namespace App\Console\Commands;

use App\Jobs\CleanupLogs;
use Illuminate\Console\Command;

class CleanupLogsCommand extends Command
{
    protected $signature   = 'logs:cleanup';
    protected $description = 'Purge les entrées expirées de combat_log, economy_log, idle_event_log, ai_generation_log et narrator_cache.';

    public function handle(): int
    {
        $this->info('Nettoyage des logs...');
        CleanupLogs::dispatchSync();
        $this->info('Terminé.');
        return self::SUCCESS;
    }
}
