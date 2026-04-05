<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');
        $foretId   = DB::table('zones')->where('slug', 'foret_elfes')->value('id');

        $quests = [
            // ── Zone 1 — La Prairie des Débutants ──
            [
                'zone_id'            => $prairieId,
                'type'               => 'zone',
                'title'              => 'Le Rat et le Fromage',
                'description'        => 'Un fermier vous supplie de récupérer son fromage dérobé par un rat peureux. C\'est votre toute première quête. Le Narrateur vous souhaite bonne chance avec une inflexion qui sous-entend clairement le contraire.',
                'steps_count'        => 3,
                'order_index'        => 1,
                'reward_xp'          => 50,
                'reward_gold'        => 30,
                'reward_loot_rarity' => 'commun',
                'is_repeatable'      => true,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $prairieId,
                'type'               => 'zone',
                'title'              => 'Les Slimes du Chemin',
                'description'        => 'Le chemin principal est infesté de slimes. Un marchand offre une récompense. Il a l\'air désespéré. C\'est peut-être le seul marchand désespéré de payer des aventuriers incompétents.',
                'steps_count'        => 4,
                'order_index'        => 2,
                'reward_xp'          => 100,
                'reward_gold'        => 60,
                'reward_loot_rarity' => 'commun',
                'is_repeatable'      => true,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $prairieId,
                'type'               => 'zone',
                'title'              => 'Le Gobelin Chapardeur et la Caisse Enregistreuse',
                'description'        => 'Un gobelin a volé la caisse du marché local. La marchande est en larmes. Ou peut-être qu\'elle coupe des oignons. Difficile à dire.',
                'steps_count'        => 4,
                'order_index'        => 3,
                'reward_xp'          => 150,
                'reward_gold'        => 90,
                'reward_loot_rarity' => 'peu_commun',
                'is_repeatable'      => true,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $prairieId,
                'type'               => 'zone',
                'title'              => 'L\'Épouvantail Existentiel',
                'description'        => 'Un épouvantail animé se pose des questions métaphysiques et effraie les villageois. Un sage demande votre aide. Le Narrateur précise que ce sage n\'est pas vraiment sage — c\'est juste un vieux qui ne sait pas garder ses bocaux fermés.',
                'steps_count'        => 5,
                'order_index'        => 4,
                'reward_xp'          => 200,
                'reward_gold'        => 120,
                'reward_loot_rarity' => 'peu_commun',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $prairieId,
                'type'               => 'zone',
                'title'              => 'Le Fermier Possédé — Exorcisme en Option',
                'description'        => 'Le fermier du coin agit bizarrement. Ses yeux sont noirs, ses paroles sont étranges, et il essaie de fourcher les gens. Un prêtre local tente un exorcisme. Il a besoin d\'assistants. Vous voilà.',
                'steps_count'        => 5,
                'order_index'        => 5,
                'reward_xp'          => 300,
                'reward_gold'        => 180,
                'reward_loot_rarity' => 'rare',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],

            // ── Zone 2 — Forêt des Elfes Vexés ──
            [
                'zone_id'            => $foretId,
                'type'               => 'zone',
                'title'              => 'L\'Elfe Qui Ne Voulait Pas Aider',
                'description'        => 'Elara l\'Elfe Vexée a perdu son arc. Elle vous demande de le retrouver tout en faisant clairement comprendre qu\'elle ne vous fait pas confiance. Le Narrateur lui donne raison.',
                'steps_count'        => 4,
                'order_index'        => 1,
                'reward_xp'          => 250,
                'reward_gold'        => 150,
                'reward_loot_rarity' => 'commun',
                'is_repeatable'      => true,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $foretId,
                'type'               => 'zone',
                'title'              => 'La Fée et les Mauvaises Nouvelles',
                'description'        => 'Une fée malveillante répand des rumeurs sur votre équipe. Les elfes vous regardent encore plus mal qu\'avant, ce qui semblait impossible. Il faut résoudre ce problème diplomatiquement... ou autrement.',
                'steps_count'        => 5,
                'order_index'        => 2,
                'reward_xp'          => 400,
                'reward_gold'        => 240,
                'reward_loot_rarity' => 'peu_commun',
                'is_repeatable'      => true,
                'is_ai_generated'    => false,
            ],
        ];

        foreach ($quests as $quest) {
            $quest['created_at'] = now();
            $quest['updated_at'] = now();
            $id = DB::table('quests')->insertGetId($quest);
            $this->seedSteps($id, $quest['title'], $quest['steps_count']);
        }
    }

    private function seedSteps(int $questId, string $title, int $stepCount): void
    {
        $stepsData = $this->getStepsData($title);
        if (empty($stepsData)) {
            $stepsData = $this->generateGenericSteps($stepCount);
        }

        foreach ($stepsData as $index => $step) {
            DB::table('quest_steps')->insert([
                'quest_id'   => $questId,
                'step_index' => $index + 1,
                'content'    => json_encode($step),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getStepsData(string $title): array
    {
        $allSteps = [
            'Le Rat et le Fromage' => [
                [
                    'narration' => 'Le fermier Gustave vous tend une carte dessinée à la main. Le rat habite "quelque part dans la prairie". La précision est remarquable.',
                    'narrator_comment' => 'Un rat a volé un fromage. Votre toute première mission. Le Narrateur s\'attendait à mieux. Lui aussi.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Chercher méthodiquement (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 25], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous trouvez facilement le terrier. Le rat n\'a pas essayé de se cacher.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D10', 'target' => 'party']], 'narration' => 'Vous cherchez pendant deux heures. La fatigue commence à se faire sentir.']],
                        ['id' => 'B', 'text' => 'Suivre l\'odeur du fromage', 'test' => null, 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Le fromage est du bleu. L\'odeur vous guide sans effort.'], 'failure' => null],
                    ],
                ],
                [
                    'narration' => 'Vous trouvez le terrier. Le rat Peureux vous regarde avec méfiance et serre son fromage contre lui. Il est plus courageux que vous ne l\'attendiez.',
                    'narrator_comment' => 'Un rat tient tête à des aventuriers. Voilà l\'épopée dont les bardes chanteront. Non.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Tendre la main pacifiquement (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 20], 'success' => ['next_step' => 3, 'effects' => [['type' => 'buff', 'id' => 'B06', 'target' => 'party']], 'narration' => 'Le rat vous tend le fromage. Une larme coule sur son museau. Non.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Le rat vous mord le doigt. Pas profondément. Humiliant quand même.']],
                        ['id' => 'B', 'text' => 'Attaquer le rat', 'test' => ['type' => 'combat', 'enemy' => 'rat_peureux'], 'success' => ['next_step' => 3, 'effects' => [['type' => 'buff', 'id' => 'B02', 'target' => 'attacker']], 'narration' => 'Le rat est vaincu. Le fromage est intact. Victoire.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Le rat s\'est enfui avec le fromage. Vous avez perdu contre un rat.']],
                    ],
                ],
                [
                    'narration' => 'Vous revenez voir Gustave. Selon ce qui s\'est passé, il réagit différemment. Il est content. Ou moins content.',
                    'narrator_comment' => 'La quête est terminée. Le Narrateur préfère ne pas commenter.',
                    'is_final' => true,
                    'choices' => [
                        ['id' => 'A', 'text' => 'Remettre le fromage fièrement', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'prairie', 'amount' => 10]], 'narration' => 'Gustave est ravi. Il vous offre aussi un petit quelque chose.'], 'failure' => null],
                    ],
                ],
            ],

            'Le Gobelin Chapardeur et la Caisse Enregistreuse' => [
                [
                    'narration' => 'La marchande Hortense est effondrée. La caisse contenait toutes ses économies de la semaine. Le gobelin est parti vers le nord... enfin elle croit. Elle n\'avait pas ses lunettes.',
                    'narrator_comment' => 'Une marchande sans lunettes, un gobelin chapardeur, une caisse disparue. Situation classique. Le Narrateur s\'ennuie déjà.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Interroger les témoins (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Un enfant vous indique la bonne direction. Il voulait juste participer.'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Les témoins se contredisent. Vous partez vers le nord au hasard.']],
                        ['id' => 'B', 'text' => 'Suivre les traces de pas', 'test' => ['stat' => 'int', 'difficulty' => 25], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Les traces vous mènent à un buisson suspects.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D08', 'target' => 'leader']], 'narration' => 'Vous suivez les mauvaises traces. Vous finissez par revenir. Fatigués.']],
                    ],
                ],
                [
                    'narration' => 'Vous trouvez le gobelin en train de compter l\'or derrière un arbre. Il vous voit. Il sourit. Ce n\'est pas rassurant.',
                    'narrator_comment' => 'Un gobelin qui sourit à des aventuriers. Soit il est stupide, soit il a un plan. Ces deux options ne s\'excluent pas.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Négocier (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 40, 'trait_bonus' => ['Mythomane' => 15]], 'success' => ['next_step' => 3, 'effects' => [['type' => 'gold', 'amount' => 20]], 'narration' => 'Le gobelin accepte de rendre la caisse contre 20 pièces de votre poche. Deal discutable.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Le gobelin refuse et siffle. Des amis arrivent.']],
                        ['id' => 'B', 'text' => 'Attaquer directement', 'test' => ['type' => 'combat', 'enemy' => 'gobelin_chapardeur'], 'success' => ['next_step' => 3, 'effects' => [['type' => 'loot', 'rarity_min' => 'commun']], 'narration' => 'Le gobelin est vaincu. La caisse est récupérée.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'Le gobelin vous a sérieusement résisté avant de fuir.']],
                    ],
                ],
                [
                    'narration' => 'Vous avez la caisse. Ou ce qu\'il en reste. Il faut la ramener à Hortense.',
                    'narrator_comment' => 'Presque fini. Le Narrateur retient son souffle. Non il ne le retient pas.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Ramener directement', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Vous retournez au marché. Hortense attend avec anxiété.'], 'failure' => null],
                        ['id' => 'B', 'text' => 'Vérifier s\'il ne reste pas d\'autres gobelins', 'test' => ['stat' => 'vit', 'difficulty' => 30], 'success' => ['next_step' => 4, 'effects' => [['type' => 'gold', 'amount' => 30]], 'narration' => 'Bien vu — un gobelin planqué avait encore une bourse. +30 or.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'Rien à trouver. Vous avez perdu du temps.']],
                    ],
                ],
                [
                    'narration' => 'Hortense vous accueille les larmes aux yeux. Elle compte les pièces. Vous la regardez compter.',
                    'narrator_comment' => 'La marchande compte ses pièces. Vous regardez. C\'est moins héroïque qu\'annoncé.',
                    'is_final' => true,
                    'choices' => [
                        ['id' => 'A', 'text' => 'Accepter la récompense gracieusement', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'prairie', 'amount' => 10]], 'narration' => 'Hortense vous remercie. Elle vous donnera une réduction la prochaine fois. (Elle ne le fera probablement pas.)'], 'failure' => null],
                    ],
                ],
            ],

            'L\'Épouvantail Existentiel' => [
                [
                    'narration' => 'L\'épouvantail Anatole a commencé à se poser des questions sur le sens de son existence. Il terrorise maintenant les villageois en récitant des tirades philosophiques. Le sage Balmotius vous explique la situation sur un ton grave.',
                    'narrator_comment' => 'Un épouvantail existentiel. Le Narrateur aurait aimé avoir cette idée lui-même.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Engager la conversation philosophique (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 35], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Anatole est surpris que quelqu\'un lui réponde. Il se calme légèrement.'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous répondez quelque chose de stupide. Anatole est encore plus perturbé.']],
                        ['id' => 'B', 'text' => 'L\'ignorer et avancer', 'test' => null, 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'L\'épouvantail vous suit en récitant Descartes. C\'est pire.'], 'failure' => null],
                    ],
                ],
                [
                    'narration' => 'Anatole vous pose LA question : "Pourquoi combattons-nous ?" Votre réponse va déterminer son état d\'esprit pour la suite.',
                    'narrator_comment' => 'La question à 50 pièces d\'or. Enfin, la question à 120 pièces d\'or, vu les récompenses de cette quête.',
                    'choices' => [
                        ['id' => 'A', 'text' => '"Pour protéger les innocents !" (Voie Héroïque)', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Anatole semble apaisé. Il hoche la tête de paille.'], 'failure' => null],
                        ['id' => 'B', 'text' => '"Pour l\'or !" (Voie Maligne)', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Anatole apprécie l\'honnêteté. Il vous respecte pour ça.'], 'failure' => null],
                        ['id' => 'C', 'text' => '"Je ne sais pas, j\'étais là par hasard." (Voie Comique)', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [['type' => 'buff', 'id' => 'B06', 'target' => 'party']], 'narration' => 'Anatole éclate de rire — enfin, il vibre bizarrement. C\'est peut-être du rire.'], 'failure' => null],
                    ],
                ],
                [
                    'narration' => 'Anatole accepte de retourner dans son champ, mais pose une condition : il veut un livre. Il a entendu parler des livres.',
                    'narrator_comment' => 'L\'épouvantail veut lire. Le Narrateur estime que c\'est une évolution inquiétante.',
                    'choices' => [
                        ['id' => 'A', 'text' => 'Demander au sage s\'il a un livre (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 5, 'effects' => [], 'narration' => 'Balmotius soupire et vous tend un vieux grimoire. "Rendez-le."'], 'failure' => ['next_step' => 5, 'effects' => [['type' => 'gold', 'amount' => -50]], 'narration' => 'Le sage veut 50 or pour son livre. Transaction validée.']],
                        ['id' => 'B', 'text' => 'Inventer un résumé de livre (test INT trait Mythomane)', 'test' => ['stat' => 'int', 'difficulty' => 40, 'trait_bonus' => ['Mythomane' => 25]], 'success' => ['next_step' => 5, 'effects' => [['type' => 'buff', 'id' => 'B03', 'target' => 'party']], 'narration' => 'Votre résumé improvisé d\'un livre inexistant satisfait pleinement Anatole.'], 'failure' => ['next_step' => 5, 'effects' => [['type' => 'debuff', 'id' => 'D05', 'target' => 'leader']], 'narration' => 'Anatole remarque l\'incohérence et vous juge publiquement.']],
                    ],
                ],
                [
                    'narration' => 'Anatole retourne dans son champ. Avant de partir, il vous dit quelque chose. Vous ne comprenez pas tout, mais le ton était aimable.',
                    'narrator_comment' => 'L\'épouvantail s\'en va. Le village est sauvé. La philosophie, comme d\'habitude, a failli tout gâcher.',
                    'is_final' => true,
                    'choices' => [
                        ['id' => 'A', 'text' => 'Le saluer et partir', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'prairie', 'amount' => 10]], 'narration' => 'Les villageois vous acclament. Balmotius vous remet la récompense avec un soupir de soulagement.'], 'failure' => null],
                    ],
                ],
            ],
        ];

        return $allSteps[$title] ?? [];
    }

    private function generateGenericSteps(int $count): array
    {
        $steps = [];
        for ($i = 1; $i <= $count; $i++) {
            $isFinal = ($i === $count);
            $steps[] = [
                'narration'        => 'La situation se présente. Vous devez faire un choix.',
                'narrator_comment' => 'Le Narrateur observe. Le Narrateur juge.',
                'is_final'         => $isFinal,
                'choices'          => [
                    [
                        'id'      => 'A',
                        'text'    => $isFinal ? 'Conclure la quête' : 'Agir courageusement (test ATQ)',
                        'test'    => $isFinal ? null : ['stat' => 'atq', 'difficulty' => 30],
                        'success' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => $isFinal ? [['type' => 'reputation', 'zone' => 'prairie', 'amount' => 10]] : [['type' => 'buff', 'id' => 'B02', 'target' => 'leader']],
                            'narration' => $isFinal ? 'Quête accomplie.' : 'L\'action porte ses fruits.',
                        ],
                        'failure' => $isFinal ? null : [
                            'next_step' => $i + 1,
                            'effects'   => [],
                            'narration' => 'Ça n\'a pas marché. Vous continuez quand même.',
                        ],
                    ],
                    [
                        'id'      => 'B',
                        'text'    => $isFinal ? 'Partir discrètement avec la récompense' : 'Agir prudemment (test VIT)',
                        'test'    => $isFinal ? null : ['stat' => 'vit', 'difficulty' => 25],
                        'success' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => $isFinal ? [['type' => 'gold', 'amount' => 20]] : [],
                            'narration' => $isFinal ? 'Vous partez vite. La récompense est dans votre poche.' : 'La prudence paye.',
                        ],
                        'failure' => $isFinal ? null : [
                            'next_step' => $i + 1,
                            'effects'   => [],
                            'narration' => 'La prudence n\'a pas suffi.',
                        ],
                    ],
                ],
            ];
        }
        return $steps;
    }
}
