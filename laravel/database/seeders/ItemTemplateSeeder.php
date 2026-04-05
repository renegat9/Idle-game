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
        ];

        $cols = ['zone_id', 'name', 'description', 'rarity', 'slot', 'element', 'allowed_classes', 'base_atq', 'base_def', 'base_hp', 'base_vit', 'base_cha', 'base_int', 'base_level', 'base_sell_value'];

        foreach ($templates as $tpl) {
            $row = array_combine($cols, $tpl);
            DB::table('item_templates')->insert($row);
        }
    }
}
