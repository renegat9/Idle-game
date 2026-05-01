<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Zone3MinesQuestSeeder extends Seeder
{
    public function run(): void
    {
        $zoneId = DB::table('zones')->where('slug', 'mines_nain')->value('id');
        if (!$zoneId) {
            $this->command->warn('Zone mines_nain introuvable — seeder ignoré.');
            return;
        }

        $quests = [
            [
                'title'              => 'L\'Effondrement Évitable',
                'description'        => 'Une galerie de la mine est sur le point de s\'effondrer. Thorin le Nain Ivre assure que ce n\'est pas grave. Il dit ça en se tenant à un pilier qui penche. Vous n\'êtes pas convaincus.',
                'steps_count'        => 3,
                'order_index'        => 1,
                'reward_xp'          => 450,
                'reward_gold'        => 280,
                'reward_loot_rarity' => 'commun',
                'is_repeatable'      => true,
                'steps' => [
                    [
                        'narration'        => 'Le pilier central de la galerie B craque de façon inquiétante. Thorin dit que c\'est "le bruit normal du bon bois". Le bois est en pierre. Thorin est ivre.',
                        'narrator_comment' => 'Un nain ivre évalue la solidité structurelle d\'une mine. Le Narrateur a vu pire. Pas souvent.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Inspecter les structures (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 30], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous identifiez trois points critiques. La situation est grave mais gérable.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D10', 'target' => 'party']], 'narration' => 'Vous touchez le mauvais pilier. Des pierres tombent. Tout le monde court.']],
                            ['id' => 'B', 'text' => 'Demander l\'aide des mineurs (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 25], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Les mineurs acceptent d\'aider. L\'un d\'eux sait exactement quoi faire — il attendait qu\'on lui demande.'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Les mineurs haussent les épaules. "C\'est le problème de Thorin." Thorin est toujours ivre.']],
                        ],
                    ],
                    [
                        'narration'        => 'Il faut étayer la galerie avant qu\'elle s\'effondre complètement. Il y a des poutres disponibles mais elles sont de l\'autre côté d\'un couloir partiellement bouché par des rochers.',
                        'narrator_comment' => 'Les poutres sont à 10 mètres. Le couloir est un peu bouché. C\'est la mine, pas un palais.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Dégager les rochers à la force (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 35], 'success' => ['next_step' => 3, 'effects' => [['type' => 'buff', 'id' => 'B02', 'target' => 'party']], 'narration' => 'Vous dégagez le passage rapidement. Les poutres sont récupérées.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'Les rochers bougent mais pas comme prévu. Quelques contusions. Les poutres sont récupérées quand même.']],
                            ['id' => 'B', 'text' => 'Trouver une voie détournée (test VIT)', 'test' => ['stat' => 'vit', 'difficulty' => 28], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous contournez par un tunnel secondaire. Les poutres sont là.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D08', 'target' => 'leader']], 'narration' => 'Le tunnel secondaire était plus étroit que prévu. Beaucoup plus étroit.']],
                        ],
                    ],
                    [
                        'narration'        => 'Les poutres sont en place. La galerie tient. Thorin regarde l\'ensemble et dit "j\'allais faire ça moi-même". Il tient toujours son chope.',
                        'narrator_comment' => 'La mine est sauvée. Thorin revendique la paternité de l\'idée. Le Narrateur note tout.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Accepter les remerciements de Thorin', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 10]], 'narration' => 'Thorin vous remet la récompense en renversant légèrement de la bière dessus. C\'est son signe d\'affection.'], 'failure' => null],
                            ['id' => 'B', 'text' => 'Faire remarquer que Thorin n\'a rien fait', 'test' => ['stat' => 'cha', 'difficulty' => 50], 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 15], ['type' => 'gold', 'amount' => 50]], 'narration' => 'Thorin rit. Il vous donne un bonus. "Vous avez du caractère."'], 'failure' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 5]], 'narration' => 'Thorin est vexé. La récompense est quand même là. Mais il boude.']],
                        ],
                    ],
                ],
            ],
            [
                'title'              => 'La Bière de la Discorde',
                'description'        => 'Deux clans de nains se disputent une barrique de bière légendaire trouvée dans une galerie abandonnée. Des pioches sont sorties. Un nain a commencé à rédiger un manifeste.',
                'steps_count'        => 4,
                'order_index'        => 2,
                'reward_xp'          => 650,
                'reward_gold'        => 400,
                'reward_loot_rarity' => 'peu_commun',
                'is_repeatable'      => true,
                'steps' => [
                    [
                        'narration'        => 'Le Clan Grongrak et le Clan Brutok se font face autour d\'une barrique. Elle est couverte de toiles d\'araignées et sent divinement la bière ambrée. Les deux clans crient depuis une heure. La barrique attend patiemment.',
                        'narrator_comment' => 'Deux clans nains, une barrique de bière, et vous au milieu. Le Narrateur prend des paris.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'S\'interposer calmement (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 35], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Les nains vous écoutent. Temporairement. C\'est déjà bien.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D05', 'target' => 'leader']], 'narration' => 'Les deux clans vous ignorent et vous aspergent accidentellement de bière en gesticulant.']],
                            ['id' => 'B', 'text' => 'Examiner la barrique (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 28], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'La barrique porte une marque — clan Grongrak, année 847. Élément de preuve.'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous n\'arrivez pas à lire la marque. Elle est peut-être en vieux nanique.']],
                        ],
                    ],
                    [
                        'narration'        => 'Après négociation, les deux clans acceptent un principe : la bière sera partagée si quelqu\'un peut prouver son origine. Ils veulent un test de dégustation.',
                        'narrator_comment' => 'Un test de dégustation pour régler un conflit territorial. C\'est la méthode naine traditionnelle.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Goûter la bière vous-même (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 3, 'effects' => [['type' => 'buff', 'id' => 'B06', 'target' => 'party']], 'narration' => 'Votre avis convainc les deux clans. Et la bière est excellente.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D07', 'target' => 'party']], 'narration' => 'Vous toussez. Les nains rient. Mais ils vous respectent un peu plus pour avoir essayé.']],
                            ['id' => 'B', 'text' => 'Proposer un arbitre neutre', 'test' => ['stat' => 'int', 'difficulty' => 32], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous trouvez un vieux nain qui connaît les deux brasseries. Il tranche.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'L\'arbitre choisi par vous avait un conflit d\'intérêts. Il était du Clan Grongrak.']],
                        ],
                    ],
                    [
                        'narration'        => 'Les deux clans acceptent un partage à 50/50. Il reste à transporter la barrique sans incident. Elle est lourde. Très lourde.',
                        'narrator_comment' => 'La paix est conclue. La barrique pèse 200 kilos. Le Narrateur parie que quelqu\'un va trébucher.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Faire rouler la barrique (test VIT)', 'test' => ['stat' => 'vit', 'difficulty' => 30], 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'La barrique roule parfaitement jusqu\'au camp central. Les nains applaudissent.'], 'failure' => ['next_step' => 4, 'effects' => [['type' => 'gold', 'amount' => -80]], 'narration' => 'La barrique dévale une pente. Vous perdez environ 80 or de bière dans des flaques. Les nains pleurent.']],
                            ['id' => 'B', 'text' => 'Mobiliser les deux clans pour porter (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 25], 'success' => ['next_step' => 4, 'effects' => [['type' => 'buff', 'id' => 'B02', 'target' => 'party']], 'narration' => 'Ensemble, les deux clans portent la barrique. C\'est presque émouvant.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'Elle est vraiment très lourde. Vous arrivez quand même.']],
                        ],
                    ],
                    [
                        'narration'        => 'La barrique est partagée. Les deux clans boivent ensemble pour la première fois en 40 ans. Thorin pleure. Il dit que ce n\'est pas la bière. C\'est la bière.',
                        'narrator_comment' => 'Paix dans les mines. Grâce à une barrique de bière. Le Narrateur ne pouvait pas inventer ça.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Accepter une chope offerte par les deux clans', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 15], ['type' => 'buff', 'id' => 'B04', 'target' => 'party']], 'narration' => 'Vous buvez. Les nains vous adoptent symboliquement. Votre réputation dans les mines augmente considérablement.'], 'failure' => null],
                        ],
                    ],
                ],
            ],
            [
                'title'              => 'Les Cristaux Trop Brillants',
                'description'        => 'Des cristaux magiques aveuglants ont poussé dans le chemin principal. Les mineurs travaillent les yeux fermés. Ça n\'améliore pas le rendement.',
                'steps_count'        => 4,
                'order_index'        => 3,
                'reward_xp'          => 800,
                'reward_gold'        => 500,
                'reward_loot_rarity' => 'rare',
                'is_repeatable'      => false,
                'steps' => [
                    [
                        'narration'        => 'Les cristaux sont immenses et pulsent d\'une lumière dorée. C\'est magnifique. Ça brûle les yeux immédiatement. Deux mineurs ont déjà marché dans un puits à cause d\'eux.',
                        'narrator_comment' => 'Des cristaux magiques dans une mine. Classique. Le Narrateur note que "magnifique" et "dangereux" coexistent souvent.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Analyser les cristaux avec précaution (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 38], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous déterminez qu\'ils réagissent à la chaleur. Solution possible : les refroidir.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D10', 'target' => 'leader']], 'narration' => 'Vous vous approchez trop. Aveuglé temporairement. Les cristaux continuent de briller, indifférents.']],
                            ['id' => 'B', 'text' => 'Tenter de les casser (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 50], 'success' => ['next_step' => 2, 'effects' => [['type' => 'loot', 'rarity_min' => 'rare']], 'narration' => 'Un cristal se brise, révélant un fragment magique réutilisable.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'Le marteau rebondit et les cristaux vibrent, intensifiant la lumière. Mauvaise idée.']],
                        ],
                    ],
                    [
                        'narration'        => 'Il faut soit couvrir les cristaux, soit les déplacer. Un vieux mineur marmonne qu\'il a vu des cristaux similaires réagir à l\'eau de source froide.',
                        'narrator_comment' => 'Un vieux mineur qui sait des choses. Ces personnages existent uniquement pour donner des indices.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Trouver de l\'eau de source dans la mine (test VIT)', 'test' => ['stat' => 'vit', 'difficulty' => 30], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Une source souterraine non loin. Vous remplissez des récipients.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous trouvez une flaque. Ça fera l\'affaire. Peut-être.']],
                            ['id' => 'B', 'text' => 'Fabriquer des couvertures opaques (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 25], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Des toiles de sacs + quelques planches. Rudimentaire mais opaque.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vos couvertures ont des trous. La lumière passe encore. Plan B requis.']],
                        ],
                    ],
                    [
                        'narration'        => 'Avec l\'eau froide ou les couvertures, vous approchez des cristaux. La lumière diminue. Il reste à sécuriser la zone pour que les mineurs puissent reprendre le travail.',
                        'narrator_comment' => 'Presque réglé. Le Narrateur apprécie l\'approche méthodique. Ou pas. Il est difficile à cerner.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Installer un périmètre de sécurité (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 28], 'success' => ['next_step' => 4, 'effects' => [['type' => 'buff', 'id' => 'B03', 'target' => 'party']], 'narration' => 'Des barricades solides. La zone est sécurisée. Professionnel.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'Les barricades tiennent à peu près. C\'est suffisant.']],
                            ['id' => 'B', 'text' => 'Expliquer aux mineurs comment éviter les risques', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Les mineurs comprennent et écoutent. L\'un d\'eux prend des notes.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'Les mineurs hochent la tête sans écouter. C\'est la tradition.']],
                        ],
                    ],
                    [
                        'narration'        => 'Les cristaux sont maîtrisés. La mine peut reprendre son activité. Thorin vous serre la main avec une poigne qui indique qu\'il a passé 60 ans à manier une pioche.',
                        'narrator_comment' => 'Problème résolu. Les mines tournent à nouveau. Thorin est sobre cette fois. Relativement.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Garder un fragment de cristal en souvenir', 'test' => ['stat' => 'cha', 'difficulty' => 20], 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 20], ['type' => 'loot', 'rarity_min' => 'rare']], 'narration' => 'Thorin vous offre le plus beau fragment. "Ne le vendez pas trop vite, il vaut quelque chose."'], 'failure' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'mines_nain', 'amount' => 10]], 'narration' => 'Thorin préfère garder les cristaux. Vous avez la récompense prévue.']],
                        ],
                    ],
                ],
            ],
        ];

        $this->insertQuests($zoneId, $quests);
    }

    private function insertQuests(int $zoneId, array $quests): void
    {
        foreach ($quests as $questData) {
            $steps = $questData['steps'];
            unset($questData['steps']);

            $exists = DB::table('quests')
                ->where('zone_id', $zoneId)
                ->where('title', $questData['title'])
                ->exists();
            if ($exists) {
                continue;
            }

            $questData['zone_id']         = $zoneId;
            $questData['type']            = 'zone';
            $questData['is_ai_generated'] = false;
            $questData['created_at']      = now();
            $questData['updated_at']      = now();

            $questId = DB::table('quests')->insertGetId($questData);

            foreach ($steps as $index => $step) {
                DB::table('quest_steps')->insert([
                    'quest_id'   => $questId,
                    'step_index' => $index + 1,
                    'content'    => json_encode($step, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
