<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EncounterGroupSeeder extends Seeder
{
    public function run(): void
    {
        $prairieId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $ratId = DB::table('monsters')->where('slug', 'rat_peureux')->value('id');
        $slimeId = DB::table('monsters')->where('slug', 'slime_vert')->value('id');
        $gobId = DB::table('monsters')->where('slug', 'gobelin_chapardeur')->value('id');
        $loupId = DB::table('monsters')->where('slug', 'loup_solitaire')->value('id');
        $epouvId = DB::table('monsters')->where('slug', 'epouvantail_anime')->value('id');
        $abeilleId = DB::table('monsters')->where('slug', 'abeille_geante')->value('id');
        $fermierId = DB::table('monsters')->where('slug', 'fermier_possede')->value('id');
        $taureauId = DB::table('monsters')->where('slug', 'taureau_pre_maudit')->value('id');

        // Zones 5-8
        $tourId    = DB::table('zones')->where('slug', 'tour_mage_distrait')->value('id');
        $cimetId   = DB::table('zones')->where('slug', 'cimetiere_syndique')->value('id');
        $volcanId  = DB::table('zones')->where('slug', 'volcan_dragon_retraite')->value('id');
        $capitId   = DB::table('zones')->where('slug', 'capitale_incompetents')->value('id');

        // Zone 5 monsters
        $balaiId    = DB::table('monsters')->where('slug', 'balai_enchante')->value('id');
        $livreId    = DB::table('monsters')->where('slug', 'livre_mordeur')->value('id');
        $elemInstId = DB::table('monsters')->where('slug', 'elementaire_instable')->value('id');
        $armureId   = DB::table('monsters')->where('slug', 'armure_animee')->value('id');
        $apprentiId = DB::table('monsters')->where('slug', 'apprenti_rate')->value('id');
        $gargId     = DB::table('monsters')->where('slug', 'gargouille')->value('id');
        $horlogeId  = DB::table('monsters')->where('slug', 'horloge_folle')->value('id');
        $familiarId = DB::table('monsters')->where('slug', 'familiar_rebelle')->value('id');
        $mageId     = DB::table('monsters')->where('slug', 'mage_distrait')->value('id');

        // Zone 6 monsters
        $squelSyndId = DB::table('monsters')->where('slug', 'squelette_syndique')->value('id');
        $zombieFoncId= DB::table('monsters')->where('slug', 'zombie_fonctionnaire')->value('id');
        $fantomePlId = DB::table('monsters')->where('slug', 'fantome_plaintif')->value('id');
        $gouleId     = DB::table('monsters')->where('slug', 'goule_affamee')->value('id');
        $vampireId   = DB::table('monsters')->where('slug', 'vampire_comptable')->value('id');
        $revenantId  = DB::table('monsters')->where('slug', 'revenant_armure')->value('id');
        $delegueId   = DB::table('monsters')->where('slug', 'delegue_syndical_morts')->value('id');
        $necroId     = DB::table('monsters')->where('slug', 'necromancien_retraite')->value('id');

        // Zone 7 monsters
        $elemFeuId  = DB::table('monsters')->where('slug', 'elementaire_feu_volcan')->value('id');
        $diablotinId= DB::table('monsters')->where('slug', 'diablotin_farceur')->value('id');
        $tortueId   = DB::table('monsters')->where('slug', 'tortue_lave')->value('id');
        $phenixId   = DB::table('monsters')->where('slug', 'phenix_mineur')->value('id');
        $forgeronId = DB::table('monsters')->where('slug', 'forgeron_damne')->value('id');
        $wyrmId     = DB::table('monsters')->where('slug', 'wyrm_lave')->value('id');
        $gardienId  = DB::table('monsters')->where('slug', 'gardien_volcan')->value('id');
        $dragonRetId= DB::table('monsters')->where('slug', 'dragon_retraite')->value('id');

        // Zone 8 monsters
        $gardeId    = DB::table('monsters')->where('slug', 'garde_corrompu')->value('id');
        $voleurId   = DB::table('monsters')->where('slug', 'voleur_grand_chemin')->value('id');
        $mageRueId  = DB::table('monsters')->where('slug', 'mage_rue')->value('id');
        $golemGId   = DB::table('monsters')->where('slug', 'golem_guilde')->value('id');
        $ratMutId   = DB::table('monsters')->where('slug', 'rat_egout_mutant')->value('id');
        $marchandId = DB::table('monsters')->where('slug', 'marchand_rival')->value('id');
        $assassinId = DB::table('monsters')->where('slug', 'assassin_guilde')->value('id');
        $statueId   = DB::table('monsters')->where('slug', 'statue_vivante')->value('id');
        $maitreId   = DB::table('monsters')->where('slug', 'maitre_guilde_zeros')->value('id');
        $maireId    = DB::table('monsters')->where('slug', 'maire_capitale')->value('id');

        // Zone 2
        $foretId = DB::table('zones')->where('slug', 'foret_elfes')->value('id');
        $elfArcherId = DB::table('monsters')->where('slug', 'elfe_archer_contrarie')->value('id');
        $feeMalvId = DB::table('monsters')->where('slug', 'fee_malveillante')->value('id');
        $loupBoisId = DB::table('monsters')->where('slug', 'loup_bois_enchante')->value('id');
        $plantePedId = DB::table('monsters')->where('slug', 'plante_carnivore_pedante')->value('id');
        $dryadeCId = DB::table('monsters')->where('slug', 'dryade_contrariee')->value('id');
        $araigneeId = DB::table('monsters')->where('slug', 'araignee_soie_magique')->value('id');
        $elaraId = DB::table('monsters')->where('slug', 'elara_elfe_vexee_mini')->value('id');
        $espritId = DB::table('monsters')->where('slug', 'esprit_foret_tres_enerve')->value('id');

        // Zone 3
        $minesId = DB::table('zones')->where('slug', 'mines_nain')->value('id');
        $ratMineId = DB::table('monsters')->where('slug', 'rat_mine_alcoolise')->value('id');
        $golemId = DB::table('monsters')->where('slug', 'golem_pierre_ebreche')->value('id');
        $koboldId = DB::table('monsters')->where('slug', 'kobold_mineur_syndique')->value('id');
        $chauveId = DB::table('monsters')->where('slug', 'chauve_souris_minerale')->value('id');
        $trollId = DB::table('monsters')->where('slug', 'troll_tunnels')->value('id');
        $cristalId = DB::table('monsters')->where('slug', 'cristal_gardien')->value('id');
        $thorinId = DB::table('monsters')->where('slug', 'thorin_ivre_furieux_mini')->value('id');
        $dragonId = DB::table('monsters')->where('slug', 'dragon_mines_retraite')->value('id');

        // Zone 4
        $maraisId = DB::table('zones')->where('slug', 'marais_bureaucratie')->value('id');
        $squelAdmId = DB::table('monsters')->where('slug', 'squelette_administratif')->value('id');
        $fantomeBurId = DB::table('monsters')->where('slug', 'fantome_bureaucrate')->value('id');
        $vaseId = DB::table('monsters')->where('slug', 'vase_marecageux_conscient')->value('id');
        $inspecId = DB::table('monsters')->where('slug', 'inspecteur_finances_mort_vivant')->value('id');
        $hydreId = DB::table('monsters')->where('slug', 'hydre_formulaires')->value('id');
        $licheId = DB::table('monsters')->where('slug', 'liche_comptable')->value('id');
        $directeurId = DB::table('monsters')->where('slug', 'directeur_impots_mini')->value('id');
        $krakenId = DB::table('monsters')->where('slug', 'kraken_comptable')->value('id');

        $groups = [
            // Niveau 1-2
            ['zone_id' => $prairieId, 'name' => 'Un rat', 'monster_ids' => json_encode([$ratId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 30, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Deux rats', 'monster_ids' => json_encode([$ratId, $ratId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Un slime', 'monster_ids' => json_encode([$slimeId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Rat et Slime', 'monster_ids' => json_encode([$ratId, $slimeId]), 'level_min' => 1, 'level_max' => 2, 'weight' => 25, 'is_boss_encounter' => 0],
            // Niveau 2-3
            ['zone_id' => $prairieId, 'name' => 'Gobelin seul', 'monster_ids' => json_encode([$gobId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Loup solitaire', 'monster_ids' => json_encode([$loupId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Gobelin et Rat', 'monster_ids' => json_encode([$gobId, $ratId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Deux Gobelins', 'monster_ids' => json_encode([$gobId, $gobId]), 'level_min' => 2, 'level_max' => 3, 'weight' => 25, 'is_boss_encounter' => 0],
            // Niveau 3-5
            ['zone_id' => $prairieId, 'name' => 'Épouvantail', 'monster_ids' => json_encode([$epouvId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Abeilles furieuses', 'monster_ids' => json_encode([$abeilleId, $abeilleId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Loup et Gobelin', 'monster_ids' => json_encode([$loupId, $gobId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Trio de gobelins', 'monster_ids' => json_encode([$gobId, $gobId, $gobId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $prairieId, 'name' => 'Épouvantail et Abeilles', 'monster_ids' => json_encode([$epouvId, $abeilleId]), 'level_min' => 3, 'level_max' => 5, 'weight' => 25, 'is_boss_encounter' => 0],
            // Mini-boss (poids faible = rare)
            ['zone_id' => $prairieId, 'name' => 'Le Fermier Possédé', 'monster_ids' => json_encode([$fermierId]), 'level_min' => 4, 'level_max' => 5, 'weight' => 5, 'is_boss_encounter' => 0],
            // Boss prairie (déclenché manuellement)
            ['zone_id' => $prairieId, 'name' => 'Le Taureau du Pré Maudit', 'monster_ids' => json_encode([$taureauId]), 'level_min' => 5, 'level_max' => 5, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 2 : Forêt des Elfes Vexés ──
            // Niveau 5-7
            ['zone_id' => $foretId, 'name' => 'Elfe archer seul', 'monster_ids' => json_encode([$elfArcherId]), 'level_min' => 5, 'level_max' => 7, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Fée malveillante', 'monster_ids' => json_encode([$feeMalvId]), 'level_min' => 5, 'level_max' => 7, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Elfe et Fée', 'monster_ids' => json_encode([$elfArcherId, $feeMalvId]), 'level_min' => 5, 'level_max' => 7, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Loup des bois', 'monster_ids' => json_encode([$loupBoisId]), 'level_min' => 5, 'level_max' => 7, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Deux elfes archers', 'monster_ids' => json_encode([$elfArcherId, $elfArcherId]), 'level_min' => 6, 'level_max' => 7, 'weight' => 15, 'is_boss_encounter' => 0],
            // Niveau 7-10
            ['zone_id' => $foretId, 'name' => 'Plante carnivore', 'monster_ids' => json_encode([$plantePedId]), 'level_min' => 7, 'level_max' => 10, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Dryade contrariée', 'monster_ids' => json_encode([$dryadeCId]), 'level_min' => 7, 'level_max' => 10, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Araignée de soie', 'monster_ids' => json_encode([$araigneeId]), 'level_min' => 8, 'level_max' => 10, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Loup et Plante', 'monster_ids' => json_encode([$loupBoisId, $plantePedId]), 'level_min' => 8, 'level_max' => 10, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'Dryade et Araignée', 'monster_ids' => json_encode([$dryadeCId, $araigneeId]), 'level_min' => 9, 'level_max' => 10, 'weight' => 10, 'is_boss_encounter' => 0],
            // Mini-boss et boss forêt
            ['zone_id' => $foretId, 'name' => 'Elara l\'Elfe Vexée', 'monster_ids' => json_encode([$elaraId]), 'level_min' => 10, 'level_max' => 11, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $foretId, 'name' => 'L\'Esprit de la Forêt', 'monster_ids' => json_encode([$espritId]), 'level_min' => 12, 'level_max' => 12, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 3 : Mines du Nain Ivre ──
            // Niveau 12-15
            ['zone_id' => $minesId, 'name' => 'Rats de mine', 'monster_ids' => json_encode([$ratMineId, $ratMineId]), 'level_min' => 12, 'level_max' => 14, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Golem ébréché', 'monster_ids' => json_encode([$golemId]), 'level_min' => 12, 'level_max' => 14, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Kobolds syndiqués', 'monster_ids' => json_encode([$koboldId, $koboldId]), 'level_min' => 13, 'level_max' => 15, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Chauve-souris minérale', 'monster_ids' => json_encode([$chauveId]), 'level_min' => 14, 'level_max' => 15, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Golem et Chauve-souris', 'monster_ids' => json_encode([$golemId, $chauveId]), 'level_min' => 14, 'level_max' => 15, 'weight' => 15, 'is_boss_encounter' => 0],
            // Niveau 15-18
            ['zone_id' => $minesId, 'name' => 'Troll des tunnels', 'monster_ids' => json_encode([$trollId]), 'level_min' => 15, 'level_max' => 18, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Cristal gardien', 'monster_ids' => json_encode([$cristalId]), 'level_min' => 16, 'level_max' => 18, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Kobold et Troll', 'monster_ids' => json_encode([$koboldId, $trollId]), 'level_min' => 16, 'level_max' => 18, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Cristal et Chauve-souris', 'monster_ids' => json_encode([$cristalId, $chauveId]), 'level_min' => 17, 'level_max' => 18, 'weight' => 15, 'is_boss_encounter' => 0],
            // Mini-boss et boss mines
            ['zone_id' => $minesId, 'name' => 'Thorin Ivre-Furieux', 'monster_ids' => json_encode([$thorinId]), 'level_min' => 18, 'level_max' => 19, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $minesId, 'name' => 'Le Dragon des Mines', 'monster_ids' => json_encode([$dragonId]), 'level_min' => 20, 'level_max' => 20, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 4 : Marais de la Bureaucratie ──
            // Niveau 20-24
            ['zone_id' => $maraisId, 'name' => 'Squelette administratif', 'monster_ids' => json_encode([$squelAdmId]), 'level_min' => 20, 'level_max' => 23, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Fantôme bureaucrate', 'monster_ids' => json_encode([$fantomeBurId]), 'level_min' => 20, 'level_max' => 23, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Vase marécageux', 'monster_ids' => json_encode([$vaseId]), 'level_min' => 21, 'level_max' => 24, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Squelette et Fantôme', 'monster_ids' => json_encode([$squelAdmId, $fantomeBurId]), 'level_min' => 22, 'level_max' => 24, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Vase et Squelette', 'monster_ids' => json_encode([$vaseId, $squelAdmId]), 'level_min' => 23, 'level_max' => 24, 'weight' => 10, 'is_boss_encounter' => 0],
            // Niveau 24-28
            ['zone_id' => $maraisId, 'name' => 'Inspecteur des finances', 'monster_ids' => json_encode([$inspecId]), 'level_min' => 24, 'level_max' => 28, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Hydre des formulaires', 'monster_ids' => json_encode([$hydreId]), 'level_min' => 25, 'level_max' => 28, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Liche-Comptable', 'monster_ids' => json_encode([$licheId]), 'level_min' => 26, 'level_max' => 28, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Hydre et Inspecteur', 'monster_ids' => json_encode([$hydreId, $inspecId]), 'level_min' => 27, 'level_max' => 28, 'weight' => 10, 'is_boss_encounter' => 0],
            // Mini-boss et boss marais
            ['zone_id' => $maraisId, 'name' => 'Le Directeur des Impôts', 'monster_ids' => json_encode([$directeurId]), 'level_min' => 28, 'level_max' => 29, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $maraisId, 'name' => 'Le Kraken Comptable', 'monster_ids' => json_encode([$krakenId]), 'level_min' => 30, 'level_max' => 30, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 5 : Tour du Mage Distrait ──
            ['zone_id' => $tourId, 'name' => 'Balai enchanté', 'monster_ids' => json_encode([$balaiId]), 'level_min' => 30, 'level_max' => 33, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Livre mordeur', 'monster_ids' => json_encode([$livreId]), 'level_min' => 30, 'level_max' => 33, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Balai et Livre', 'monster_ids' => json_encode([$balaiId, $livreId]), 'level_min' => 31, 'level_max' => 34, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Élémentaire instable', 'monster_ids' => json_encode([$elemInstId]), 'level_min' => 33, 'level_max' => 36, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Armure animée', 'monster_ids' => json_encode([$armureId]), 'level_min' => 34, 'level_max' => 37, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Apprenti raté', 'monster_ids' => json_encode([$apprentiId]), 'level_min' => 34, 'level_max' => 38, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Gargouille', 'monster_ids' => json_encode([$gargId]), 'level_min' => 36, 'level_max' => 40, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Horloge folle', 'monster_ids' => json_encode([$horlogeId]), 'level_min' => 37, 'level_max' => 41, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Élémentaire et Apprenti', 'monster_ids' => json_encode([$elemInstId, $apprentiId]), 'level_min' => 36, 'level_max' => 40, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Le Familiar Rebelle', 'monster_ids' => json_encode([$familiarId]), 'level_min' => 37, 'level_max' => 40, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $tourId, 'name' => 'Le Mage Distrait', 'monster_ids' => json_encode([$mageId]), 'level_min' => 42, 'level_max' => 42, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 6 : Cimetière Syndiqué ──
            ['zone_id' => $cimetId, 'name' => 'Squelette syndiqué', 'monster_ids' => json_encode([$squelSyndId]), 'level_min' => 42, 'level_max' => 46, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Zombie fonctionnaire', 'monster_ids' => json_encode([$zombieFoncId]), 'level_min' => 42, 'level_max' => 46, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Squelette et Zombie', 'monster_ids' => json_encode([$squelSyndId, $zombieFoncId]), 'level_min' => 44, 'level_max' => 47, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Fantôme plaintif', 'monster_ids' => json_encode([$fantomePlId]), 'level_min' => 45, 'level_max' => 49, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Goule affamée', 'monster_ids' => json_encode([$gouleId]), 'level_min' => 45, 'level_max' => 50, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Vampire comptable', 'monster_ids' => json_encode([$vampireId]), 'level_min' => 47, 'level_max' => 52, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Revenant en armure', 'monster_ids' => json_encode([$revenantId]), 'level_min' => 49, 'level_max' => 53, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Goule et Fantôme', 'monster_ids' => json_encode([$gouleId, $fantomePlId]), 'level_min' => 48, 'level_max' => 53, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Le Délégué Syndical', 'monster_ids' => json_encode([$delegueId]), 'level_min' => 50, 'level_max' => 53, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $cimetId, 'name' => 'Le Nécromancien Retraité', 'monster_ids' => json_encode([$necroId]), 'level_min' => 55, 'level_max' => 55, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 7 : Volcan du Dragon Retraité ──
            ['zone_id' => $volcanId, 'name' => 'Élémentaire de feu', 'monster_ids' => json_encode([$elemFeuId]), 'level_min' => 55, 'level_max' => 59, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Diablotin farceur', 'monster_ids' => json_encode([$diablotinId]), 'level_min' => 55, 'level_max' => 59, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Diablotin et Élémentaire', 'monster_ids' => json_encode([$diablotinId, $elemFeuId]), 'level_min' => 57, 'level_max' => 61, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Tortue de lave', 'monster_ids' => json_encode([$tortueId]), 'level_min' => 57, 'level_max' => 62, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Phénix mineur', 'monster_ids' => json_encode([$phenixId]), 'level_min' => 59, 'level_max' => 64, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Forgeron damné', 'monster_ids' => json_encode([$forgeronId]), 'level_min' => 61, 'level_max' => 66, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Wyrm de lave', 'monster_ids' => json_encode([$wyrmId]), 'level_min' => 63, 'level_max' => 68, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Phénix et Forgeron', 'monster_ids' => json_encode([$phenixId, $forgeronId]), 'level_min' => 63, 'level_max' => 68, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Le Gardien du Volcan', 'monster_ids' => json_encode([$gardienId]), 'level_min' => 65, 'level_max' => 68, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $volcanId, 'name' => 'Le Dragon Retraité', 'monster_ids' => json_encode([$dragonRetId]), 'level_min' => 70, 'level_max' => 70, 'weight' => 0, 'is_boss_encounter' => 1],

            // ── Zone 8 : Capitale des Incompétents ──
            ['zone_id' => $capitId, 'name' => 'Garde corrompu', 'monster_ids' => json_encode([$gardeId]), 'level_min' => 70, 'level_max' => 74, 'weight' => 25, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Voleur de grand chemin', 'monster_ids' => json_encode([$voleurId]), 'level_min' => 70, 'level_max' => 74, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Garde et Voleur', 'monster_ids' => json_encode([$gardeId, $voleurId]), 'level_min' => 72, 'level_max' => 76, 'weight' => 20, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Mage de rue', 'monster_ids' => json_encode([$mageRueId]), 'level_min' => 72, 'level_max' => 76, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Golem de la guilde', 'monster_ids' => json_encode([$golemGId]), 'level_min' => 74, 'level_max' => 78, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Rat mutant', 'monster_ids' => json_encode([$ratMutId]), 'level_min' => 74, 'level_max' => 78, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Assassin de la guilde', 'monster_ids' => json_encode([$assassinId]), 'level_min' => 76, 'level_max' => 82, 'weight' => 15, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Marchand rival', 'monster_ids' => json_encode([$marchandId]), 'level_min' => 76, 'level_max' => 82, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Statue vivante', 'monster_ids' => json_encode([$statueId]), 'level_min' => 78, 'level_max' => 83, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Assassin et Mage de rue', 'monster_ids' => json_encode([$assassinId, $mageRueId]), 'level_min' => 78, 'level_max' => 83, 'weight' => 10, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Le Maître de la Guilde', 'monster_ids' => json_encode([$maitreId]), 'level_min' => 78, 'level_max' => 83, 'weight' => 5, 'is_boss_encounter' => 0],
            ['zone_id' => $capitId, 'name' => 'Le Maire de la Capitale', 'monster_ids' => json_encode([$maireId]), 'level_min' => 85, 'level_max' => 85, 'weight' => 0, 'is_boss_encounter' => 1],
        ];

        foreach ($groups as $group) {
            DB::table('encounter_groups')->insert($group);
        }
    }
}
