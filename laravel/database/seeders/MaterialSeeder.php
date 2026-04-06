<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
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

        $materials = [
            // ── Matériaux génériques (toutes zones) — selon ECONOMY.md §7.1 ──
            ['zone_id' => null, 'slug' => 'ferraille',          'name' => 'Ferraille',              'description' => 'Des bouts de métal de qualité douteuse. Gérard en redemande toujours.',                 'is_generic' => 1, 'drop_chance' => 30, 'base_value' => 2],
            ['zone_id' => null, 'slug' => 'cuir',               'name' => 'Cuir',                   'description' => 'Du cuir brut. Solide. Sent un peu. C\'est normal.',                                     'is_generic' => 1, 'drop_chance' => 25, 'base_value' => 3],
            ['zone_id' => null, 'slug' => 'gemme_brute',        'name' => 'Gemme Brute',            'description' => 'Pas encore taillée. Gérard dit qu\'il s\'en occupera. Depuis 6 mois.',                  'is_generic' => 1, 'drop_chance' => 15, 'base_value' => 8],
            ['zone_id' => null, 'slug' => 'essence_mineure',    'name' => 'Essence Mineure',        'description' => 'Brille légèrement. Utile pour les recettes intermédiaires.',                            'is_generic' => 1, 'drop_chance' => 12, 'base_value' => 15],
            ['zone_id' => null, 'slug' => 'cristal_brut',       'name' => 'Cristal Brut',           'description' => 'Très rare. Très utile. Le Narrateur le surveille de près.',                             'is_generic' => 1, 'drop_chance' => 5,  'base_value' => 40],
            ['zone_id' => null, 'slug' => 'essence_majeure',    'name' => 'Essence Majeure',        'description' => 'Obtenue des boss et légendaires. Gérard refuse de la toucher sans gants.',              'is_generic' => 1, 'drop_chance' => 2,  'base_value' => 100],
            ['zone_id' => null, 'slug' => 'fragment_stellaire', 'name' => 'Fragment Stellaire',     'description' => 'Un éclat de quelque chose de cosmique. Provenance inconnue. Mieux vaut pas demander.',   'is_generic' => 1, 'drop_chance' => 1,  'base_value' => 300],
            ['zone_id' => null, 'slug' => 'ficelle_cosmique',   'name' => 'Bout de Ficelle Cosmique','description' => 'WTF. Vraiment WTF.',                                                                   'is_generic' => 1, 'drop_chance' => 0,  'base_value' => 1000],
            ['zone_id' => null, 'slug' => 'larme_gerard',       'name' => 'Larme de Gérard',        'description' => '10% de chance sur un échec de fusion. Gérard jure que ce sont des gouttes de sueur.',   'is_generic' => 1, 'drop_chance' => 0,  'base_value' => 50],
            ['zone_id' => null, 'slug' => 'poussieres_narrateur','name' => 'Poussière du Narrateur', 'description' => 'Obtenue lors d\'événements spéciaux. Sent le café.',                                    'is_generic' => 1, 'drop_chance' => 0,  'base_value' => 500],

            // ── Zone 1 — La Prairie des Débutants ──
            ['zone_id' => $prairieId, 'slug' => 'herbe_prairie',   'name' => 'Herbe de Prairie',      'description' => 'De l\'herbe. Verte. De la prairie. Gérard fait des tisanes avec.',                   'is_generic' => 0, 'drop_chance' => 40, 'base_value' => 1],
            ['zone_id' => $prairieId, 'slug' => 'fourrure_rat',    'name' => 'Fourrure de Rat',       'description' => 'Étonnamment douce. Ne posez pas de questions sur comment vous l\'avez obtenue.',       'is_generic' => 0, 'drop_chance' => 35, 'base_value' => 3],
            ['zone_id' => $prairieId, 'slug' => 'residus_slime',   'name' => 'Résidus de Slime',      'description' => 'Gluant. Inutile. Mais Gérard en veut toujours pour ses colles.',                      'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 4],
            ['zone_id' => $prairieId, 'slug' => 'croc_gobelin',    'name' => 'Croc de Gobelin',       'description' => 'Un croc. D\'un gobelin. Assez propre. Relatif.',                                      'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 6],
            ['zone_id' => $prairieId, 'slug' => 'dard_abeille',    'name' => 'Dard d\'Abeille Géante','description' => 'Très pointu. Légèrement venimeux. Très légèrement. Enfin Gérard l\'espère.',          'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 8],
            ['zone_id' => $prairieId, 'slug' => 'laine_taureau',   'name' => 'Poil de Taureau',       'description' => 'Dur à arracher. Encore plus dur à arracher poliment.',                                'is_generic' => 0, 'drop_chance' => 10, 'base_value' => 12],

            // ── Zone 2 — La Forêt des Elfes Vexés ──
            ['zone_id' => $foretId, 'slug' => 'seve_toxique',       'name' => 'Sève Toxique',          'description' => 'Obtenue des plantes de la forêt. Ne léchez pas.',                                   'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 10],
            ['zone_id' => $foretId, 'slug' => 'bois_encante',       'name' => 'Bois Enchanté',         'description' => 'La forêt vous l\'a donné. Elle veut probablement quelque chose en retour.',         'is_generic' => 0, 'drop_chance' => 25, 'base_value' => 12],
            ['zone_id' => $foretId, 'slug' => 'pollen_fee',         'name' => 'Pollen de Fée',         'description' => 'Brillant, capricieux, légèrement allergisant.',                                     'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 15],
            ['zone_id' => $foretId, 'slug' => 'ecorce_treant',      'name' => 'Écorce de Tréant',      'description' => 'Extrêmement dure. Le tréant était mécontent de la donner.',                        'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 18],
            ['zone_id' => $foretId, 'slug' => 'toile_araignee',     'name' => 'Toile d\'Araignée',     'description' => 'Résistante. Légèrement collante. Gérard s\'y est coincé deux fois.',               'is_generic' => 0, 'drop_chance' => 12, 'base_value' => 20],

            // ── Zone 3 — Les Mines du Nain Ivre ──
            ['zone_id' => $minesId, 'slug' => 'minerai_cristal',    'name' => 'Minerai de Cristal',    'description' => 'Extrait avec passion et sans sobriété.',                                            'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 20],
            ['zone_id' => $minesId, 'slug' => 'poudre_golem',       'name' => 'Poudre de Golem',       'description' => 'Ce qu\'il reste après avoir battu un golem. Gérard la met dans ses gâteaux.',      'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 25],
            ['zone_id' => $minesId, 'slug' => 'charbon_ardent',     'name' => 'Charbon Ardent',        'description' => 'Ne refroidit jamais. Pratique pour forger. Moins pour les poches.',                'is_generic' => 0, 'drop_chance' => 25, 'base_value' => 18],
            ['zone_id' => $minesId, 'slug' => 'biere_naine',        'name' => 'Bière Naine Concentrée','description' => 'Ingredient secret de Thorin. 90% alcool. 10% courage. 0% sagesse.',               'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 30],

            // ── Zone 4 — Le Marais de la Bureaucratie ──
            ['zone_id' => $maraisId, 'slug' => 'encre_zombie',      'name' => 'Encre de Zombie',       'description' => 'Sert à remplir les formulaires. Coule bizarrement. Convient.',                    'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 25],
            ['zone_id' => $maraisId, 'slug' => 'vase_marais',       'name' => 'Vase du Marais',        'description' => 'Résistant à tout, y compris au bon sens.',                                        'is_generic' => 0, 'drop_chance' => 35, 'base_value' => 20],
            ['zone_id' => $maraisId, 'slug' => 'formulaire_maudit', 'name' => 'Formulaire Maudit',     'description' => 'Un W-47-ter incomplet. Utile pour les recettes administratives.',                  'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 35],

            // ── Zone 5 — La Tour du Mage Distrait ──
            ['zone_id' => $tourId, 'slug' => 'eclat_foudre',        'name' => 'Éclat de Foudre',       'description' => 'Une charge électrique captée pendant un sort raté du Mage. Pique encore.',        'is_generic' => 0, 'drop_chance' => 25, 'base_value' => 40],
            ['zone_id' => $tourId, 'slug' => 'glace_eternelle',     'name' => 'Glace Éternelle',       'description' => 'Ne fond pas. Jamais. Pas même dans la lave. Gérard a essayé.',                   'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 45],
            ['zone_id' => $tourId, 'slug' => 'page_sort_rate',      'name' => 'Page de Sort Raté',     'description' => 'Une page arrachée d\'un grimoire avec un sort à moitié lancé dessus.',            'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 35],

            // ── Zone 6 — Le Cimetière Syndiqué ──
            ['zone_id' => $cimetId, 'slug' => 'essence_ombre',      'name' => 'Essence d\'Ombre',      'description' => 'Condensé d\'obscurité syndicale. Très utile. Légèrement déprimant.',              'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 60],
            ['zone_id' => $cimetId, 'slug' => 'os_revenant',        'name' => 'Os de Revenant',        'description' => 'Collecté après combat. Le revenant était en pause syndicale donc c\'est légal.',  'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 50],
            ['zone_id' => $cimetId, 'slug' => 'cristal_ombre',      'name' => 'Cristal d\'Ombre',      'description' => 'Brille dans l\'obscurité. Dans la lumière il est juste gris. Décevant.',         'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 75],

            // ── Zone 7 — Le Volcan du Dragon Retraité ──
            ['zone_id' => $volcanId, 'slug' => 'cendre_volcanique', 'name' => 'Cendre Volcanique',    'description' => 'Encore chaude. Encore légèrement en feu. Gérard porte des gants maintenant.',     'is_generic' => 0, 'drop_chance' => 35, 'base_value' => 70],
            ['zone_id' => $volcanId, 'slug' => 'ecaille_dragon',    'name' => 'Écaille de Dragon',    'description' => 'Le dragon était content de la donner. En échange d\'une excuse formelle.',         'is_generic' => 0, 'drop_chance' => 15, 'base_value' => 120],
            ['zone_id' => $volcanId, 'slug' => 'lave_solidifiee',   'name' => 'Lave Solidifiée',      'description' => 'De la lave qui a refroidi. Prend 3 minutes. Ou 10 000 ans. Variable.',            'is_generic' => 0, 'drop_chance' => 25, 'base_value' => 85],

            // ── Zone 8 — La Capitale des Incompétents ──
            ['zone_id' => $capitId, 'slug' => 'medaille_guilde',    'name' => 'Médaille de Guilde',    'description' => 'Décernée pour des services rendus à la Guilde. La Guilde nie avoir décerné ça.',  'is_generic' => 0, 'drop_chance' => 20, 'base_value' => 150],
            ['zone_id' => $capitId, 'slug' => 'or_corrompu',        'name' => 'Or Corrompu',           'description' => 'Légèrement différent de l\'or normal. La différence est légale. À peine.',       'is_generic' => 0, 'drop_chance' => 25, 'base_value' => 130],
            ['zone_id' => $capitId, 'slug' => 'parchemin_officiel', 'name' => 'Parchemin Officiel',    'description' => 'Signé, tamponné, certifié. Par qui ? Mystère.',                                   'is_generic' => 0, 'drop_chance' => 30, 'base_value' => 110],
        ];

        foreach ($materials as $mat) {
            DB::table('materials')->updateOrInsert(['slug' => $mat['slug']], $mat);
        }
    }
}
