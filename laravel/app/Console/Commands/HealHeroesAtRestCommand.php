<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\IdleService;
use Illuminate\Console\Command;

class HealHeroesAtRestCommand extends Command
{
    protected $signature   = 'heroes:heal-at-rest';
    protected $description = 'Guérit progressivement les héros des joueurs qui n\'explorent pas (10%/heure).';

    public function __construct(private readonly IdleService $idleService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Récupérer tous les utilisateurs avec des héros blessés et sans exploration active
        $users = User::whereHas('heroes', function ($q) {
                $q->where('is_active', true)->whereColumn('current_hp', '<', 'max_hp');
            })
            ->whereDoesntHave('activeExploration')
            ->whereNotNull('last_idle_calc_at')
            ->get();

        $healed = 0;
        foreach ($users as $user) {
            $this->idleService->healHeroesAtRest($user);
            $healed++;
        }

        $this->info("Guérison appliquée pour {$healed} joueur(s).");
        return self::SUCCESS;
    }
}
