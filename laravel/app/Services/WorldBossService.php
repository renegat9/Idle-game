<?php

namespace App\Services;

use App\Models\BossContribution;
use App\Models\Hero;
use App\Models\User;
use App\Models\WorldBoss;
use Illuminate\Support\Facades\DB;

class WorldBossService
{
    /**
     * Définitions statiques des boss disponibles.
     */
    private array $bossDefinitions = [
        [
            'name'             => 'Le Dragon Distrait',
            'slug'             => 'le-dragon-distrait',
            'total_hp'         => 50000,
            'special_mechanic' => 'enrage',
        ],
        [
            'name'             => 'Le Lich Procrastinateur',
            'slug'             => 'le-lich-procrastinateur',
            'total_hp'         => 75000,
            'special_mechanic' => 'shield_phase',
        ],
        [
            'name'             => 'Le Troll Philosophe',
            'slug'             => 'le-troll-philosophe',
            'total_hp'         => 40000,
            'special_mechanic' => null,
        ],
    ];

    /**
     * Commentaires du Narrateur pour les attaques sur le boss mondial.
     */
    private array $narrationTemplates = [
        'Le Boss mondial daigne remarquer vos coups. À peine.',
        'Vous frappez le Boss. Il hausse un sourcil. C\'est déjà quelque chose.',
        'Attaque lancée ! Le Boss bâille poliment.',
        'Vos héros s\'acharnent sur le Boss avec un enthousiasme inversement proportionnel à leur efficacité.',
        'Coup porté ! Le Narrateur note le manque flagrant d\'effet dramatique.',
        'Le Boss encaisse. Il a l\'air vaguement ennuyé.',
        'Votre équipe frappe fort. "Fort" étant relatif, bien sûr.',
        'Les dégâts sont infligés. Le Boss consulte son agenda pour voir s\'il peut mourir bientôt.',
    ];

    private array $defeatTemplates = [
        'Le Boss s\'effondre ! Contre toute attente, vous avez gagné. Le Narrateur est stupéfait.',
        'Vaincu ! Le Boss mondial tombe. Le Narrateur vérifie ses notes pour s\'assurer que c\'est bien réel.',
        'DÉFAITE DU BOSS ! C\'est historique. Et légèrement accidentel, probablement.',
        'Le Boss est abattu ! Votre incompétence collective a, paradoxalement, triomphé.',
    ];

    public function __construct(
        private SettingsService $settings,
        private GeminiService $gemini,
    ) {}

    /**
     * Retourne le boss mondial actuellement actif, ou null.
     */
    public function getActiveBoss(): ?WorldBoss
    {
        return WorldBoss::where('status', 'active')->first();
    }

    /**
     * Attaque le boss mondial actif avec l'équipe de héros de l'utilisateur.
     *
     * @return array{damage_dealt: int, boss_current_hp: int, boss_defeated: bool, narration: string}
     */
    public function attack(User $user): array
    {
        $boss = $this->getActiveBoss();

        if ($boss === null) {
            return [
                'error'       => 'no_active_boss',
                'damage_dealt'    => 0,
                'boss_current_hp' => 0,
                'boss_defeated'   => false,
                'narration'       => 'Aucun boss actif à attaquer.',
            ];
        }

        // Vérification du cooldown
        $cooldown = $this->getCooldownRemaining($user, $boss);
        if ($cooldown > 0) {
            return [
                'error'             => 'cooldown',
                'seconds_remaining' => $cooldown,
                'damage_dealt'      => 0,
                'boss_current_hp'   => $boss->current_hp,
                'boss_defeated'     => false,
                'narration'         => 'Vos héros soufflent encore. Patience.',
            ];
        }

        // Calcul des dégâts
        $heroes = $user->activeHeroes()->with(['race', 'gameClass', 'equippedItems'])->get();

        $teamPower = 0;
        foreach ($heroes as $hero) {
            $stats = $hero->computedStats();
            $teamPower += $stats['atq'] + $stats['int'];
        }

        $damageScale = $this->settings->get('WORLD_BOSS_DAMAGE_SCALE', 10);
        $baseDamage  = intdiv($teamPower * $damageScale, 100);
        $variance    = random_int(
            $this->settings->get('VARIANCE_MIN', 90),
            $this->settings->get('VARIANCE_MAX', 110)
        );
        $finalDamage = intdiv($baseDamage * $variance, 100);
        $finalDamage = max(1, $finalDamage);

        // Transaction: mise à jour boss + contribution
        $bossDefeated = false;
        $newHp        = 0;

        DB::transaction(function () use ($boss, $user, $finalDamage, &$bossDefeated, &$newHp) {
            // Recharger le boss en mode verrou pour éviter les race conditions
            $boss->refresh();

            if ($boss->status !== 'active') {
                // Boss déjà vaincu entre-temps
                $newHp        = $boss->current_hp;
                $bossDefeated = $boss->status === 'defeated';
                return;
            }

            $newHp = max(0, $boss->current_hp - $finalDamage);
            $boss->current_hp = $newHp;

            if ($newHp <= 0) {
                $boss->status      = 'defeated';
                $boss->defeated_at = now();
                $bossDefeated      = true;
            }

            $boss->save();

            // Mise à jour ou création de la contribution
            $contribution = BossContribution::firstOrNew([
                'boss_id' => $boss->id,
                'user_id' => $user->id,
            ]);

            $contribution->damage_dealt += $finalDamage;
            $contribution->hits_count   += 1;
            $contribution->save();
        });

        // Choisir la narration
        if ($bossDefeated) {
            $narration = $this->defeatTemplates[array_rand($this->defeatTemplates)];
        } else {
            $narration = $this->narrationTemplates[array_rand($this->narrationTemplates)];
        }

        return [
            'damage_dealt'    => $finalDamage,
            'boss_current_hp' => $newHp,
            'boss_defeated'   => $bossDefeated,
            'narration'       => $narration,
        ];
    }

