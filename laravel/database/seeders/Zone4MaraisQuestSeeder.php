<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Zone4MaraisQuestSeeder extends Seeder
{
    public function run(): void
    {
        $zoneId = DB::table('zones')->where('slug', 'marais_bureaucratie')->value('id');
        if (!$zoneId) {
            $this->command->warn('Zone marais_bureaucratie introuvable — seeder ignoré.');
            return;
        }

        $quests = [
            [
                'title'              => 'Le Cachet Manquant',
                'description'        => 'Pour traverser le marais officiellement, il faut un cachet de la Direction Régionale des Zones Humides. Le bureau est ouvert le deuxième mardi de chaque mois, de 9h à 9h15, sauf jours fériés, jours de pluie, et périodes de marée haute administrative.',
                'zone_id'            => $zoneId,
                'steps_count'        => 4,
                'order_index'        => 1,
                'reward_xp'          => 650,
                'reward_gold'        => 380,
                'reward_loot_rarity' => 'peu_commun',
                'is_repeatable'      => true,
                'steps' => [
                    [
                        'narration'        => 'Le fonctionnaire de l\'accueil vous regarde avec la compassion d\'un mur humide. "Le formulaire 27-B est nécessaire pour obtenir le formulaire 12-C, lequel permet de demander le formulaire 27-B." Il dit ça sans sourciller.',
                        'narrator_comment' => 'La boucle administrative parfaite. Le Narrateur admire l\'efficacité avec laquelle ce système ne fonctionne pas.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Expliquer l\'absurdité de la situation (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 35], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous trouvez une faille logique dans le système. Le fonctionnaire est momentanément déstabilisé et vous donne un formulaire préliminaire.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D01', 'target' => 'leader']], 'narration' => 'Le fonctionnaire sort un classeur de 400 pages. "Vous trouverez la réponse à votre question en annexe 7." L\'annexe 7 renvoie à l\'annexe 12.']],
                            ['id' => 'B', 'text' => 'Charmer le fonctionnaire (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Le fonctionnaire se détend légèrement. "Entre nous, le formulaire 27-B est disponible au sous-sol. Ne le dites à personne."'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Le fonctionnaire vous regarde avec plus de méfiance. "Avez-vous rempli le formulaire de demande de renseignements ?"']],
                        ],
                    ],
                    [
                        'narration'        => 'Le sous-sol contient 40 ans d\'archives dans un ordre connu de personne. Quelque part là-dedans se trouve le formulaire 27-B, probablement.',
                        'narrator_comment' => 'Quarante années de paperasse non classée. Le Narrateur pense à ses propres archives et préfère ne pas y penser.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Fouiller méthodiquement (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 28], 'success' => ['next_step' => 3, 'effects' => [['type' => 'buff', 'id' => 'B01', 'target' => 'party']], 'narration' => 'Deux heures plus tard, vous trouvez le formulaire 27-B derrière une archive de 1987 sur la réglementation des grenouilles.'], 'failure' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous trouvez le formulaire 27-B après quatre heures. Il était dans le carton "URGENT 1994".']],
                            ['id' => 'B', 'text' => 'Demander à la grenouille qui surveille les archives (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 22], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'La grenouille archiviste connaît l\'emplacement exact de tout. Elle attendait juste qu\'on lui demande poliment.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D08', 'target' => 'leader']], 'narration' => 'La grenouille est syndiquée et exige une pause déjeuner avant d\'aider. Deux heures de plus.']],
                        ],
                    ],
                    [
                        'narration'        => 'Le formulaire 27-B est rempli. Il doit maintenant être contresigné par le Chef de Service, qui est en réunion. La réunion porte sur la réforme du processus de contresignature.',
                        'narrator_comment' => 'La réunion pour réformer les réunions. Le Narrateur n\'est pas surpris. Il est lassé, ce qui est différent.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Attendre patiemment (pas de test)', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Trois heures plus tard, le Chef de Service sort. Il contresigne sans lire. C\'est son mode de fonctionnement habituel.'], 'failure' => null],
                            ['id' => 'B', 'text' => 'Pénétrer dans la réunion pour accélérer les choses (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 20], 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Vous entrez. La réunion s\'arrête. Le Chef de Service, soulagé d\'avoir une excuse, contresigne immédiatement.'], 'failure' => ['next_step' => 4, 'effects' => [['type' => 'debuff', 'id' => 'D10', 'target' => 'party']], 'narration' => 'Vous entrez. On vous fait asseoir. Vous êtes maintenant dans la réunion. La réunion porte sur vous.']],
                        ],
                    ],
                    [
                        'narration'        => 'Le formulaire est contresigné. Le fonctionnaire de l\'accueil l\'examine longuement. "Il manque le cachet humide." Il sort un tampon. Il tamponne. "Bienvenue dans le marais." Ça a pris toute la journée.',
                        'narrator_comment' => 'La bureaucratie a triomphé, comme toujours. Vos héros ont un cachet. Le cachet est valable 30 jours. Le Narrateur ne dira pas combien il reste de jours.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Remercier chaleureusement le fonctionnaire', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'marais_bureaucratie', 'amount' => 15]], 'narration' => 'Le fonctionnaire hoche la tête. C\'est sa première interaction positive depuis 2019. Il le notera dans son rapport mensuel.'], 'failure' => null],
                        ],
                    ],
                ],
            ],
            [
                'title'              => 'La Grenouille Syndiquée',
                'description'        => 'Grimaud, représentant syndical des grenouilles du marais, bloque le passage principal pour protester contre les "conditions de travail dégradées par le passage intempestif d\'aventuriers". Ses revendications sont détaillées dans un document de 47 pages.',
                'zone_id'            => $zoneId,
                'steps_count'        => 4,
                'order_index'        => 2,
                'reward_xp'          => 800,
                'reward_gold'        => 480,
                'reward_loot_rarity' => 'rare',
                'is_repeatable'      => true,
                'steps' => [
                    [
                        'narration'        => 'Grimaud la grenouille tient une pancarte : "NON AUX BOTTES DANS LE MARAIS". Derrière lui, une centaine de grenouilles regardent en silence. Certaines tiennent des petites pancartes. Celle du fond dit "Solidarité amphibienne".',
                        'narrator_comment' => 'Le Narrateur ne pensait pas voir ça aujourd\'hui. Le Narrateur avait tort. Le Narrateur prend des notes.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Lire les 47 pages de revendications (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 30], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Vous lisez tout. La page 23 contient une proposition raisonnable. Grimaud est impressionné que quelqu\'un ait vraiment lu.'], 'failure' => ['next_step' => 2, 'effects' => [['type' => 'debuff', 'id' => 'D01', 'target' => 'leader']], 'narration' => 'Vous vous perdez en page 12. La mise en forme est déplorable. Grimaud soupire.']],
                            ['id' => 'B', 'text' => 'Entamer une négociation directe (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 35], 'success' => ['next_step' => 2, 'effects' => [['type' => 'buff', 'id' => 'B02', 'target' => 'leader']], 'narration' => 'Grimaud accepte de négocier. "Vous êtes le premier aventurier à nous parler sans nous enjamber."'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Grimaud convoque une assemblée générale pour voter sur l\'opportunité d\'entamer des négociations. Ça prend deux heures.']],
                        ],
                    ],
                    [
                        'narration'        => 'La revendication principale : une indemnité de dérangement pour chaque aventurier passant dans le marais. Grimaud propose 50 or par passage. C\'est négociable, mais il faut le convaincre.',
                        'narrator_comment' => 'Cinquante or par passage. Multipliez par le nombre d\'aventuriers. Le Narrateur a fait le calcul. Grimaud est plus malin qu\'il en a l\'air.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Proposer une alternative (nettoyage du marais) (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 32], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous proposez que les aventuriers nettoient les déchets laissés par leurs prédécesseurs. Grimaud consulte ses collègues. Accord provisoire.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'Votre contre-proposition est jugée "insuffisante". Grimaud sort une deuxième pancarte : "Pas d\'accord".']],
                            ['id' => 'B', 'text' => 'Payer les 50 or (pas de test, coût en or)', 'test' => null, 'success' => ['next_step' => 3, 'effects' => [['type' => 'gold', 'amount' => -50]], 'narration' => 'Vous payez. Grimaud hoche la tête. "Transaction validée." Il sort un reçu. Il avait un reçu prêt. Il attendait ça.'], 'failure' => null],
                        ],
                    ],
                    [
                        'narration'        => 'Accord trouvé sur le principe. Il faut maintenant rédiger un protocole d\'accord officiel. Grimaud sort un stylo-plume et du papier parchemin de qualité.',
                        'narrator_comment' => 'Une grenouille avec un stylo-plume. Le Narrateur a décidément vu des choses aujourd\'hui.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Rédiger un accord clair et précis (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 28], 'success' => ['next_step' => 4, 'effects' => [['type' => 'buff', 'id' => 'B01', 'target' => 'party']], 'narration' => 'L\'accord est bien rédigé. Grimaud le lit, fait quelques corrections mineures et signe avec fierté.'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'L\'accord est vague sur plusieurs points. Grimaud ajoute 8 amendements. Vous signez quand même.']],
                            ['id' => 'B', 'text' => 'Laisser Grimaud rédiger (pas de test)', 'test' => null, 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'Grimaud rédige un document de 12 pages pour un accord de 3 lignes. Il est très minutieux.'], 'failure' => null],
                        ],
                    ],
                    [
                        'narration'        => 'L\'accord est signé des deux côtés. Grimaud range la pancarte. Les grenouilles se dispersent. L\'une d\'elles remet une fleur à vos héros. C\'est une fleur de marais. Elle sent la boue. C\'est affectueux.',
                        'narrator_comment' => 'La grève des grenouilles est terminée. L\'histoire retiendra que vos héros ont négocié avec des amphibiens syndiqués. Le Narrateur l\'a déjà noté dans ses mémoires.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Serrer la main (la patte) de Grimaud', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'marais_bureaucratie', 'amount' => 20]], 'narration' => 'Grimaud serre la main avec une poignée ferme et humide. "Revenez quand vous voulez. En respectant le protocole."'], 'failure' => null],
                        ],
                    ],
                ],
            ],
            [
                'title'              => 'Le Fonctionnaire Fantôme',
                'description'        => 'Le bureau 7B du Secrétariat des Eaux Troubles est hanté par le fantôme de l\'inspecteur Moreau, décédé en 1987 pendant la Grande Réforme Administrative. Il refuse de partir avant que son rapport final soit classé. Le rapport fait 800 pages. Personne ne sait où le classer.',
                'zone_id'            => $zoneId,
                'steps_count'        => 5,
                'order_index'        => 3,
                'reward_xp'          => 950,
                'reward_gold'        => 560,
                'reward_loot_rarity' => 'rare',
                'is_repeatable'      => false,
                'steps' => [
                    [
                        'narration'        => 'L\'inspecteur Moreau flotte au-dessus de son bureau, entouré de piles de paperasse fantomatique. Il lève les yeux. "Ah. Des aventuriers. Avez-vous les autorisations adéquates pour visiter ce bureau ?" Sa plume grince sur du parchemin invisible.',
                        'narrator_comment' => 'Un fantôme bureaucrate. La mort n\'a pas simplifié ses procédures. Si quelque chose, elle les a complexifiées.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Expliquer qu\'on est là pour l\'aider (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 30], 'success' => ['next_step' => 2, 'effects' => [], 'narration' => 'Moreau baisse sa plume. "Personne n\'est venu m\'aider depuis... longtemps. Très bien. Écoutez."'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Moreau vous tend un formulaire fantomatique. "Remplissez d\'abord le formulaire de visite." Le formulaire est froid au toucher.']],
                            ['id' => 'B', 'text' => 'Montrer de l\'empathie pour sa situation (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 25], 'success' => ['next_step' => 2, 'effects' => [['type' => 'buff', 'id' => 'B02', 'target' => 'party']], 'narration' => '"Vous comprenez." Moreau semble moins spectral. "Trente-neuf ans à attendre que quelqu\'un classe ce rapport."'], 'failure' => ['next_step' => 2, 'effects' => [], 'narration' => 'Moreau vous regarde. "L\'empathie ne remplace pas les procédures." Il sort un nouveau formulaire.']],
                        ],
                    ],
                    [
                        'narration'        => 'Le rapport de Moreau est intitulé "Réforme Systémique des Processus de Validation Inter-Départementaux dans les Zones Humides : Tome III". Les tomes I et II ont disparu. Sans eux, le rapport ne peut pas être classé.',
                        'narrator_comment' => 'Le tome III sans les tomes I et II. Un classique. Le Narrateur ne peut pas aider. Le Narrateur a quand même des archives impeccables, lui.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Chercher les tomes I et II dans les archives (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 38], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Après des heures de recherche, vous trouvez le tome I derrière la photocopieuse et le tome II dans un carton marqué "PERTE TOTALE".'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D01', 'target' => 'leader']], 'narration' => 'Les tomes sont introuvables. Moreau soupire. "Je sais. Je les cherche depuis 1987." Il vous suggère une autre approche.']],
                            ['id' => 'B', 'text' => 'Proposer de recréer les tomes manquants (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 32], 'success' => ['next_step' => 3, 'effects' => [], 'narration' => 'Vous reconstituez les grandes lignes des tomes I et II d\'après les références du tome III. C\'est approximatif mais Moreau est ému.'], 'failure' => ['next_step' => 3, 'effects' => [['type' => 'debuff', 'id' => 'D08', 'target' => 'leader']], 'narration' => 'Votre reconstitution est historiquement incorrecte. Moreau le sait. Il accepte quand même. "C\'est mieux que rien."']],
                        ],
                    ],
                    [
                        'narration'        => 'Le rapport complet fait 2400 pages en comptant les tomes retrouvés/reconstitués. Il doit aller dans la section "Archives Définitives". La section Archives Définitives est condamnée depuis 2003 suite à une "restructuration provisoire".',
                        'narrator_comment' => 'La restructuration provisoire de 2003. Elle est encore en cours. Dans l\'administration, "provisoire" est un terme relatif.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Forcer l\'entrée de la section condamnée (test ATQ)', 'test' => ['stat' => 'atq', 'difficulty' => 30], 'success' => ['next_step' => 4, 'effects' => [], 'narration' => 'La porte cède. La section Archives Définitives est intacte. Elle est juste fermée depuis 20 ans. Ça sent le papier vieux.'], 'failure' => ['next_step' => 4, 'effects' => [['type' => 'debuff', 'id' => 'D03', 'target' => 'party']], 'narration' => 'La porte résiste. Vous finissez par passer par une fenêtre. Quelques blessures. La section est accessible.']],
                            ['id' => 'B', 'text' => 'Créer une nouvelle section d\'archives (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 25], 'success' => ['next_step' => 4, 'effects' => [['type' => 'buff', 'id' => 'B01', 'target' => 'party']], 'narration' => 'Brillant. Vous créez la section "Archives Transitoires Définitives". Moreau approuve. "C\'est nouveau mais conforme."'], 'failure' => ['next_step' => 4, 'effects' => [], 'narration' => 'La nouvelle section nécessite une approbation préalable. Moreau signe lui-même. Il est l\'unique fonctionnaire disponible.']],
                        ],
                    ],
                    [
                        'narration'        => 'Le rapport est prêt à être classé. Il manque le tampon final de l\'Inspecteur Général. L\'Inspecteur Général est lui aussi mort depuis les années 90. Son bureau est de l\'autre côté du bâtiment.',
                        'narrator_comment' => 'Deux fantômes bureaucrates dans le même bâtiment. Le Narrateur commence à comprendre pourquoi rien ne fonctionne ici.',
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Aller négocier avec le fantôme de l\'Inspecteur Général (test CHA)', 'test' => ['stat' => 'cha', 'difficulty' => 33], 'success' => ['next_step' => 5, 'effects' => [], 'narration' => 'L\'Inspecteur Général accepte de tamponner. "C\'est irrégulier mais le dossier est complet pour une fois." Il tamponne avec enthousiasme.'], 'failure' => ['next_step' => 5, 'effects' => [['type' => 'debuff', 'id' => 'D10', 'target' => 'party']], 'narration' => 'L\'Inspecteur Général exige une réunion préliminaire. La réunion dure trois heures. Le tampon est finalement apposé.']],
                            ['id' => 'B', 'text' => 'Utiliser le tampon de Moreau à la place (test INT)', 'test' => ['stat' => 'int', 'difficulty' => 28], 'success' => ['next_step' => 5, 'effects' => [], 'narration' => 'Vous convincquez Moreau que son tampon d\'inspecteur de terrain a autorité équivalente dans ce contexte précis. Il se laisse convaincre.'], 'failure' => ['next_step' => 5, 'effects' => [], 'narration' => 'Moreau refuse. "Les procédures existent pour une raison." Vous allez quand même voir l\'Inspecteur Général.']],
                        ],
                    ],
                    [
                        'narration'        => 'Le rapport est classé. L\'inspecteur Moreau regarde le dossier rejoindre les archives. Pour la première fois depuis 39 ans, son travail est terminé. Il commence lentement à devenir translucide. "Merci", dit-il simplement. Puis il disparaît, laissant une légère odeur de papier carbone.',
                        'narrator_comment' => 'Le Narrateur ne s\'attendait pas à être ému par une histoire de classement administratif. Et pourtant.',
                        'is_final'         => true,
                        'choices'          => [
                            ['id' => 'A', 'text' => 'Saluer respectueusement l\'inspecteur Moreau', 'test' => null, 'success' => ['next_step' => null, 'effects' => [['type' => 'reputation', 'zone' => 'marais_bureaucratie', 'amount' => 25]], 'narration' => 'Le bureau est silencieux. Sur le bureau de Moreau reste une feuille : "Bon pour service. Signé : Moreau." La paperasse fantomatique a disparu.'], 'failure' => null],
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

            $existing = DB::table('quests')
                ->where('zone_id', $zoneId)
                ->where('title', $questData['title'])
                ->first();

            if ($existing) {
                // Quest exists but steps may be missing (e.g. previous seeder run failed mid-way)
                $stepCount = DB::table('quest_steps')->where('quest_id', $existing->id)->count();
                if ($stepCount === 0) {
                    foreach ($steps as $index => $step) {
                        DB::table('quest_steps')->insert([
                            'quest_id'   => $existing->id,
                            'step_index' => $index + 1,
                            'content'    => json_encode($step, JSON_UNESCAPED_UNICODE),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                continue;
            }

            $questData['type'] = 'zone';
            $questData['created_at'] = now();
            $questData['updated_at'] = now();

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
