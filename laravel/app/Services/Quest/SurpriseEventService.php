<?php

namespace App\Services\Quest;

use App\Models\User;
use App\Models\UserQuest;
use App\Services\LootService;
use App\Services\SettingsService;

/**
 * Surprise events S01-S15 triggered during quest steps.
 * QUESTS_EFFECTS.md §4.6.
 */
class SurpriseEventService
{
    public function __construct(
        private readonly SettingsService   $settings,
        private readonly QuestEffectService $effects,
        private readonly LootService        $loot,
    ) {}

    /**
     * Roll for a surprise event; returns event data or null if not triggered.
     */
    public function maybeTriggered(User $user, UserQuest $userQuest): ?array
    {
        $chance = (int) $this->settings->get('QUEST_SURPRISE_CHANCE', 15);
        if (rand(1, 100) > $chance) {
            return null;
        }
        return $this->trigger($user, $userQuest);
    }

    /**
     * Force-trigger a surprise event for the given quest context.
     */
    public function trigger(User $user, UserQuest $userQuest): array
    {
        $isWtf       = ($userQuest->quest->type ?? '') === 'wtf';
        $isLongQuest = ($userQuest->quest->steps_count ?? 0) >= 5;

        // S15: 2% rare override (WTF only)
        if ($isWtf && rand(1, 100) <= 2) {
            return $this->resolve('S15', $user, $userQuest);
        }

        $pool = ['S01','S02','S03','S04','S05','S06','S07','S09','S10','S11','S12'];
        if ($isLongQuest) {
            $pool[] = 'S08';
            $pool[] = 'S14';
        }
        if ($isWtf) {
            $pool[] = 'S13';
        }

        return $this->resolve($pool[array_rand($pool)], $user, $userQuest);
    }

    // ── Resolution handlers ───────────────────────────────────────────────────

