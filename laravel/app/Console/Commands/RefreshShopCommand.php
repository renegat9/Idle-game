<?php

namespace App\Console\Commands;

use App\Jobs\RefreshShop;
use Illuminate\Console\Command;

class RefreshShopCommand extends Command
{
    protected $signature   = 'shop:refresh';
    protected $description = 'Purge les items de boutique expirés (le stock est regénéré lazily à la prochaine visite).';

    public function handle(): int
    {
        $this->info('Rafraîchissement de la boutique...');
        RefreshShop::dispatchSync();
        $this->info('Terminé.');
        return self::SUCCESS;
    }
}
