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
        $minesId   = DB::table('zones')->where('slug', 'mines_nain')->value('id');
        $maraisId  = DB::table('zones')->where('slug', 'marais_bureaucratie')->value('id');

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

            // ── Quêtes WTF — Absurdes, épiques, imprévisibles ──
            [
                'zone_id'            => $prairieId,
                'type'               => 'wtf',
                'title'              => 'Le Narrateur a Besoin d\'un Café',
                'description'        => 'Une voix venue de nulle part vous demande d\'apporter un café au Narrateur. Vous ne voyez pas de Narrateur. La voix insiste. Le café est dans la maison du fermier à l\'autre bout de la carte. Il est peut-être froid.',
                'steps_count'        => 7,
                'order_index'        => 10,
                'reward_xp'          => 2000,
                'reward_gold'        => 1500,
                'reward_loot_rarity' => 'epique',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $foretId,
                'type'               => 'wtf',
                'title'              => 'La Fée Qui Voulait Devenir Comptable',
                'description'        => 'Une fée de la forêt a décidé qu\'elle voulait changer de vie. Elle veut devenir comptable. Elle a besoin d\'un certificat. Le certificat est gardé par un gobelin qui l\'a mangé. Vous devez récupérer des morceaux. La fée est... enthousiaste.',
                'steps_count'        => 8,
                'order_index'        => 11,
                'reward_xp'          => 3000,
                'reward_gold'        => 2200,
                'reward_loot_rarity' => 'epique',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $minesId,
                'type'               => 'wtf',
                'title'              => 'Thorin a Perdu ses Clés',
                'description'        => 'Thorin le Nain Ivre a perdu les clés de sa cave à bière. Sans elles, il ne peut pas brasser sa bière secrète. Sans la bière secrète, sa mine s\'effondre. Enfin c\'est ce qu\'il dit. Le Narrateur a des doutes. Vous aussi probablement.',
                'steps_count'        => 9,
                'order_index'        => 12,
                'reward_xp'          => 4500,
                'reward_gold'        => 3000,
                'reward_loot_rarity' => 'legendaire',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $maraisId,
                'type'               => 'wtf',
                'title'              => 'Le Formulaire W-99-Bis',
                'description'        => 'Un fantôme bureaucrate vous remet le Formulaire W-99-Bis en 47 exemplaires. Ce formulaire, s\'il est rempli correctement, permettrait théoriquement de rembourser tous vos impôts. Théoriquement. Le formulaire est en latin. Le Narrateur ne parle pas latin non plus.',
                'steps_count'        => 10,
                'order_index'        => 13,
                'reward_xp'          => 6000,
                'reward_gold'        => 4500,
                'reward_loot_rarity' => 'legendaire',
                'is_repeatable'      => false,
                'is_ai_generated'    => false,
            ],
            [
                'zone_id'            => $prairieId,
                'type'               => 'wtf',
                'title'              => 'Gérard a Commandé Trop de Ferraille',
                'description'        => 'Gérard a commandé de la ferraille par erreur. 47 tonnes. Elle bloque la forge. Et la route. Et apparemment le canal. Les ferrailleuses de la zone ont envoyé une facture. Gérard pleure. Aidez-le ou pas — de toute façon le Narrateur s\'en amuse.',
                'steps_count'        => 7,
                'order_index'        => 14,
                'reward_xp'          => 2500,
                'reward_gold'        => 2000,
                'reward_loot_rarity' => 'epique',
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

        // WTF quests use generic steps with WTF-flavored narration
        $wtfTitles = [
            'Le Narrateur a Besoin d\'un Café',
            'La Fée Qui Voulait Devenir Comptable',
            'Thorin a Perdu ses Clés',
            'Le Formulaire W-99-Bis',
            'Gérard a Commandé Trop de Ferraille',
        ];

        if (in_array($title, $wtfTitles, true)) {
            return $this->generateWtfSteps($title);
        }

        return $allSteps[$title] ?? [];
    }

    private function generateWtfSteps(string $title): array
    {
        $wtfNarrations = [
            'Le Narrateur a Besoin d\'un Café' => [
                ['narration' => '"Le café. Maintenant. S\'il vous plaît." La voix est étrangement proche de votre oreille gauche.', 'narrator_comment' => 'Le Narrateur reconnaît qu\'il parle directement aux personnages. C\'est contre les règles. Il s\'en moque.', 'choices' => [['id' => 'A', 'text' => 'Demander "Mais vous êtes qui ?"', 'test' => ['stat' => 'int', 'difficulty' => 99], 'success' => ['next_step' => 2, 'effects' => [['type' => 'buff', 'id' => 'B07', 'target' => 'party']], 'narration' => 'Une révélation cosmique vous traverse. Puis la voix dit "café d\'abord".'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous ne comprenez pas. Personne ne comprend. C\'est normal.']], ['id' => 'B', 'text' => 'Aller chercher le café sans poser de questions', 'test' => null, 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Sage décision. La voix approuve.'], 'failure' => null]]],
                ['narration' => 'Le fermier a du café... mais il est froid. Il faudrait un feu. Il y a un dragon par là.', 'narrator_comment' => 'Un dragon comme source de chaleur pour un café. Qui a approuvé cette quête ?', 'choices' => [['id' => 'A', 'text' => 'Négocier avec le dragon (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 60], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Le dragon accepte de chauffer votre café. Il le trouve trop léger.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'Le dragon a réchauffé le café et a failli réchauffer votre équipe aussi.']], ['id' => 'B', 'text' => 'Attaquer le dragon pour son feu', 'test' => ['type' => 'combat', 'enemy' => 'dragon_mines_retraite'], 'success' => ['next_step' => 3, 'effects' => [['type' => 'loot', 'rarity_min' => 'rare']], 'narration' => 'Victoire héroïque. Le café est chaud. Le dragon est un peu froissé.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D07', 'target' => 'party']], 'narration' => 'Le dragon vous a un peu brûlé. Le café aussi est brûlant. Tout est brûlant maintenant.']]]],
                ['narration' => 'Le café est chaud. Il y a un problème : pas de tasse. La voix soupire.', 'narrator_comment' => 'Évidemment. Il fallait prévoir ça.', 'choices' => [['id' => 'A', 'text' => 'Chercher une tasse dans la zone (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 35], 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Vous trouvez une tasse dans les décombres d\'une maison. Elle est propre. Presque.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'Vous ne trouvez qu\'une corne de taureau. Ce sera une tasse maintenant.']], ['id' => 'B', 'text' => 'Improviser avec un casque', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Un casque de gobelin. C\'est pas parfait mais ça contient du liquide.'], 'failure' => null]]],
                ['narration' => 'Tasse trouvée. Café chaud. Il reste à traverser la prairie sans le renverser.', 'narrator_comment' => 'La plus grande épreuve de cette quête. Le Narrateur tient enfin son café.', 'choices' => [['id' => 'A', 'text' => 'Marcher très doucement (test VIT inversé)', 'test' => ['stat' => 'vit', 'difficulty' => 20], 'success' => ['next_step' => 5, 'effects' => [], 'narration' => 'Vous vous déplacez lentement. Très lentement. Mais le café est intact.'], 'failure' => ['next_step' => 5, 'effects' => [['type' => 'gold', 'amount' => -100]], 'narration' => 'Vous avez renversé un peu. Le Narrateur est mécontent. 100 or d\'amende pour distraction.']], ['id' => 'B', 'text' => 'Courir très vite avant que ça refroidisse', 'test' => ['stat' => 'vit', 'difficulty' => 70], 'success' => ['next_step' => 5, 'effects' => [['type' => 'buff', 'id' => 'B04', 'target' => 'party']], 'narration' => 'Speed run ! Le café est chaud et intact. La voix est surprise.'], 'failure' => ['next_step' => 5, 'effects' => [['type' => 'gold', 'amount' => -200]], 'narration' => 'Vous avez tout renversé. Le Narrateur est très mécontent.']]]],
                ['narration' => 'La voix reçoit le café. Silence. Longue gorgée.', 'narrator_comment' => '...', 'choices' => [['id' => 'A', 'text' => 'Attendre la récompense', 'test' => null, 'success' => ['next_step' => 6, 'effects' => [], 'narration' => '"Il est froid." La voix est décevante. Vous avez pourtant essayé.'], 'failure' => null]]],
                ['narration' => '"Bon. Vous avez quand même essayé. Voici votre récompense. Ne parlez de ça à personne."', 'narrator_comment' => 'Personne ne croira votre histoire de toute façon. C\'était une bonne quête. Le Narrateur le dit à contrecœur.', 'is_final' => true, 'choices' => [['id' => 'A', 'text' => 'Promettre de garder le secret', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'buff', 'id' => 'B07', 'target' => 'party'], ['type' => 'loot', 'rarity_min' => 'epique']], 'narration' => 'Une récompense cosmique. Et un secret gardé. Le Narrateur reprend son café.'], 'failure' => null], ['id' => 'B', 'text' => 'Raconter à tout le monde', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'loot', 'rarity_min' => 'epique'], ['type' => 'debuff', 'id' => 'D05', 'target' => 'party']], 'narration' => 'Personne ne vous croit. Mais la récompense est là quand même. Le Narrateur est légèrement offensé.'], 'failure' => null]]],
                ['narration' => 'Le Narrateur a terminé son café.', 'narrator_comment' => 'FIN DE LA QUÊTE WTF. Le Narrateur reprend son travail normal, mécontent de l\'interruption mais légèrement mieux disposé.', 'is_final' => true, 'choices' => [['id' => 'A', 'text' => 'Rentrer à la taverne', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'prairie', 'amount' => 25]], 'narration' => 'Vous avez aidé le Narrateur. Votre réputation en prairie en profite bizarrement.'], 'failure' => null]]],
            ],
        ];

        if (isset($wtfNarrations[$title])) {
            return $wtfNarrations[$title];
        }

        // Fallback: generate generic steps with WTF flavor for other WTF quests
        $steps = [];
        $count = match ($title) {
            'La Fée Qui Voulait Devenir Comptable' => 8,
            'Thorin a Perdu ses Clés'              => 9,
            'Le Formulaire W-99-Bis'               => 10,
            default                                => 7,
        };

        for ($i = 1; $i <= $count; $i++) {
            $isFinal = ($i === $count);
            $steps[] = [
                'narration'        => 'La situation devient de plus en plus absurde. C\'est une quête WTF. C\'est prévu.',
                'narrator_comment' => 'Le Narrateur documente l\'absurdité croissante avec une satisfaction tranquille.',
                'is_final'         => $isFinal,
                'choices'          => [
                    [
                        'id'      => 'A',
                        'text'    => $isFinal ? 'Résoudre l\'absurdité par la force (test ATQ)' : 'Affronter le problème (test ATQ)',
                        'test'    => ['stat' => 'atq', 'difficulty' => min(30 + ($i * 5), 75)],
                        'success' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => $isFinal
                                ? [['type' => 'loot', 'rarity_min' => 'epique'], ['type' => 'buff', 'id' => 'B07', 'target' => 'party']]
                                : [['type' => 'buff', 'id' => 'B02', 'target' => 'attacker']],
                            'narration' => $isFinal ? 'Contre toute logique, ça a marché. La quête WTF est terminée.' : 'L\'absurdité recule temporairement.',
                        ],
                        'failure' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => $isFinal
                                ? [['type' => 'loot', 'rarity_min' => 'rare']]
                                : [['type' => 'debuff', 'id' => 'D10', 'target' => 'leader']],
                            'narration' => $isFinal ? 'L\'échec final. Vous avez quand même une récompense. La quête était WTF.' : 'L\'absurdité riposte.',
                        ],
                    ],
                    [
                        'id'      => 'B',
                        'text'    => $isFinal ? 'Résoudre par la ruse (test INT)' : 'Analyser la situation (test INT)',
                        'test'    => ['stat' => 'int', 'difficulty' => min(25 + ($i * 5), 70)],
                        'success' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => $isFinal
                                ? [['type' => 'loot', 'rarity_min' => 'epique'], ['type' => 'gold', 'amount' => 500]]
                                : [['type' => 'buff', 'id' => 'B03', 'target' => 'leader']],
                            'narration' => $isFinal ? 'Votre intelligence transcende l\'absurdité. Impressionnant.' : 'Votre analyse révèle des patterns... absurdes.',
                        ],
                        'failure' => [
                            'next_step' => $isFinal ? null : $i + 1,
                            'effects'   => [],
                            'narration' => $isFinal ? 'Même l\'analyse échoue face à l\'absurde. C\'est beau.' : 'L\'absurdité défie l\'analyse.',
                        ],
                    ],
                ],
            ];
        }
        return $steps;
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
