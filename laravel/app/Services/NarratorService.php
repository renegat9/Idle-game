<?php

namespace App\Services;

use App\Models\NarratorCache;

class NarratorService
{
    private array $templates = [
        'combat_win' => [
            'Victoire ! Enfin, "victoire"... Vous avez vaincu des monstres. Bravo. Vraiment.',
            'L\'ennemi est vaincu. Le Narrateur note que ce n\'était pas particulièrement impressionnant.',
            'Succès ! Votre équipe incompétente a triomphé. Le Narrateur est presque fier.',
            'Vous avez gagné. Ne vous y habituez pas.',
            'Combat terminé. Victoire obtenue avec le minimum syndical d\'effort.',
        ],
        'combat_defeat' => [
            'Défaite. Remarquable dans sa médiocrité.',
            'Votre équipe gît sur le sol. Le Narrateur prend des notes.',
            'Battus. Par ça. Chapeau.',
            'La défaite est consommée. Le Narrateur a du mal à être surpris.',
            'Tombés au combat. Le Taureau vous remercie pour le repas.',
        ],
        'combat_draw' => [
            'Match nul. Tout ça pour ça.',
            'Ni victoire ni défaite. Une belle illustration de votre médiocrité.',
            'Égalité. Même les ennemis n\'avaient pas envie de finir ça.',
        ],
        'loot_found' => [
            'Un objet trouvé ! Il est probablement nul, mais c\'est le vôtre.',
            'Du loot ! Gérard en serait jaloux. Peut-être.',
            'Un objet apparaît dans votre inventaire. Le Narrateur note son inutilité potentielle.',
            'Trouvé quelque chose. Ça ne rendra pas votre équipe moins incompétente.',
            'Loot obtenu. Le Narrateur s\'abstient de commenter sa qualité.',
        ],
        'level_up' => [
            'Niveau supérieur ! Vous êtes maintenant légèrement moins incompétent.',
            'Montée de niveau ! Continuez comme ça et vous deviendrez... correct.',
            'Niveau suivant atteint. Le Narrateur acquiesce avec un enthousiasme modéré.',
            'Vous avez progressé ! C\'est inattendu et pourtant réel.',
        ],
        'trait_triggered_couard' => [
            'La fuite, c\'est une stratégie. Une mauvaise stratégie, mais une stratégie.',
            'Il fuit ! On ne peut pas lui en vouloir. Si, en fait, on peut.',
            'La panique a pris le dessus. Le Narrateur soupire.',
        ],
        'trait_triggered_narcoleptique' => [
            'Zzz... Le moment était vraiment bien choisi pour dormir.',
            'Il s\'endort. En plein combat. Remarquable.',
            'Les ronflements couvrent le bruit des épées.',
        ],
        'trait_triggered_pyromane' => [
            'Le feu résout tout. Surtout les amitiés.',
            'TOUT BRÛLE. C\'était prévisible.',
            'Le Pyromane frappe à nouveau. Et ses alliés aussi.',
        ],
        'trait_triggered_philosophe' => [
            'Mais au fond, pourquoi combattre ? Cette question mérite réflexion.',
            'Il s\'arrête pour méditer. Ses ennemis sont moins philosophiques.',
            'La grande question existentielle du combat l\'absorbe.',
        ],
        'trait_triggered_kleptomane' => [
            'Un objet a mystérieusement changé de poche.',
            'Ce n\'est pas du vol, c\'est de la redistribution des richesses.',
            'Kleptomane en action. Ses alliés vérifieront leurs poches plus tard.',
        ],
        'trait_triggered_allergique' => [
            'ATCHOUM — ah, le boss nous a vus maintenant.',
            'L\'allergie se manifeste au pire moment possible.',
            'Les éternuements résonnent dans tout le donjon.',
        ],
        'trait_triggered_pacifiste' => [
            'Il ne peut pas frapper quelque chose d\'aussi mignon.',
            'Le pacifisme triomphe. Et l\'ennemi en profite.',
            'Regardez cette petite tête ! Il refuse d\'attaquer.',
        ],
        'hero_created' => [
            'Un nouveau héros ! Le Narrateur retient son enthousiasme.',
            'Bienvenue dans l\'équipe des incompétents. Tu seras chez toi.',
            'Un héros de plus. Les donjons du monde tremblent... de rire.',
        ],
        'exploration_started' => [
            'L\'équipe part en exploration. Le Narrateur prépare ses condoléances.',
            'En route vers l\'aventure ! Et probablement la catastrophe.',
            'L\'exploration commence. Le Narrateur s\'installe confortablement.',
        ],
        'offline_return' => [
            'Tu es revenu. Le Narrateur a pris des notes sur tes absences.',
            'Enfin de retour ! Tes héros ont survécu. Plus ou moins.',
            'Reconnecté. Voilà ce qui s\'est passé pendant ton absence...',
        ],
        'default' => [
            'Quelque chose s\'est passé. Le Narrateur l\'a vu.',
            'Un événement notable. Relativement.',
            'Le Narrateur observe. Il commente moins. Il juge davantage.',
        ],
    ];

    public function getComment(string $eventType, array $context = []): string
    {
        $contextHash = md5($eventType . serialize($context));

        // Vérifier le cache
        $cached = NarratorCache::where('event_type', $eventType)
            ->where('context_hash', $contextHash)
            ->first();

        if ($cached) {
            $cached->increment('usage_count');
            return $cached->text;
        }

        $text = $this->getStaticTemplate($eventType, $context);

        NarratorCache::create([
            'event_type' => $eventType,
            'context_hash' => $contextHash,
            'text' => $text,
            'is_ai_generated' => false,
        ]);

        return $text;
    }

    private function getStaticTemplate(string $eventType, array $context): string
    {
        $templates = $this->templates[$eventType] ?? $this->templates['default'];

        $selected = $templates[array_rand($templates)];

        // Substitutions contextuelles basiques
        if (!empty($context['hero_name'])) {
            $selected = str_replace('{hero}', $context['hero_name'], $selected);
        }
        if (!empty($context['monster_name'])) {
            $selected = str_replace('{monster}', $context['monster_name'], $selected);
        }
        if (!empty($context['item_name'])) {
            $selected = str_replace('{item}', $context['item_name'], $selected);
        }

        return $selected;
    }
}
