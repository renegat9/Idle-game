<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');
        $foretId   = DB::table('zones')->where('slug', 'foret_elfes')->value('id');
        $minesId   = DB::table('zones')->where('slug', 'mines_nain')->value('id');
        $maraisId  = DB::table('zones')->where('slug', 'marais_bureaucratie')->value('id');
        $tourId    = DB::table('zones')->where('slug', 'tour_mage_distrait')->value('id');
        $cimetId   = DB::table('zones')->where('slug', 'cimetiere_syndique')->value('id');
        $volcanId  = DB::table('zones')->where('slug', 'volcan_dragon_retraite')->value('id');
        $capitId   = DB::table('zones')->where('slug', 'capitale_incompetents')->value('id');

        $templates = [
            // ── Zone 1 : Prairie ──
            [$prairieId, 'Bâton Ramassé Par Terre', 'Un bâton. Trouvé par terre. Ça fait des dégâts... un peu.', 'commun', 'arme', 'physique', null, 4, 0, 0, 0, 0, 0, 1, 5],
            [$prairieId, 'Épée Rouillée', 'Une épée. Très rouillée. Ça coupe... parfois.', 'commun', 'arme', 'physique', json_encode(['guerrier', 'barbare']), 6, 0, 0, -1, 0, 0, 1, 8],
            [$prairieId, 'Dague Émoussée', 'Censée piquer. Elle chatouille surtout.', 'commun', 'arme', 'physique', json_encode(['voleur', 'ranger']), 5, 0, 0, 2, 0, 0, 1, 6],
            [$prairieId, 'Baguette de Noisetier', 'Pas encore enchantée mais Gérard dit que ça compte.', 'commun', 'arme', 'physique', json_encode(['mage', 'pretre', 'necromancien']), 2, 0, 0, 0, 0, 5, 1, 7],
            [$prairieId, 'Gilet en Toile de Jute', 'Légèrement protecteur. Très inconfortable.', 'commun', 'armure', 'physique', null, 0, 4, 5, 0, 0, 0, 1, 6],
            [$prairieId, 'Armure de Cuir Douteux', 'Ça sent... particulier. Mais ça protège.', 'commun', 'armure', 'physique', json_encode(['guerrier', 'voleur', 'ranger', 'barbare']), 0, 6, 8, -1, 0, 0, 1, 10],
            [$prairieId, 'Casque en Bois', 'Fait par Gérard un dimanche pluvieux.', 'commun', 'casque', 'physique', null, 0, 2, 5, 0, -1, 0, 1, 5],
            [$prairieId, 'Sandales de Pèlerin', 'Confortables. Pas terribles en combat.', 'commun', 'bottes', 'physique', null, 0, 1, 0, 3, 0, 0, 1, 4],
            [$prairieId, 'Anneau en Étain', 'Peut-être magique. Probablement pas.', 'commun', 'accessoire', 'physique', null, 0, 0, 5, 0, 2, 0, 1, 5],
            [$prairieId, 'Épée du Brave (Presque)', 'Une épée bien équilibrée. Appartient à quelqu\'un d\'autre mais bon.', 'peu_commun', 'arme', 'physique', json_encode(['guerrier', 'barbare']), 10, 1, 5, 0, 0, 0, 2, 18],
            [$prairieId, 'Bâton de l\'Apprenti Souffleur', 'Un mage l\'a laissé là. Il le réclamait depuis 3 ans.', 'peu_commun', 'arme', 'foudre', json_encode(['mage', 'necromancien']), 3, 0, 0, 2, 0, 9, 2, 20],
            [$prairieId, 'Cuirasse des Prairies', 'Fabriquée avec des ressources locales. C\'est une façon de le dire.', 'peu_commun', 'armure', 'physique', null, 0, 10, 15, -2, 0, 0, 2, 22],
            [$prairieId, 'Bottes de l\'Explorateur Imprudent', 'L\'explorateur est décédé. Ses bottes sont disponibles.', 'peu_commun', 'bottes', 'physique', null, 0, 2, 5, 6, 0, 0, 2, 15],
            [$prairieId, 'Amulette du Chanceux', 'Porte chance. Enfin c\'est ce qu\'on dit.', 'peu_commun', 'accessoire', 'physique', null, 0, 0, 10, 0, 5, 3, 2, 20],

            // ── Zone 2 : Forêt des Elfes Vexés ──
            [$foretId, 'Arc des Elfes Contrariés', 'Les elfes s\'en servent pour vous regarder de haut. Et pour tirer des flèches.', 'commun', 'arme', 'physique', json_encode(['ranger', 'voleur']), 12, 0, 0, 4, 0, 3, 5, 20],
            [$foretId, 'Lame de Fée', 'Tranchante mais capricieuse. Comme une fée.', 'commun', 'arme', 'sacre', json_encode(['voleur', 'barde']), 10, 0, 0, 6, 3, 2, 6, 22],
            [$foretId, 'Bâton des Bois Enchantés', 'La forêt vous l\'a vendu sans vous demander votre avis.', 'commun', 'arme', 'sacre', json_encode(['mage', 'pretre', 'necromancien', 'barde']), 5, 0, 0, 2, 0, 14, 5, 25],
            [$foretId, 'Armure d\'Écorce', 'Légère et résistante. Sent le bois mouillé.', 'commun', 'armure', 'physique', null, 0, 12, 18, -1, 0, 3, 5, 28],
            [$foretId, 'Capuche Elfique (Rejetée)', 'Un elfe l\'a jetée parce qu\'elle ne lui allait plus. Super.', 'commun', 'casque', 'sacre', null, 0, 5, 8, 0, 2, 5, 5, 18],
            [$foretId, 'Bottes de Fée', 'Silencieuses. Les fées les portent pour vous espionner.', 'commun', 'bottes', 'sacre', null, 0, 2, 5, 10, 0, 3, 6, 22],
            [$foretId, 'Gemme de la Forêt (Brute)', 'Trouvée sous un champignon. La forêt est possessive.', 'commun', 'accessoire', 'sacre', null, 0, 0, 12, 0, 4, 6, 5, 20],
            [$foretId, 'Arc de Bois d\'If Maudit', 'Chaque flèche est une réponse à vos choix de vie.', 'peu_commun', 'arme', 'poison', json_encode(['ranger']), 18, 0, 0, 8, 0, 5, 7, 50],
            [$foretId, 'Dague d\'Ombre Sylvestre', 'Forgée dans l\'ombre des grands arbres. Gérard s\'est coupé.', 'peu_commun', 'arme', 'poison', json_encode(['voleur']), 16, 2, 0, 10, 0, 4, 7, 45],
            [$foretId, 'Robe des Druides Excentriques', 'Porter ceci vous donne l\'air sage. Ce n\'est qu\'une apparence.', 'peu_commun', 'armure', 'sacre', json_encode(['mage', 'pretre', 'barde', 'necromancien']), 0, 15, 20, 0, 5, 18, 6, 60],
            [$foretId, 'Couronne de Brindilles', 'Un elfe l\'appelle "diadème". Tout le monde voit des brindilles.', 'peu_commun', 'casque', 'physique', null, 0, 8, 12, 0, 6, 8, 7, 40],
            [$foretId, 'Pendentif de Racine Vivante', 'La racine essaie parfois de s\'accrocher à votre cou.', 'peu_commun', 'accessoire', 'sacre', null, 2, 3, 15, 4, 8, 10, 7, 55],

            // ── Zone 3 : Mines du Nain Ivre ──
            [$minesId, 'Pioche de Mineur (Usagée)', 'Ça fait des dégâts. Thorin en a un stock infini.', 'commun', 'arme', 'physique', json_encode(['guerrier', 'barbare']), 22, 0, 0, -2, 0, 0, 12, 40],
            [$minesId, 'Masse de Pierre Grossière', 'Très lourde. Thorin l\'appelle "son poids de forme".', 'commun', 'arme', 'physique', json_encode(['guerrier', 'barbare', 'pretre']), 20, 3, 5, -3, 0, 0, 12, 38],
            [$minesId, 'Casque de Mineur Cabossé', 'Il a servi à protéger quelqu\'un. Ce quelqu\'un n\'est plus là.', 'commun', 'casque', 'physique', null, 0, 14, 20, -2, 0, 0, 12, 35],
            [$minesId, 'Tablier de Forge en Cuir', 'Sentait le métal fondu. Maintenant il sent juste... fort.', 'commun', 'armure', 'physique', null, 0, 18, 25, -3, 0, 0, 13, 45],
            [$minesId, 'Bottes Ferrées de Mineur', 'Lourdes, résistantes, douloureuses. En un mot : naines.', 'commun', 'bottes', 'physique', null, 0, 8, 10, 2, 0, 0, 12, 30],
            [$minesId, 'Gemme Brute de Cristal', 'Cristal pas encore taillé. Gérard promet de s\'en occuper.', 'commun', 'accessoire', 'physique', null, 0, 0, 20, 0, 3, 5, 13, 35],
            [$minesId, 'Hache de Guerre Naine', 'Forgée avec amour, alcool et beaucoup de métal.', 'peu_commun', 'arme', 'physique', json_encode(['guerrier', 'barbare']), 32, 5, 0, -2, 0, 0, 14, 100],
            [$minesId, 'Orbe de Cristal Minier', 'Le cristal vibre bizarrement. Gérard évite de le toucher.', 'peu_commun', 'arme', 'sacre', json_encode(['mage', 'necromancien']), 8, 0, 0, 0, 0, 28, 13, 95],
            [$minesId, 'Armure de Plates Naine (taille -3)', 'Faite pour un nain. Adaptable avec de la volonté.', 'peu_commun', 'armure', 'physique', null, 0, 28, 35, -5, 0, 0, 14, 120],
            [$minesId, 'Heaume du Défenseur Alcoolisé', 'Le défenseur avait bu. Sa technique était surprenante.', 'peu_commun', 'casque', 'physique', null, 0, 20, 28, -2, -3, 0, 14, 90],
            [$minesId, 'Amulette de la Chope Magique', 'Pleine de bière enchantée. Thorin vous demande de la rendre.', 'peu_commun', 'accessoire', 'physique', null, 5, 5, 30, 0, 8, 0, 13, 85],

            // ── Zone 4 : Marais de la Bureaucratie ──
            [$maraisId, 'Sceptre du Fonctionnaire', 'Tamponne les ennemis. Officiellement.', 'commun', 'arme', 'ombre', json_encode(['pretre', 'mage', 'necromancien']), 15, 5, 0, 0, 10, 18, 20, 60],
            [$maraisId, 'Épée Administrative', 'Chaque coup nécessite un formulaire préalable.', 'commun', 'arme', 'physique', json_encode(['guerrier']), 32, 5, 0, 0, 5, 0, 20, 65],
            [$maraisId, 'Robe du Bureaucrate Arcanique', 'Grise. Terne. Mais résistante aux sorts comme aux plaintes.', 'commun', 'armure', 'ombre', json_encode(['mage', 'necromancien']), 0, 20, 30, 0, 5, 22, 20, 70],
            [$maraisId, 'Cotte de Mailles Marécageuse', 'Rouillée par le marais mais qui a dit que c\'était un défaut ?', 'commun', 'armure', 'physique', null, 0, 28, 40, -4, 0, 0, 21, 75],
            [$maraisId, 'Chapeau du Percepteur', 'Noir, haut de forme. Les ennemis lui obéissent par réflexe.', 'commun', 'casque', 'ombre', null, 0, 10, 15, 0, 18, 10, 20, 55],
            [$maraisId, 'Bottes de Marais Imperméables', 'Imperméables. C\'est déjà ça.', 'commun', 'bottes', 'physique', null, 0, 12, 15, 14, 0, 0, 21, 50],
            [$maraisId, 'Tampon Officiel +3', 'Valide la mort de vos ennemis. Encre rouge incluse.', 'commun', 'accessoire', 'ombre', null, 5, 0, 20, 0, 15, 5, 20, 60],
            [$maraisId, 'Faux du Nécrobiureau', 'Pour récolter les âmes en souffrance administrative.', 'peu_commun', 'arme', 'ombre', json_encode(['necromancien']), 42, 8, 0, 5, 0, 30, 22, 160],
            [$maraisId, 'Bâton des Marécages Profonds', 'Trouvé dans des profondeurs que vous préféreriez oublier.', 'peu_commun', 'arme', 'sacre', json_encode(['mage', 'pretre']), 12, 0, 0, 0, 8, 40, 21, 150],
            [$maraisId, 'Armure du Haut Fonctionnaire', 'Protège contre les attaques physiques et les mémos non sollicités.', 'peu_commun', 'armure', 'ombre', null, 0, 38, 55, -5, 12, 0, 22, 185],
            [$maraisId, 'Heaume de l\'Inquisiteur Fiscal', 'Regarde vos ennemis avec soupçon. Ils s\'en rendent compte.', 'peu_commun', 'casque', 'ombre', null, 0, 25, 35, 0, 20, 15, 21, 150],
            [$maraisId, 'Anneau du Pacte Marécageux', 'Signé en triplicata. Pas moyen de s\'en défaire.', 'peu_commun', 'accessoire', 'ombre', null, 8, 8, 40, 5, 18, 15, 22, 170],

            // ── Zone 5 : Tour du Mage Distrait ──
            [$tourId, 'Baguette du Distrait', 'Lance des sorts. Pas toujours les bons. Rarement les bons.', 'commun', 'arme', 'foudre', json_encode(['mage', 'necromancien']), 12, 0, 0, 5, 0, 40, 30, 100],
            [$tourId, 'Livre de Sorts Annoté', 'Les annotations sont illisibles. Même le Mage ne sait plus ce qu\'il a écrit.', 'commun', 'arme', 'physique', json_encode(['mage', 'barde']), 18, 0, 0, 0, 5, 35, 31, 110],
            [$tourId, 'Robe des Apprentis Ratés', 'Toutes les taches de sort sont des médailles d\'honneur selon le Mage.', 'commun', 'armure', 'foudre', json_encode(['mage', 'necromancien', 'barde']), 0, 30, 45, 5, 5, 30, 30, 120],
            [$tourId, 'Chapeau Pointu Légèrement Tordu', 'Tellement pointu qu\'il sert d\'arme. Involontairement.', 'commun', 'casque', 'foudre', null, 5, 15, 20, 0, 8, 25, 31, 100],
            [$tourId, 'Bottes de l\'Apprenti Pressé', 'Courir vite dans une tour magique instable est une nécessité.', 'commun', 'bottes', 'physique', null, 0, 12, 15, 20, 0, 0, 30, 90],
            [$tourId, 'Orbe de Foudre Capricieux', 'Génère de l\'électricité. Dont sur vous si vous n\'êtes pas concentré.', 'commun', 'accessoire', 'foudre', null, 8, 0, 30, 0, 5, 20, 31, 105],
            [$tourId, 'Sceptre du Chaos Arcanique', 'Le Mage l\'a oublié dans un couloir. Il est mécontent que vous l\'ayez.', 'peu_commun', 'arme', 'foudre', json_encode(['mage', 'necromancien']), 20, 0, 0, 8, 0, 65, 33, 280],
            [$tourId, 'Tunique de Magie Inversée', 'Chaque sort devient... différent. Gérard refuse de la toucher.', 'peu_commun', 'armure', 'glace', json_encode(['mage', 'barde', 'necromancien']), 0, 42, 65, 8, 10, 45, 34, 300],
            [$tourId, 'Diadème du Savant Confus', 'Améliore l\'intelligence. Malheureusement aussi la confusion.', 'peu_commun', 'casque', 'foudre', null, 0, 22, 32, 0, 15, 52, 33, 260],
            [$tourId, 'Anneau du Paradoxe', 'Porte chance ET malchance. Le résultat est imprévisible.', 'peu_commun', 'accessoire', 'physique', null, 10, 10, 60, 10, 20, 30, 34, 290],

            // ── Zone 6 : Cimetière Syndiqué ──
            [$cimetId, 'Faux du Syndicat Funèbre', 'Chaque coup respecte le droit du travail des morts-vivants.', 'commun', 'arme', 'ombre', json_encode(['necromancien']), 48, 0, 0, 0, 0, 30, 42, 200],
            [$cimetId, 'Épée des Revenants', 'Forgée par des fantômes. Ils n\'arrivent pas à la tenir correctement.', 'commun', 'arme', 'ombre', json_encode(['guerrier', 'paladin']), 55, 5, 0, -3, 0, 15, 43, 210],
            [$cimetId, 'Armure Fantôme', 'Mi-solide, mi-vapeur. Protège à 50% tout le temps ou à 100% une fois sur deux.', 'commun', 'armure', 'ombre', null, 0, 58, 80, -2, 5, 15, 42, 220],
            [$cimetId, 'Heaume du Mort-Vivant Dignifié', 'Il avait une belle mort. Vous avez son casque maintenant.', 'commun', 'casque', 'ombre', null, 0, 28, 40, -2, 10, 18, 43, 190],
            [$cimetId, 'Bottes de la Marche Funèbre', 'Silencieuses. Très. Personne ne vous entend arriver. Ni partir.', 'commun', 'bottes', 'ombre', null, 0, 18, 25, 22, 0, 0, 42, 170],
            [$cimetId, 'Médaillon du Syndicat', 'Prouve que vous êtes en règle avec les morts-vivants. Important.', 'commun', 'accessoire', 'ombre', null, 5, 5, 60, 0, 20, 12, 43, 195],
            [$cimetId, 'Grimoire des Ombres Syndicales', 'Les sorts nécessitent un préavis de 48h. C\'est dans le règlement.', 'peu_commun', 'arme', 'ombre', json_encode(['mage', 'necromancien', 'barde']), 25, 0, 0, 5, 0, 90, 45, 500],
            [$cimetId, 'Armure du Délégué Fantôme', 'Protège et syndicalise en même temps. Bonus : pause syndicale bonus.', 'peu_commun', 'armure', 'ombre', null, 0, 80, 110, -3, 18, 25, 46, 550],
            [$cimetId, 'Couronne des Âmes Perdues', 'Elles ne sont pas perdues, elles font grève.', 'peu_commun', 'casque', 'ombre', null, 0, 40, 60, 0, 28, 40, 45, 490],
            [$cimetId, 'Phylactère du Comptable', 'Stocke la vie. En double entrée comptable.', 'peu_commun', 'accessoire', 'ombre', null, 12, 12, 100, 5, 25, 40, 46, 520],

            // ── Zone 7 : Volcan du Dragon Retraité ──
            [$volcanId, 'Épée de Magma', 'Encore chaude. Évitez de la saisir par la lame. Évitez tout court.', 'commun', 'arme', 'feu', json_encode(['guerrier', 'barbare']), 85, 0, 0, -5, 0, 0, 55, 380],
            [$volcanId, 'Bâton de Lave Solidifiée', 'Très chaud. Pas une métaphore.', 'commun', 'arme', 'feu', json_encode(['mage', 'necromancien', 'pretre']), 20, 0, 0, 0, 0, 80, 56, 390],
            [$volcanId, 'Armure d\'Obsidienne', 'Forgée dans un volcan par des créatures qui ne savent pas que c\'est chaud.', 'commun', 'armure', 'feu', null, 0, 90, 120, -8, 0, 0, 55, 400],
            [$volcanId, 'Casque Ignifugé du Dragon', 'Le dragon l\'a usé. Il sentait la fumée. Il sent encore la fumée.', 'commun', 'casque', 'feu', null, 0, 42, 60, -3, 0, 0, 56, 360],
            [$volcanId, 'Bottes de Cendres Volantes', 'Ultra légères car faites de cendres. Ultra chaudes aussi.', 'commun', 'bottes', 'feu', null, 0, 20, 30, 28, 0, 0, 55, 330],
            [$volcanId, 'Amulette de l\'Éruption', 'Peut exploser. Gérard a demandé à ne pas en être informé.', 'commun', 'accessoire', 'feu', null, 15, 0, 80, 0, 8, 15, 56, 370],
            [$volcanId, 'Épée du Phoenix Tombé', 'Un phénix est mort pour forger ça. Il est revenu. Il est en colère.', 'peu_commun', 'arme', 'feu', json_encode(['guerrier', 'paladin', 'barbare']), 115, 8, 0, -3, 0, 0, 58, 900],
            [$volcanId, 'Manteau de Braise Vivante', 'Brûle légèrement les ennemis en approche. Et vous aussi parfois.', 'peu_commun', 'armure', 'feu', null, 0, 125, 175, -5, 0, 20, 59, 950],
            [$volcanId, 'Crocs du Dragon Retraité', 'Il les a perdus naturellement. Il veut qu\'on lui rende. Non.', 'peu_commun', 'accessoire', 'feu', null, 20, 15, 150, 5, 12, 30, 58, 880],
            [$volcanId, 'Heaume du Forgeron Damné', 'Sa dernière forge. Son chef-d\'œuvre. Vous êtes là à le porter.', 'peu_commun', 'casque', 'feu', null, 0, 60, 85, -4, 0, 35, 59, 870],

            // ── Zone 8 : Capitale des Incompétents ──
            [$capitId, 'Épée de Garde Corrompue', 'Chaque coup est légalement douteux. C\'est une caractéristique.', 'commun', 'arme', 'physique', json_encode(['guerrier', 'barbare']), 110, 0, 0, -2, 12, 0, 70, 550],
            [$capitId, 'Dague de l\'Assassin Amateur', 'L\'assassin était amateur. La dague, elle, est professionnelle.', 'commun', 'arme', 'ombre', json_encode(['voleur', 'ranger']), 105, 0, 0, 18, 5, 10, 71, 560],
            [$capitId, 'Tome du Mage de Rue', 'Volé, revendu, racheté. Ce livre a vécu plus que vous.', 'commun', 'arme', 'foudre', json_encode(['mage', 'barde']), 30, 0, 0, 10, 0, 120, 70, 570],
            [$capitId, 'Armure de Garde Municipal', 'Lourde, solide, officiellement distribuée. Non-officiellement revendue.', 'commun', 'armure', 'physique', null, 0, 120, 165, -8, 8, 0, 70, 580],
            [$capitId, 'Casque à Plumes du Capitaine', 'Impressionnant. Les plumes sont tombées. Les ennemis rient.', 'commun', 'casque', 'physique', null, 0, 55, 75, -3, 22, 0, 71, 520],
            [$capitId, 'Bottes de Pavés Usés', 'Courir sur les pavés de la capitale use les semelles. Et les pieds.', 'commun', 'bottes', 'physique', null, 0, 25, 40, 32, 0, 0, 70, 490],
            [$capitId, 'Écharpe du Marchand Douteux', 'Vendue à 10× sa valeur. Vous l\'avez quand même achetée.', 'commun', 'accessoire', 'physique', null, 10, 10, 100, 10, 30, 20, 71, 540],
            [$capitId, 'Lame du Maître de Guilde', 'Forgée pour un maître. Vous l\'avez gagnée. Il est humilié.', 'peu_commun', 'arme', 'physique', json_encode(['guerrier', 'paladin', 'barbare']), 148, 12, 0, 5, 15, 0, 73, 1200],
            [$capitId, 'Grimoire du Mage Urbain', 'Tous les sorts ont une variante d\'arnaque. C\'est une tradition locale.', 'peu_commun', 'arme', 'foudre', json_encode(['mage', 'necromancien', 'barde']), 40, 0, 0, 15, 0, 165, 74, 1250],
            [$capitId, 'Armure du Héros Incompétent Officiel', 'Certifiée "Incompétence Garantie" par la Guilde. Port exigé.', 'peu_commun', 'armure', 'physique', null, 0, 165, 230, -5, 20, 0, 73, 1280],
            [$capitId, 'Couronne du Candidat Battu', 'Il a perdu les élections. Vous avez sa couronne. Tout le monde perd.', 'peu_commun', 'casque', 'physique', null, 0, 75, 105, 0, 40, 30, 74, 1150],
            [$capitId, 'Médaillon de l\'Immunité Diplomatique', 'Protège contre 1 attaque par combat. Non-opposable aux gobelins.', 'peu_commun', 'accessoire', 'physique', null, 18, 18, 180, 12, 35, 35, 73, 1200],
        ];

        $cols = ['zone_id', 'name', 'description', 'rarity', 'slot', 'element', 'allowed_classes', 'base_atq', 'base_def', 'base_hp', 'base_vit', 'base_cha', 'base_int', 'base_level', 'base_sell_value'];

        foreach ($templates as $tpl) {
            $row = array_combine($cols, $tpl);
            DB::table('item_templates')->insert($row);
        }
    }
}
