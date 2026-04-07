<?php

namespace App\Console\Commands;

use App\Models\WorldBoss;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Simule des attaques NPC (bots) sur le boss mondial toutes les 2 heures.
 * Permet d'animer le boss même sans joueurs actifs et de le faire évoluer.
 *
 * php artisan world-boss:auto-attack
 */
class WorldBossAutoAttackCommand extends Command
{
    protected $signature = 'world-boss:auto-attack';
    protected $description = 'Simule des attaques automatiques de NPCs sur le boss mondial';

    // Noms de NPCs pour simuler de l'activité
    private const NPC_NAMES = [
        'Adventurier_Anonyme', 'Héros_Aléatoire', 'Bard_Bot_3000',
        'Le_Vrai_Protagoniste', 'PNJ_Courageux', 'Gruntak_v2',
        'L\'IA_Qui_Essaie', 'Gladiateur_Offline', 'Le_Fantôme_Errant',
        'Mercenaire_Inconnu',
    ];

    public function __construct(private readonly SettingsService $settings)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $boss = WorldBoss::where('status', 'active')->first();

        if (!$boss) {
            $this->info('Aucun boss actif. Rien à faire.');
            return self::SUCCESS;
        }

        // Nombre d'attaques NPC par cycle (paramétrable)
        $npcAttackCount = $this->settings->get('WORLD_BOSS_NPC_ATTACKS_PER_CYCLE', 5);

        $totalDamage = 0;
        $attacks     = [];

        for ($i = 0; $i < $npcAttackCount; $i++) {
            // Dégâts NPC : basés sur le niveau du boss, avec variance
            $baseDamage = intdiv($boss->total_hp, 500); // ~0.2% du HP total par attaque NPC
            $baseDamage = max(10, $baseDamage);
            $variance   = random_int(80, 120);
            $damage     = intdiv($baseDamage * $variance, 100);

            $npcName = self::NPC_NAMES[array_rand(self::NPC_NAMES)];
            $attacks[] = ['name' => $npcName, 'damage' => $damage];
            $totalDamage += $damage;
        }

        // Appliquer les dégâts en transaction
        $bossDefeated = false;
        DB::transaction(function () use ($boss, $totalDamage, &$bossDefeated) {
            $boss->refresh();
            if ($boss->status !== 'active') {
                return;
            }

            $newHp = max(0, $boss->current_hp - $totalDamage);
            $boss->current_hp = $newHp;

            if ($newHp <= 0) {
                $boss->status      = 'defeated';
                $boss->defeated_at = now();
                $bossDefeated      = true;
            }

            $boss->save();
        });

        // Logger les attaques NPC (application log uniquement — pas de FK user)
        Log::info("world-boss:auto-attack — boss_id={$boss->id} damage={$totalDamage} attacks={$npcAttackCount}", [
            'attacks' => $attacks,
        ]);

        $this->info("Boss mondial : {$npcAttackCount} attaques NPC, {$totalDamage} dégâts totaux infligés.");

        if ($bossDefeated) {
            $this->info('Le boss mondial a été vaincu par les NPCs !');
        } else {
            $boss->refresh();
            $this->info("HP restants : {$boss->current_hp} / {$boss->total_hp}");
        }

        return self::SUCCESS;
    }
}
