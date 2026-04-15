<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupStaleQuestsCommand extends Command
{
    protected $signature   = 'quests:cleanup-stale';
    protected $description = 'Supprime les quêtes en cours depuis plus de 7 jours (bloquées).';

    public function handle(): int
    {
        $deleted = DB::table('user_quests')
            ->whereIn('status', ['in_progress', 'failed'])
            ->where('started_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Quêtes bloquées supprimées : {$deleted}.");
        return self::SUCCESS;
    }
}
