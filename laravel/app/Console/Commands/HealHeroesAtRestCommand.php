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
        $users = User::whereHas('heroes', function ($q) {
                $q->where('is_active', true)->whereColumn('current_hp', '<', 'max_hp');
            })
            ->whereDoesntHave('activeExploration')
            ->with('heroes')
            ->get();

        if ($users->isEmpty()) {
            $this->info('Aucun joueur à soigner (tous à fond ou en exploration).');
            return self::SUCCESS;
        }

        $totalHealed = 0;
        foreach ($users as $user) {
            $result = $this->idleService->healHeroesAtRest($user);

            $this->line("── <fg=yellow>{$user->email}</> ({$result['elapsed_minutes']} min écoulées, {$result['heal_percent']}% soin)");

            if ($result['initialized']) {
                $this->line('   <fg=cyan>→ Timer initialisé (pas de soin cette fois)</fg=cyan>');
                continue;
            }

            if (empty($result['heroes'])) {
                $this->line('   <fg=gray>→ Aucun héros soigné (déjà à fond ou heal_percent=0)</fg=gray>');
                continue;
            }

            foreach ($result['heroes'] as $h) {
                $this->line("   <fg=green>✓</fg=green> {$h['name']} : {$h['hp_before']} → {$h['hp_after']} / {$h['max_hp']} PV (+{$h['gained']})");
                $totalHealed++;
            }
        }

        $this->info("\nGuérison terminée : {$totalHealed} héro(s) soigné(s) sur " . $users->count() . ' joueur(s).');
        return self::SUCCESS;
    }
}
