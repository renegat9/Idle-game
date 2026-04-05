<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $templates = [
            // Armes communes
            ['zone_id' => $prairieId, 'name' => 'Bâton Ramassé Par Terre', 'description' => 'Un bâton. Trouvé par terre. Ça fait des dégâts... un peu.', 'rarity' => 'commun', 'slot' => 'arme', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 4, 'base_def' => 0, 'base_hp' => 0, 'base_vit' => 0, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 5],
            ['zone_id' => $prairieId, 'name' => 'Épée Rouillée', 'description' => 'Une épée. Très rouillée. Ça coupe... parfois.', 'rarity' => 'commun', 'slot' => 'arme', 'element' => 'physique', 'allowed_classes' => json_encode(['guerrier', 'barbare']), 'base_atq' => 6, 'base_def' => 0, 'base_hp' => 0, 'base_vit' => -1, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 8],
            ['zone_id' => $prairieId, 'name' => 'Dague Émoussée', 'description' => 'Censée piquer. Elle chatouille surtout.', 'rarity' => 'commun', 'slot' => 'arme', 'element' => 'physique', 'allowed_classes' => json_encode(['voleur', 'ranger']), 'base_atq' => 5, 'base_def' => 0, 'base_hp' => 0, 'base_vit' => 2, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 6],
            ['zone_id' => $prairieId, 'name' => 'Baguette de Noisetier', 'description' => 'Pas encore enchantée mais Gérard dit que ça compte.', 'rarity' => 'commun', 'slot' => 'arme', 'element' => 'physique', 'allowed_classes' => json_encode(['mage', 'pretre', 'necromancien']), 'base_atq' => 2, 'base_def' => 0, 'base_hp' => 0, 'base_vit' => 0, 'base_cha' => 0, 'base_int' => 5, 'base_level' => 1, 'base_sell_value' => 7],
            // Armures communes
            ['zone_id' => $prairieId, 'name' => 'Gilet en Toile de Jute', 'description' => 'Légèrement protecteur. Très inconfortable.', 'rarity' => 'commun', 'slot' => 'armure', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 4, 'base_hp' => 5, 'base_vit' => 0, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 6],
            ['zone_id' => $prairieId, 'name' => 'Armure de Cuir Douteux', 'description' => 'Ça sent... particulier. Mais ça protège.', 'rarity' => 'commun', 'slot' => 'armure', 'element' => 'physique', 'allowed_classes' => json_encode(['guerrier', 'voleur', 'ranger', 'barbare']), 'base_atq' => 0, 'base_def' => 6, 'base_hp' => 8, 'base_vit' => -1, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 10],
            // Casques communs
            ['zone_id' => $prairieId, 'name' => 'Casque en Bois', 'description' => 'Fait par Gérard un dimanche pluvieux.', 'rarity' => 'commun', 'slot' => 'casque', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 2, 'base_hp' => 5, 'base_vit' => 0, 'base_cha' => -1, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 5],
            // Bottes communes
            ['zone_id' => $prairieId, 'name' => 'Sandales de Pèlerin', 'description' => 'Confortables. Pas terribles en combat.', 'rarity' => 'commun', 'slot' => 'bottes', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 1, 'base_hp' => 0, 'base_vit' => 3, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 4],
            // Accessoires communs
            ['zone_id' => $prairieId, 'name' => 'Anneau en Étain', 'description' => 'Peut-être magique. Probablement pas.', 'rarity' => 'commun', 'slot' => 'accessoire', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 0, 'base_hp' => 5, 'base_vit' => 0, 'base_cha' => 2, 'base_int' => 0, 'base_level' => 1, 'base_sell_value' => 5],
            // Peu communs
            ['zone_id' => $prairieId, 'name' => 'Épée du Brave (Presque)', 'description' => 'Une épée bien équilibrée. Appartient à quelqu\'un d\'autre mais bon.', 'rarity' => 'peu_commun', 'slot' => 'arme', 'element' => 'physique', 'allowed_classes' => json_encode(['guerrier', 'barbare']), 'base_atq' => 10, 'base_def' => 1, 'base_hp' => 5, 'base_vit' => 0, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 2, 'base_sell_value' => 18],
            ['zone_id' => $prairieId, 'name' => 'Bâton de l\'Apprenti Souffleur', 'description' => 'Un mage l\'a laissé là. Il le réclamait depuis 3 ans.', 'rarity' => 'peu_commun', 'slot' => 'arme', 'element' => 'foudre', 'allowed_classes' => json_encode(['mage', 'necromancien']), 'base_atq' => 3, 'base_def' => 0, 'base_hp' => 0, 'base_vit' => 2, 'base_cha' => 0, 'base_int' => 9, 'base_level' => 2, 'base_sell_value' => 20],
            ['zone_id' => $prairieId, 'name' => 'Cuirasse des Prairies', 'description' => 'Fabriquée avec des ressources locales. C\'est une façon de le dire.', 'rarity' => 'peu_commun', 'slot' => 'armure', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 10, 'base_hp' => 15, 'base_vit' => -2, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 2, 'base_sell_value' => 22],
            ['zone_id' => $prairieId, 'name' => 'Bottes de l\'Explorateur Imprudent', 'description' => 'L\'explorateur est décédé. Ses bottes sont disponibles.', 'rarity' => 'peu_commun', 'slot' => 'bottes', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 2, 'base_hp' => 5, 'base_vit' => 6, 'base_cha' => 0, 'base_int' => 0, 'base_level' => 2, 'base_sell_value' => 15],
            ['zone_id' => $prairieId, 'name' => 'Amulette du Chanceux', 'description' => 'Porte chance. Enfin c\'est ce qu\'on dit.', 'rarity' => 'peu_commun', 'slot' => 'accessoire', 'element' => 'physique', 'allowed_classes' => null, 'base_atq' => 0, 'base_def' => 0, 'base_hp' => 10, 'base_vit' => 0, 'base_cha' => 5, 'base_int' => 3, 'base_level' => 2, 'base_sell_value' => 20],
        ];

        foreach ($templates as $template) {
            DB::table('item_templates')->insert($template);
        }
    }
}
