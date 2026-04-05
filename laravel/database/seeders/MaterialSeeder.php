<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $materials = [
            // Matériaux génériques
            ['zone_id' => null, 'slug' => 'ferraille', 'name' => 'Ferraille', 'description' => 'Des bouts de métal de qualité douteuse', 'is_generic' => 1, 'drop_chance' => 30, 'base_value' => 2],
            ['zone_id' => null, 'slug' => 'essence_magique', 'name' => 'Essence Magique', 'description' => 'Une essence qui brille. Probablement magique.', 'is_generic' => 1, 'drop_chance' => 15, 'base_value' => 8],
            ['zone_id' => null, 'slug' => 'bout_de_ficelle', 'name' => 'Bout de Ficelle', 'description' => 'Indispensable selon Gérard', 'is_generic' => 1, 'drop_chance' => 25, 'base_value' => 1],
            ['zone_id' => null, 'slug' => 'cuir_abime', 'name' => 'Cuir Abîmé', 'description' => 'Du cuir qui a vu des jours meilleurs', 'is_generic' => 1, 'drop_chance' => 20, 'base_value' => 3],
            // Matériaux Zone 1 — Prairie
            ['zone_id' => $prairieId, 'slug' => 'herbe_prairie', 'name' => 'Herbe de Prairie', 'description' => 'De l\'herbe. Verte. De la prairie.', 'is_generic' => 0, 'drop_chance' => 40, 'base_value' => 1],
            ['zone_id' => $prairieId, 'slug' => 'fourrure_rat', 'name' => 'Fourrure de Rat', 'description' => 'Étonnamment douce. Ne posez pas de questions.', 'is_generic' => 0, 'drop_chance' => 35, 'base_value' => 3],
            ['zone_id' => $prairieId, 'slug' => 'residus_slime', 'name' => 'Résidus de Slime', 'description' => 'Gluant. Inutile. Mais Gérard en veut toujours.', 'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 4],
            ['zone_id' => $prairieId, 'slug' => 'croc_gobelin', 'name' => 'Croc de Gobelin', 'description' => 'Un croc. D\'un gobelin. Assez propre.', 'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 6],
            ['zone_id' => $prairieId, 'slug' => 'dard_abeille', 'name' => 'Dard d\'Abeille Géante', 'description' => 'Très pointu. Légèrement venimeux. Très légèrement.', 'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 8],
        ];

        foreach ($materials as $mat) {
            DB::table('materials')->updateOrInsert(['slug' => $mat['slug']], $mat);
        }
    }
}