    /**
     * Retourne le classement des 10 meilleurs contributeurs pour le boss actif ou un boss donné.
     *
     * @return array<int, array{username: string, damage_dealt: int, hits_count: int}>
     */
    public function getLeaderboard(?int $bossId = null): array
    {
        if ($bossId === null) {
            $boss = WorldBoss::whereIn('status', ['active', 'defeated'])
                ->orderByDesc('id')
                ->first();

            if ($boss === null) {
                return [];
            }

            $bossId = $boss->id;
        }

        return BossContribution::where('boss_id', $bossId)
            ->join('users', 'users.id', '=', 'boss_contributions.user_id')
            ->orderByDesc('boss_contributions.damage_dealt')
            ->limit(10)
            ->get(['users.username', 'boss_contributions.damage_dealt', 'boss_contributions.hits_count'])
            ->map(fn($row) => [
                'username'     => $row->username,
                'damage_dealt' => (int) $row->damage_dealt,
                'hits_count'   => (int) $row->hits_count,
            ])
            ->toArray();
    }

    /**
     * Invoque un nouveau boss mondial (appelé par la commande console ou le scheduler).
     * Enrichit le boss avec du texte IA (description + mécanique) si Gemini est disponible.
     */
    public function spawnBoss(): WorldBoss
    {
        // Choisir un boss au hasard parmi les définitions
        $definition = $this->bossDefinitions[array_rand($this->bossDefinitions)];

        // Enrichir avec texte IA (description narrative + mécanique spéciale)
        $aiText = $this->gemini->generateBossText($definition['name']);

        $boss = WorldBoss::create([
            'name'             => $definition['name'],
            'slug'             => $definition['slug'],
            'total_hp'         => $definition['total_hp'],
            'current_hp'       => $definition['total_hp'],
            'status'           => 'active',
            'special_mechanic' => $definition['special_mechanic'] ?? $aiText['mechanic'],
            'description'      => $aiText['description'],
            'spawned_at'       => now(),
            'defeated_at'      => null,
        ]);

        return $boss;
    }

    /**
     * Vérifie si l'utilisateur est en cooldown d'attaque.
     * Retourne le nombre de secondes restantes (0 si l'utilisateur peut attaquer).
     */
    public function getCooldownRemaining(User $user, WorldBoss $boss): int
    {
        $cooldownSeconds = $this->settings->get('WORLD_BOSS_ATTACK_COOLDOWN', 300);

        $contribution = BossContribution::where('boss_id', $boss->id)
            ->where('user_id', $user->id)
            ->first();

        if ($contribution === null) {
            return 0;
        }

        $lastAttackAt = $contribution->updated_at;
        if ($lastAttackAt === null) {
            return 0;
        }

        $elapsed   = (int) now()->diffInSeconds($lastAttackAt, false);
        $remaining = $cooldownSeconds + $elapsed; // elapsed est négatif car dans le passé

        return max(0, $remaining);
    }
}
