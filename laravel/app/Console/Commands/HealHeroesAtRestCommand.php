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
            ->get();

        if ($users->isEmpty()) {
            $this->info('Aucun joueur à soigner (tous à fond ou en exploration).');
            return self::SUCCESS;
        }

        $totalHealed = 0;
        foreach ($users as $user) {
            $result = $this->idleService->healHeroesAtRest($user);

            $lastCalc = $user->fresh()->last_idle_calc_at;
            $this->line("── <fg=yellow>{$user->email}</>");
            $this->line("   last_idle_calc_at = " . ($lastCalc ? $lastCalc->toDateTimeString() . ' (il y a ' . $lastCalc->diffForHumans() . ')' : 'NULL'));
            $this->line("   elapsed={$result['elapsed_minutes']} min, heal={$result['heal_percent']}%");

            // Toujours afficher les PV actuels de chaque héros (en DB)
            $heroes = $user->heroes()->where('is_active', true)
                ->with(['race', 'gameClass', 'equippedItems'])
                ->get();
            foreach ($heroes as $hero) {
                $trueMax = $hero->computedStats()['max_hp'];
                $status  = $hero->current_hp >= $trueMax ? '<fg=green>FULL</>' : '<fg=red>BLESSÉ</>';
                $this->line("   {$status} {$hero->name} : {$hero->current_hp} / {$trueMax} PV (DB max_hp={$hero->max_hp})");
            }

            if ($result['initialized']) {
                $this->line('   <fg=cyan>→ Timer initialisé, soin au prochain passage</fg=cyan>');
                continue;
            }

            if (empty($result['heroes'])) {
                $this->line('   <fg=gray>→ Pas de soin appliqué (heal_percent=0 ou tous à fond)</fg=gray>');
                continue;
            }

            foreach ($result['heroes'] as $h) {
                $this->line("   <fg=green>✓ Soigné :</> {$h['name']} : {$h['hp_before']} → {$h['hp_after']} / {$h['max_hp']} PV (+{$h['gained']})");
                $totalHealed++;
            }
        }

        $this->info("\nGuérison terminée : {$totalHealed} héro(s) soigné(s) sur " . $users->count() . ' joueur(s).');
        return self::SUCCESS;
    }
}
