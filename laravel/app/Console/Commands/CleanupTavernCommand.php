<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupTavernCommand extends Command
{
    protected $signature   = 'tavern:cleanup-expired';
    protected $description = 'Supprime les recrues de taverne expirées et non embauchées.';

    public function handle(): int
    {
        $deleted = DB::table('tavern_recruits')
            ->where('is_hired', false)
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Recrues expirées supprimées : {$deleted}.");
        return self::SUCCESS;
    }
}
