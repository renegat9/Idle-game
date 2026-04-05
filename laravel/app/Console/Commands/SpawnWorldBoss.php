<?php

namespace App\Console\Commands;

use App\Services\WorldBossService;
use Illuminate\Console\Command;

class SpawnWorldBoss extends Command
{
    /**
     * @var string
     */
    protected $signature = 'world-boss:spawn';

    /**
     * @var string
     */
    protected $description = 'Invoque un nouveau boss mondial si aucun n\'est actuellement actif.';

    public function __construct(private WorldBossService $worldBossService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $activeBoss = $this->worldBossService->getActiveBoss();

        if ($activeBoss !== null) {
            $this->info("Un boss mondial est déjà actif : {$activeBoss->name} (HP restants : {$activeBoss->current_hp}/{$activeBoss->total_hp}).");
            return self::SUCCESS;
        }

        $boss = $this->worldBossService->spawnBoss();

        $this->info("Boss mondial invoqué : {$boss->name} (slug: {$boss->slug}, HP: {$boss->total_hp}).");

        return self::SUCCESS;
    }
}
