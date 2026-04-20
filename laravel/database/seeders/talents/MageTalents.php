<?php

namespace Database\Seeders\Talents;

/**
 * Talents du Mage — 3 branches × 7 talents.
 * GDD §2. Branches : Élémentaliste (offensive), Arcaniste (defensive), Instabilité (defaut).
 */
class MageTalents
{
    public static function talents(int $classId): array
    {
        $base = ['class_id' => $classId, 'prerequisite_talent_id' => null];
        $t = [];

        // ── Branche A : Élémentaliste (DPS mono) — coûts 1,2 | 2,1 | 2,2,3 ────
        $t[] = $base + ['name' => 'Concentration', 'description' => 'INT +10%.', 'branch' => 'offensive', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['stat' => 'int', 'bonus_percent' => 10])];
        $t[] = $base + ['name' => 'Trait de Feu', 'description' => 'Dégâts magiques à 160% de l\'INT. CD : 3 tours.', 'branch' => 'offensive', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'spell_single', 'damage_pct_int' => 160, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Pénétration Arcanique', 'description' => 'Les sorts ignorent 20% supplémentaires de la résistance magique.', 'branch' => 'offensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'magic_pen', 'pen_pct' => 20])];
        $t[] = $base + ['name' => 'Résonance', 'description' => 'Si le sort tue la cible, 40% des dégâts excédentaires se propagent à un autre ennemi.', 'branch' => 'offensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'resonance', 'overkill_spread_pct' => 40])];
        $t[] = $base + ['name' => 'Canalisation', 'description' => 'Skip un tour pour doubler les dégâts du prochain sort. CD : 4 tours.', 'branch' => 'offensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'channel', 'next_spell_multiplier' => 2, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Embrasement', 'description' => 'Les sorts ont 25% de chance d\'infliger "En feu" (3 tours).', 'branch' => 'offensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'ignite', 'chance_pct' => 25, 'duration' => 3])];
        $t[] = $base + ['name' => 'Archimage', 'description' => 'INT +20%. Les sorts critiques lancent automatiquement un second sort gratuit à 50% des dégâts.', 'branch' => 'offensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'stat' => 'int', 'bonus_percent' => 20, 'crit_followup_pct' => 50])];

        // ── Branche B : Arcaniste (AoE / Contrôle) — coûts 2,1 | 2,1 | 2,2,3 ──
        $t[] = $base + ['name' => 'Onde de Choc', 'description' => 'Dégâts magiques à 70% de l\'INT sur tous les ennemis. CD : 4 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 1, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'aoe_spell', 'damage_pct_int' => 70, 'cooldown' => 4])];
        $t[] = $base + ['name' => 'Gel', 'description' => 'Ralentit une cible pendant 2 tours. CD : 3 tours.', 'branch' => 'defensive', 'tier' => 1, 'position' => 2, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'slow', 'duration' => 2, 'cooldown' => 3])];
        $t[] = $base + ['name' => 'Tempête Arcanique', 'description' => 'Les AoE gagnent +15% de dégâts.', 'branch' => 'defensive', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'aoe_boost', 'bonus_pct' => 15])];
        $t[] = $base + ['name' => 'Chaîne d\'Éclairs', 'description' => 'Touche 3 ennemis à 60% puis 40% puis 20% de l\'INT. CD : 5 tours.', 'branch' => 'defensive', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'chain_lightning', 'chain_pcts' => [60, 40, 20], 'cooldown' => 5])];
        $t[] = $base + ['name' => 'Silence', 'description' => 'Empêche un ennemi d\'utiliser des compétences pendant 2 tours. CD : 6 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'silence', 'duration' => 2, 'cooldown' => 6])];
        $t[] = $base + ['name' => 'Bouclier de Mana', 'description' => 'Sous 30% PV, bouclier absorbant = 50% INT pendant 2 tours.', 'branch' => 'defensive', 'tier' => 3, 'position' => 6, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'mana_shield', 'trigger_hp_pct' => 30, 'shield_pct_int' => 50, 'duration' => 2])];
        $t[] = $base + ['name' => 'Maître du Chaos', 'description' => 'Les AoE ont 15% de chance de déclencher un effet aléatoire : Étourdi, Ralenti, En feu, ou Terrifié.', 'branch' => 'defensive', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'chaos_procs', 'chance_pct' => 15, 'effects' => ['stun', 'slow', 'ignite', 'terror']])];

        // ── Branche C : Instabilité (Défaut) — coûts 1,2 | 2,1 | 2,1,3 ────────
        $t[] = $base + ['name' => 'Magie Instable', 'description' => 'Variance des sorts élargie : 70-140% au lieu de 90-110%.', 'branch' => 'defaut', 'tier' => 1, 'position' => 1, 'cost' => 1, 'required_points_in_branch' => 0, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'variance_widen', 'min_pct' => 70, 'max_pct' => 140])];
        $t[] = $base + ['name' => 'Siphon d\'Erreur', 'description' => 'Quand un trait négatif se déclenche, le Mage récupère 10% de ses PV max.', 'branch' => 'defaut', 'tier' => 1, 'position' => 2, 'cost' => 2, 'required_points_in_branch' => 0, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'on_trait_heal', 'heal_pct_max' => 10])];
        $t[] = $base + ['name' => 'Explosion Involontaire', 'description' => 'Quand le Mage est étourdi ou endormi par son trait, une explosion inflige 80% de l\'INT à tous les ennemis.', 'branch' => 'defaut', 'tier' => 2, 'position' => 3, 'cost' => 2, 'required_points_in_branch' => 3, 'talent_type' => 'reactif', 'effect_data' => json_encode(['action' => 'stunned_explode', 'damage_pct_int' => 80])];
        $t[] = $base + ['name' => 'Distorsion Réelle', 'description' => '10% de chance que les dégâts reçus soient redirigés vers un ennemi aléatoire.', 'branch' => 'defaut', 'tier' => 2, 'position' => 4, 'cost' => 1, 'required_points_in_branch' => 3, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'redirect', 'chance_pct' => 10])];
        $t[] = $base + ['name' => 'Faille Temporelle', 'description' => 'Rejoue le dernier tour du Mage (même sort, même cible). CD : 8 tours.', 'branch' => 'defaut', 'tier' => 3, 'position' => 5, 'cost' => 2, 'required_points_in_branch' => 6, 'talent_type' => 'actif', 'effect_data' => json_encode(['action' => 'time_rewind', 'cooldown' => 8])];
        $t[] = $base + ['name' => 'Éruption de Trait', 'description' => 'Trait +10% chance. Chaque déclenchement : INT +8% pour le combat.', 'branch' => 'defaut', 'tier' => 3, 'position' => 6, 'cost' => 1, 'required_points_in_branch' => 6, 'talent_type' => 'passif', 'effect_data' => json_encode(['action' => 'trait_scaling', 'trait_chance_bonus' => 10, 'int_per_trigger_pct' => 8])];
        $t[] = $base + ['name' => 'Bombe à Retardement', 'description' => 'Quand le Mage meurt, explose : 200% INT à tous les ennemis. Si ça tue au moins un ennemi, le Mage revient avec 1 PV.', 'branch' => 'defaut', 'tier' => 3, 'position' => 7, 'cost' => 3, 'required_points_in_branch' => 6, 'talent_type' => 'reactif', 'effect_data' => json_encode(['capstone' => true, 'action' => 'death_explosion', 'damage_pct_int' => 200, 'revive_if_kill' => true, 'revive_hp' => 1])];

        return $t;
    }
}