    private function resolve(string $id, User $user, UserQuest $userQuest): array
    {
        $base = ['surprise_event' => $id];

        switch ($id) {
            case 'S01': // Marchand Errant — 3 objets à -50%
                return $base + [
                    'name'              => 'Marchand Errant',
                    'merchant_discount' => 50,
                    'items_count'       => 3,
                    'message'           => 'Un marchand surgit de nulle part. "Promotions spéciales !" Il sent la moisissure.',
                ];

            case 'S02': // Embuscade — combat surprise, ennemis +20% stats
                return $base + [
                    'name'             => 'Embuscade',
                    'type'             => 'combat',
                    'enemy_stat_bonus' => 20,
                    'message'          => 'EMBUSCADE ! Des ennemis surgissent. Et ils ont l\'air de mauvaise humeur.',
                ];

            case 'S03': // Coffre Piégé — 50% loot Rare / 50% D01
                if (rand(0, 1) === 1) {
                    $item = $this->loot->rollLoot($user, 1, 'rare');
                    return $base + [
                        'name'    => 'Coffre Piégé',
                        'result'  => 'loot',
                        'item'    => $item?->name,
                        'message' => 'Le coffre était... un vrai coffre ! Quelle surprise.',
                    ];
                }
                $this->effects->applyDebuff($user, 'D01', 'party');
                return $base + ['name' => 'Coffre Piégé', 'result' => 'trap', 'debuff' => 'D01', 'message' => 'Le coffre était piégé. Évidemment. Le Narrateur ricane.'];

            case 'S04': // PNJ en Détresse — réputation +10 + B06
                $this->effects->applyReputation($user, null, 10);
                $this->effects->applyBuff($user, 'B06', 'party');
                return $base + ['name' => 'PNJ en Détresse', 'reputation' => 10, 'buff' => 'B06', 'message' => 'Vous sauvez un PNJ coincé dans un arbre. Il dit "merci" en pleurant.'];

            case 'S05': // Fontaine Mystérieuse — 60% B01 / 40% D02
                if (rand(1, 100) <= 60) {
                    $this->effects->applyBuff($user, 'B01', 'party');
                    return $base + ['name' => 'Fontaine Mystérieuse', 'result' => 'blessing', 'buff' => 'B01', 'message' => 'L\'eau a un goût bizarre mais vous vous sentez revigorés ?'];
                }
                $this->effects->applyDebuff($user, 'D02', 'party');
                return $base + ['name' => 'Fontaine Mystérieuse', 'result' => 'cursed', 'debuff' => 'D02', 'message' => 'L\'eau avait définitivement un goût bizarre. Et maintenant votre tête tourne.'];

            case 'S06': // Carte au Trésor — mini-quête 2 étapes
                return $base + ['name' => 'Carte au Trésor', 'mini_quest' => true, 'steps' => 2, 'message' => 'Vous trouvez une carte au trésor. Elle a l\'air authentique. Ou presque.'];

            case 'S07': // Fantôme Bavard — indice narratif / recette
                return $base + ['name' => 'Fantôme Bavard', 'type' => 'narrative', 'message' => 'Un fantôme vous parle pendant 10 minutes. La moitié était incompréhensible. L\'autre moitié aussi.'];

            case 'S08': // Éboulement — +1 étape quête +10% loot boss
                return $base + ['name' => 'Éboulement', 'extra_steps' => 1, 'boss_loot_bonus' => 10, 'message' => 'Un éboulement bloque le chemin. Détour obligatoire. Le Narrateur dit que c\'est votre faute.'];

            case 'S09': // Rival Aventurier — gagner B02+or / perdre D05
                if (rand(0, 1) === 1) {
                    $this->effects->applyBuff($user, 'B02', 'attacker');
                    $gold = rand(50, 150);
                    $user->increment('gold', $gold);
                    return $base + ['name' => 'Rival Aventurier', 'result' => 'victory', 'buff' => 'B02', 'gold' => $gold, 'message' => 'Vous battez l\'aventurier rival. Il repart en boudant. Vous récupérez son or.'];
                }
                $this->effects->applyDebuff($user, 'D05', 'leader');
                return $base + ['name' => 'Rival Aventurier', 'result' => 'defeat', 'debuff' => 'D05', 'message' => 'L\'aventurier rival vous humilie devant tout le village. C\'était insupportable.'];

            case 'S10': // Trait en Action — comique, aucun malus mécanique
                $hero  = $user->activeHeroes()->get()->filter(fn($h) => $h->trait_)->random();
                return $base + [
                    'name'    => 'Trait en Action',
                    'type'    => 'comic',
                    'hero'    => $hero?->name,
                    'trait'   => $hero?->trait_?->name,
                    'message' => ($hero?->name ?? 'Un héros') . ' a fait quelque chose de bizarre lié à son trait. Le Narrateur est aux anges.',
                ];

            case 'S11': // Météo Bizarre — 70% esquive +5% / 30% VIT -5%
                if (rand(1, 100) <= 70) {
                    $this->effects->applyBuff($user, 'B13', 'party');
                    return $base + ['name' => 'Météo Bizarre', 'result' => 'slippery', 'message' => 'Il pleut des poissons. Le sol est glissant pour tout le monde — y compris les ennemis.'];
                }
                $this->effects->applyDebuff($user, 'D08', 'party');
                return $base + ['name' => 'Météo Bizarre', 'result' => 'hindrance', 'message' => 'Grêle de cailloux. Vous avancez moins vite. C\'est difficile de courir avec un casque plein de gravats.'];

            case 'S12': // Le Narrateur Intervient — narratif pur
                return $base + ['name' => 'Le Narrateur Intervient', 'type' => 'narrative', 'narrator_override' => true, 'message' => '"En fait non, ce n\'était pas ce que je vous avais dit. Surprise." — Le Narrateur.'];

            case 'S13': // Inspiration Subite — +1 point de talent temporaire (WTF seulement)
                $heroes = $user->activeHeroes()->get();
                if ($heroes->isNotEmpty()) {
                    $hero = $heroes->random();
                    $hero->increment('talent_points', 1);
                    return $base + ['name' => 'Inspiration Subite', 'hero' => $hero->name, 'talent_points_gained' => 1, 'message' => $hero->name . ' a soudainement une idée brillante. Ça arrive.'];
                }
                return $base + ['name' => 'Inspiration Subite', 'message' => 'Une idée brillante surgit de nulle part. Personne ne sait d\'où.'];

            case 'S14': // Raccourci Dangereux — choix au joueur
                return $base + [
                    'name'                   => 'Raccourci Dangereux',
                    'type'                   => 'choice',
                    'shortcut_skip_steps'    => 2,
                    'shortcut_combat_bonus'  => 30,
                    'message'                => 'Un raccourci ! Tentant. Mais les bruits venant de là-dedans ne sont pas rassurants.',
                ];

            case 'S15': // Trou Dimensionnel — quête bonus, récompenses ×2 (WTF 2%)
                return $base + [
                    'name'              => 'Trou Dimensionnel',
                    'type'              => 'bonus_quest',
                    'reward_multiplier' => 2,
                    'message'           => 'Un trou dimensionnel s\'ouvre. Vous tombez dedans. C\'est... une pizzeria médiévale ?',
                ];
        }

        return $base + ['name' => 'Événement Mystérieux', 'message' => 'Il se passe quelque chose d\'imprévisible.'];
    }
}
