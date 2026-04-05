<?php

namespace App\Services;

use App\Models\NarratorCache;

class NarratorService
{
    public function __construct(private readonly GeminiService $gemini) {}

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

        // 1. Check cache (avoid duplicate AI calls for same context)
        $cached = NarratorCache::where('event_type', $eventType)
            ->where('context_hash', $contextHash)
            ->first();

        if ($cached) {
            $cached->increment('usage_count');
            return $cached->text;
        }

        // 2. Try AI generation if enabled
        $isAi = false;
        if ($this->gemini->canCall('narration')) {
            $text = $this->gemini->generateNarration($eventType, $context);
            // Fallback templates start with "Victoire" / "Défaite" etc. — AI text is typically different
            $isAi = true;
        } else {
            $text = $this->getStaticTemplate($eventType, $context);
        }

        // 3. Cache the result
        NarratorCache::create([
            'event_type'      => $eventType,
            'context_hash'    => $contextHash,
            'text'            => $text,
            'is_ai_generated' => $isAi,
        ]);

        return $text;
    }

    private array $extraTemplates = [
        'tavern_visited' => [
            'La taverne. Lieu de repos, de bière et de mauvaises décisions. Le Narrateur attendait votre arrivée.',
            'Vous entrez dans la taverne. L\'ambiance est chaleureuse. Les recrues vous regardent avec espoir. Ne les décevez pas... trop.',
            'Une taverne. Enfin un endroit où votre réputation ne vous a pas encore précédé.',
        ],
        'quest_completed' => [
            'Quête terminée. Contre toute attente.',
            '{quest_title} — accomplie. Le Narrateur met à jour ses archives avec un soupir résigné.',
            'Mission accomplie ! Le Narrateur est surpris. Il prend ça en note.',
        ],
        'craft_failure' => [
            'Fusion ratée. La forge fume encore. Gérard est embarrassé.',
            'Raté. Même Gérard n\'a pas vu ça venir. Enfin si, un peu.',
            'La fusion a échoué. Le Narrateur n\'est pas surpris. Vous devriez l\'être.',
        ],
        'dungeon_start' => [
            'Vos héros pénètrent dans le donjon. Le Narrateur prépare ses condoléances.',
            'Le donjon s\'ouvre. Sombre, humide, et rempli de mauvaises surprises. Bienvenue.',
            'Aventure en donjon ! Le Narrateur note que personne ne le lui a demandé.',
        ],
        'dungeon_completed' => [
            'Donjon terminé ! Contre toute attente, vos héros sont encore en vie. Bravo.',
            'Victoire complète ! Le Narrateur est modérément impressionné. C\'est rare.',
            'Le donjon est conquis. Vos héros ressortent couverts de gloire et de boue.',
        ],
        'dungeon_failed' => [
            'Défaite dans le donjon. Le Narrateur a vu pire. Mais pas souvent.',
            'Vos héros gisent dans le donjon. La honte est proportionnelle à la distance.',
            'Échec. Le donjon a gagné. Il s\'en fiche, mais quand même.',
        ],
        'dungeon_abandoned' => [
            'Abandonné ! Vos héros fuient le donjon. C\'est une retraite stratégique.',
            'Fuite honteuse. Le donjon regarde partir vos héros avec mépris.',
            'Abandon acté. Le Narrateur n\'est pas surpris. Il l\'attendait, celui-là.',
        ],
        'dungeon_boss_defeated' => [
            'Le boss est vaincu ! Le Narrateur reconnaît que c\'était presque impressionnant.',
            'Boss terrassé ! Pour une fois, vos héros ont fait leur travail.',
            'Le gardien du donjon est tombé. Il avait l\'air pourtant si menaçant.',
        ],
        'trap_triggered' => [
            'Piège déclenché ! Le Narrateur avait pourtant mis des panneaux d\'avertissement.',
            'PIÈGE ! Vos héros encaissent avec la dignité qu\'on leur connaît.',
            'Le piège s\'active. Vos héros n\'avaient qu\'à regarder où ils marchaient.',
        ],
        'rest_room' => [
            'Vos héros se reposent. Brève accalmie dans ce chaos organisé.',
            'Une salle de repos ! Le Narrateur admet que c\'est mérité.',
            'Repos bien gagné. Les héros récupèrent. Le Narrateur en profite aussi.',
        ],
    ];

    private function getStaticTemplate(string $eventType, array $context): string
    {
        $templates = $this->templates[$eventType] ?? $this->extraTemplates[$eventType] ?? $this->templates['default'];

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
