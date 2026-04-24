<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Combat général
            ['COMBAT_MAX_TURNS', 15, 'Nombre maximum de tours par combat'],
            ['MIN_DAMAGE', 1, 'Dégâts minimum garantis'],
            ['VARIANCE_MIN', 90, 'Variance minimale des dégâts (%)'],
            ['VARIANCE_MAX', 110, 'Variance maximale des dégâts (%)'],
            ['SPEED_BASE', 100, 'Constante de base pour le calcul d\'esquive'],

            // Statistiques de base et scaling
            ['LEVEL_SCALING_FACTOR', 3, 'Points de stat gagnés par niveau (stat primaire)'],
            ['LEVEL_SCALING_SECONDARY', 1, 'Points de stat gagnés par niveau (stat secondaire)'],

            // Critiques
            ['CRIT_BASE_CHANCE', 5, 'Chance de critique de base (%)'],
            ['CRIT_DAMAGE_MULTIPLIER', 150, 'Multiplicateur de dégâts critiques (%)'],
            ['CRIT_CAP', 50, 'Cap maximum de chance critique (%)'],

            // Esquive et défense
            ['DODGE_BASE_CHANCE', 3, 'Chance d\'esquive de base (%)'],
            ['DODGE_CAP', 40, 'Cap maximum d\'esquive (%)'],
            ['DEF_SOFT_CAP', 200, 'Soft cap pour la réduction de défense'],
            ['DEF_HARD_CAP', 75, 'Hard cap maximum de réduction de défense (%)'],

            // XP et niveaux
            ['XP_BASE_PER_KILL', 10, 'XP de base par kill'],
            ['XP_LEVEL_MULTIPLIER', 2, 'XP bonus par niveau de l\'ennemi'],
            ['XP_LEVEL_DIFF_PENALTY', 5, 'Pénalité XP par niveau de différence (ennemi plus faible, %)'],
            ['XP_LEVEL_DIFF_BONUS', 10, 'Bonus XP par niveau de différence (ennemi plus fort, %)'],
            ['XP_TO_LEVEL_BASE', 100, 'XP requise pour atteindre le niveau 2'],
            ['XP_TO_LEVEL_EXPONENT', 115, 'Exposant de la courbe XP (× 1/100 par itération)'],

            // Idle et offline
            ['OFFLINE_EFFICIENCY', 75, 'Efficacité du calcul offline (%)'],
            ['OFFLINE_MAX_HOURS', 12, 'Cap maximum des heures offline'],
            ['HEAL_BETWEEN_FIGHTS', 30, 'Régénération HP entre les combats idle (%)'],

            // Loot
            ['LOOT_DROP_CHANCE', 60, 'Chance de base de drop d\'objet (%)'],
            ['LOOT_RARITY_COMMUN', 50, 'Poids de rareté: Commun'],
            ['LOOT_RARITY_PEU_COMMUN', 25, 'Poids de rareté: Peu Commun'],
            ['LOOT_RARITY_RARE', 14, 'Poids de rareté: Rare'],
            ['LOOT_RARITY_EPIQUE', 7, 'Poids de rareté: Épique'],
            ['LOOT_RARITY_LEGENDAIRE', 3, 'Poids de rareté: Légendaire'],
            ['LOOT_RARITY_WTF', 1, 'Poids de rareté: WTF'],
            ['LOOT_LEVEL_RANGE', 2, 'Variation du niveau d\'objet autour du niveau de zone'],
            ['LOOT_SELL_PERCENT', 30, 'Pourcentage de la valeur de vente vs valeur réelle'],
            ['LOOT_STAT_VARIANCE', 15, 'Variance des stats d\'objet générées (%)'],
            ['LOOT_AI_GENERATION_MIN_RARITY', 3, 'Rareté minimum pour déclencher Gemini (3 = Rare)'],
            ['MONSTER_ELITE_CHANCE', 8, 'Probabilité qu\'un monstre soit élite (%)'],
            ['MONSTER_ELITE_STAT_MULT', 150, 'Multiplicateur de stats global pour les élites (%)'],
            ['MONSTER_ELITE_XP_BONUS', 75, 'Bonus XP pour les monstres élites (%)'],
            ['MONSTER_ELITE_LOOT_BONUS', 75, 'Bonus de chance de loot pour les élites (%)'],
            ['MONSTER_SKILL_COOLDOWN_MIN', 2, 'CD minimum des compétences de monstres (tours)'],
            ['MONSTER_SKILL_COOLDOWN_MAX', 5, 'CD maximum des compétences de monstres (tours)'],
            ['BOSS_PHASE_HP_THRESHOLD', 50, '% PV sous lequel le boss passe en phase 2'],
            ['WORLD_BOSS_MECHANIC_INTERVAL', 3, 'Tours entre chaque activation de mécanique spéciale'],

            // Traits — Couard (chance décroît avec le niveau, GDD §2.1)
            ['TRAIT_COUARD_CHANCE', 15, 'Chance de déclenchement du trait Couard (%)'],
            ['TRAIT_COUARD_CHANCE_L26', 13, 'Chance Couard niveau 26-50 (%)'],
            ['TRAIT_COUARD_CHANCE_L51', 11, 'Chance Couard niveau 51-75 (%)'],
            ['TRAIT_COUARD_CHANCE_L76', 10, 'Chance Couard niveau 76+ (%)'],

            // Traits — Narcoleptique (chance décroît, GDD §2.2)
            ['TRAIT_NARCOLEPTIQUE_CHANCE', 10, 'Chance de déclenchement Narcoleptique (%)'],
            ['TRAIT_NARCOLEPTIQUE_CHANCE_L26', 9, 'Chance Narcoleptique niveau 26-50 (%)'],
            ['TRAIT_NARCOLEPTIQUE_CHANCE_L51', 8, 'Chance Narcoleptique niveau 51-75 (%)'],
            ['TRAIT_NARCOLEPTIQUE_CHANCE_L76', 7, 'Chance Narcoleptique niveau 76+ (%)'],
            ['TRAIT_NARCOLEPTIQUE_DURATION', 2, 'Durée du sommeil en tours'],
            ['TRAIT_NARCOLEPTIQUE_WAKE_CHANCE', 50, 'Chance de se réveiller si touché (%)'],
            ['TRAIT_NARCOLEPTIQUE_WAKE_VIT_BONUS', 10, 'Bonus VIT au réveil (%)'],

            // Traits — Kleptomane (chance décroît, GDD §2.3)
            ['TRAIT_KLEPTOMANE_CHANCE', 20, 'Chance de vol de loot Kleptomane (%)'],
            ['TRAIT_KLEPTOMANE_CHANCE_L26', 18, 'Chance Kleptomane niveau 26-50 (%)'],
            ['TRAIT_KLEPTOMANE_CHANCE_L51', 16, 'Chance Kleptomane niveau 51-75 (%)'],
            ['TRAIT_KLEPTOMANE_CHANCE_L76', 15, 'Chance Kleptomane niveau 76+ (%)'],
            ['TRAIT_KLEPTOMANE_XP_STEAL_PCT', 10, '% XP volé à un allié (%)'],
            ['TRAIT_KLEPTOMANE_LOOT_STEAL_CHANCE', 30, 'Chance de s\'attribuer un loot Rare+ (%)'],

            // Traits — Pyromane (chance CONSTANTE, dégâts augmentent, GDD §2.4)
            ['TRAIT_PYROMANE_CHANCE', 20, 'Chance de déclenchement Pyromane (%)'],
            ['TRAIT_PYROMANE_CHANCE_L26', 20, 'Chance Pyromane niveau 26-50 (% — constante)'],
            ['TRAIT_PYROMANE_CHANCE_L51', 20, 'Chance Pyromane niveau 51-75 (% — constante)'],
            ['TRAIT_PYROMANE_CHANCE_L76', 20, 'Chance Pyromane niveau 76+ (% — constante)'],
            ['TRAIT_PYROMANE_DAMAGE_PERCENT', 8, 'Dégâts de feu Pyromane niveau 1-25 (% ATQ)'],
            ['TRAIT_PYROMANE_DAMAGE_L26', 10, 'Dégâts de feu Pyromane niveau 26-50 (% ATQ)'],
            ['TRAIT_PYROMANE_DAMAGE_L51', 12, 'Dégâts de feu Pyromane niveau 51-75 (% ATQ)'],
            ['TRAIT_PYROMANE_DAMAGE_L76', 15, 'Dégâts de feu Pyromane niveau 76+ (% ATQ)'],
            ['TRAIT_PYROMANE_FRIENDLY_FIRE', 1, 'Le Pyromane blesse aussi ses alliés (1=oui)'],
            ['TRAIT_PYROMANE_IGNITE_CHANCE', 30, 'Chance d\'appliquer "En feu" sur chaque cible (%)'],

            // Traits — Allergique (chance décroît, GDD §2.5)
            ['TRAIT_ALLERGIQUE_CHANCE', 25, 'Chance de déclenchement Allergique (%)'],
            ['TRAIT_ALLERGIQUE_CHANCE_L26', 22, 'Chance Allergique niveau 26-50 (%)'],
            ['TRAIT_ALLERGIQUE_CHANCE_L51', 20, 'Chance Allergique niveau 51-75 (%)'],
            ['TRAIT_ALLERGIQUE_CHANCE_L76', 18, 'Chance Allergique niveau 76+ (%)'],
            ['TRAIT_ALLERGIQUE_MALUS', 20, 'Malus de stats en zone magique (%)'],
            ['TRAIT_ALLERGIQUE_ENEMY_HIT_BONUS', 10, 'Bonus chance de toucher ennemi après éternuement (%)'],
            ['TRAIT_ALLERGIQUE_CUMUL_THRESHOLD', 3, 'Nombre d\'éternuements avant malus permanent'],

            // Traits — Philosophe (chance quasi-stable, INT buff augmente, GDD §2.6)
            ['TRAIT_PHILOSOPHE_CHANCE', 12, 'Chance de déclenchement Philosophe (%)'],
            ['TRAIT_PHILOSOPHE_CHANCE_L26', 12, 'Chance Philosophe niveau 26-50 (%)'],
            ['TRAIT_PHILOSOPHE_CHANCE_L51', 11, 'Chance Philosophe niveau 51-75 (%)'],
            ['TRAIT_PHILOSOPHE_CHANCE_L76', 10, 'Chance Philosophe niveau 76+ (%)'],
            ['TRAIT_PHILOSOPHE_INT_BUFF', 5, 'Buff INT par déclenchement niveau 1-25 (%, cumulable)'],
            ['TRAIT_PHILOSOPHE_INT_BUFF_L26', 6, 'Buff INT par déclenchement niveau 26-50 (%)'],
            ['TRAIT_PHILOSOPHE_INT_BUFF_L51', 7, 'Buff INT par déclenchement niveau 51-75 (%)'],
            ['TRAIT_PHILOSOPHE_INT_BUFF_L76', 8, 'Buff INT par déclenchement niveau 76+ (%)'],

            // Traits — Gourmand (chance décroît, GDD §2.7)
            ['TRAIT_GOURMAND_CHANCE', 25, 'Chance de consommer une potion Gourmand (%)'],
            ['TRAIT_GOURMAND_CHANCE_L26', 22, 'Chance Gourmand niveau 26-50 (%)'],
            ['TRAIT_GOURMAND_CHANCE_L51', 20, 'Chance Gourmand niveau 51-75 (%)'],
            ['TRAIT_GOURMAND_CHANCE_L76', 18, 'Chance Gourmand niveau 76+ (%)'],
            ['TRAIT_GOURMAND_POTION_HEAL_PCT', 30, 'Pourcentage de PV récupéré si PV non max (%)'],

            // Traits — Superstitieux (chance décroît, GDD §2.8)
            ['TRAIT_SUPERSTITIEUX_BLOCK_CHANCE', 15, 'Chance de refus d\'entrer dans un donjon (%)'],
            ['TRAIT_SUPERSTITIEUX_BLOCK_CHANCE_L26', 13, 'Chance Superstitieux niveau 26-50 (%)'],
            ['TRAIT_SUPERSTITIEUX_BLOCK_CHANCE_L51', 12, 'Chance Superstitieux niveau 51-75 (%)'],
            ['TRAIT_SUPERSTITIEUX_BLOCK_CHANCE_L76', 10, 'Chance Superstitieux niveau 76+ (%)'],
            ['TRAIT_SUPERSTITIEUX_CONVICTION_PENALTY', 10, 'Malus stats si convaincu par paiement (%)'],

            // Traits — Mythomane (variance décroît, GDD §2.9)
            ['TRAIT_MYTHOMANE_VARIANCE', 20, 'Variance d\'affichage des stats niveau 1-25 (%)'],
            ['TRAIT_MYTHOMANE_VARIANCE_L26', 18, 'Variance Mythomane niveau 26-50 (%)'],
            ['TRAIT_MYTHOMANE_VARIANCE_L51', 15, 'Variance Mythomane niveau 51-75 (%)'],
            ['TRAIT_MYTHOMANE_VARIANCE_L76', 12, 'Variance Mythomane niveau 76+ (%)'],

            // Traits — Pacifiste (chance et seuil décroissent, GDD §2.10)
            ['TRAIT_PACIFISTE_CHANCE', 15, 'Chance de refus d\'attaquer Pacifiste (%)'],
            ['TRAIT_PACIFISTE_CHANCE_L26', 13, 'Chance Pacifiste niveau 26-50 (%)'],
            ['TRAIT_PACIFISTE_CHANCE_L51', 12, 'Chance Pacifiste niveau 51-75 (%)'],
            ['TRAIT_PACIFISTE_CHANCE_L76', 10, 'Chance Pacifiste niveau 76+ (%)'],
            ['TRAIT_PACIFISTE_THRESHOLD', 30, 'Seuil de HP ennemi niveau 1-25 (%)'],
            ['TRAIT_PACIFISTE_THRESHOLD_L26', 28, 'Seuil HP Pacifiste niveau 26-50 (%)'],
            ['TRAIT_PACIFISTE_THRESHOLD_L51', 25, 'Seuil HP Pacifiste niveau 51-75 (%)'],
            ['TRAIT_PACIFISTE_THRESHOLD_L76', 20, 'Seuil HP Pacifiste niveau 76+ (%)'],

            // Boss et monde
            ['BOSS_STAT_MULTIPLIER', 300, 'Multiplicateur de stats des boss (%)'],
            ['MINI_BOSS_STAT_MULTIPLIER', 150, 'Multiplicateur de stats des mini-boss (%)'],
            ['MULTI_TARGET_PENALTY', 70, 'Efficacité des attaques multi-cibles (%)'],
            ['FLEE_BASE_CHANCE', 50, 'Chance de base de fuite réussie (%)'],
            ['WORLD_BOSS_HP_PER_PLAYER', 5000, 'HP du boss mondial par joueur actif'],
            ['WORLD_BOSS_DAMAGE_SCALE', 10, 'Échelle de dégâts des attaques sur le boss mondial (team_power × scale / 100)'],
            ['WORLD_BOSS_ATTACK_COOLDOWN', 300, 'Cooldown entre deux attaques sur le boss mondial (secondes)'],
            ['WORLD_BOSS_REWARD_BASE', 500, 'Récompense de base en or pour participation au boss mondial'],
            ['WORLD_BOSS_NPC_ATTACKS_PER_CYCLE', 5, 'Nombre d\'attaques NPC simulées toutes les 2h sur le boss mondial'],

            // IA et budget
            ['AI_DAILY_BUDGET_LIMIT', 5000, 'Limite de budget IA journalier (unités arbitraires, image=50, texte=10)'],
            ['AI_ENABLED', 0, 'IA activée (0=fallback statique uniquement)'],

            // Taverne
            ['TAVERN_REFRESH_HOURS', 4, 'Heures entre les rafraîchissements de la taverne'],
            ['TAVERN_MAX_OFFERS', 3, 'Nombre de héros proposés à la taverne'],
            ['HERO_MAX_SLOTS', 5, 'Nombre maximum de héros dans l\'équipe'],
            ['HERO_RECRUIT_COST_BASE', 100, 'Coût de base pour recruter un héros (or)'],
            ['HERO_RECRUIT_COST_PER_SLOT', 200, 'Coût supplémentaire par slot débloqué (or)'],

            // Économie
            ['GOLD_SELL_RATIO', 30, 'Ratio de vente d\'objets vs valeur (%)'],
            ['DUNGEON_TIMER_HOURS', 8, 'Délai entre les donjons spéciaux (heures)'],

            // Donjon
            ['DUNGEON_COOLDOWN_HOURS', 8, 'Heures de recharge entre deux donjons'],
            ['DUNGEON_ROOMS_MIN', 5, 'Nombre minimum de salles dans un donjon'],
            ['DUNGEON_ROOMS_MAX', 8, 'Nombre maximum de salles dans un donjon'],
            ['DUNGEON_BOSS_HP_MULT', 300, 'Multiplicateur de HP/ATQ du boss de donjon (%)'],

            // Boutique
            ['SHOP_REFRESH_HOURS', 6, 'Heures entre les rafraîchissements de la boutique'],
            ['SHOP_ITEMS_COUNT', 6, 'Nombre d\'articles dans la boutique'],
            ['SHOP_PRICE_MARKUP', 300, 'Majoration du prix boutique vs base (%)'],

            // Or par combat (ECONOMY.md §2.1)
            ['GOLD_PER_KILL_BASE', 5, 'Or de base par monstre tué'],
            ['GOLD_PER_KILL_LEVEL_MULT', 2, 'Or supplémentaire par niveau du monstre'],
            ['GOLD_ELITE_BONUS', 50, '% bonus or pour un monstre élite'],
            ['GOLD_MINIBOSS_MULT', 5, 'Multiplicateur or pour un mini-boss'],
            ['GOLD_BOSS_MULT', 15, 'Multiplicateur or pour un boss de zone'],
            ['GOLD_QUEST_DAILY_MULT', 20, 'Or = niveau_joueur × ce mult (quêtes quotidiennes)'],
            ['GOLD_QUEST_ZONE_MULT', 30, 'Or = niveau_zone × ce mult × numéro_quête'],
            ['GOLD_OFFLINE_EFFICIENCY', 75, '% or normal gagné en idle offline'],

            // Matériaux (ECONOMY.md §2.2)
            ['MATERIAL_DROP_CHANCE', 30, '% de chance de drop de matériau par combat'],
            ['MATERIAL_ELITE_BONUS', 100, '% de chance bonus matériau pour les élites (30+30=60%)'],
            ['MATERIAL_BOSS_GUARANTEED', 3, 'Nombre de matériaux garantis par boss'],
            ['MATERIAL_RARE_CHANCE', 5, '% chance matériau rare cross-zone par combat'],

            // Quêtes
            ['QUEST_DAILY_COUNT', 3, 'Quêtes quotidiennes par jour'],
            ['QUEST_DAILY_STEPS_MIN', 3, 'Étapes minimum d\'une quête quotidienne'],
            ['QUEST_DAILY_STEPS_MAX', 5, 'Étapes maximum d\'une quête quotidienne'],
            ['QUEST_ZONE_STEPS_MIN', 5, 'Étapes minimum d\'une quête de zone'],
            ['QUEST_ZONE_STEPS_MAX', 7, 'Étapes maximum d\'une quête de zone'],
            ['QUEST_BUFF_DURATION_SHORT', 10, 'Durée buff court (combats)'],
            ['QUEST_BUFF_DURATION_MEDIUM', 30, 'Durée buff moyen (combats)'],
            ['QUEST_BUFF_DURATION_LONG', 100, 'Durée buff long (combats)'],
            ['QUEST_DEBUFF_DURATION_MAX', 20, 'Durée maximum d\'un debuff (combats)'],
            ['QUEST_HERO_ABSENCE_MAX', 60, 'Absence maximum d\'un héros perdu (minutes)'],
            ['QUEST_SURPRISE_CHANCE', 15, '% de chance d\'événement surprise par étape'],
            ['QUEST_REPUTATION_PER_QUEST', 10, 'Points de réputation par quête réussie'],
            ['QUEST_REPUTATION_ZONE_UNLOCK', 100, 'Réputation pour débloquer le contenu bonus'],
            ['QUEST_WTF_DAILY_CHANCE', 5, '% de chance d\'apparition d\'une quête WTF'],
            ['QUEST_VOICE_HEROIC_XP_BONUS', 25, '% bonus XP voie héroïque'],
            ['QUEST_VOICE_CUNNING_GOLD_BONUS', 25, '% bonus or voie maligne'],
            ['QUEST_VOICE_COMIC_ALL_BONUS', 10, '% bonus toutes récompenses voie comique'],
            ['DAILY_QUEST_USER_LEVEL_GOLD_MULT', 10, '% bonus or quête quotidienne par niveau joueur au-dessus de 1'],
            ['QUEST_DEBUFF_REMOVE_COST', 100, 'Or × niveau du héros pour retirer un debuff'],
            ['QUEST_POOL_SIZE_TARGET', 50, 'Quêtes à maintenir dans le pool par zone'],
            ['QUEST_POOL_REFILL_THRESHOLD', 20, 'Seuil pour déclencher la régénération du pool'],
            ['DAILY_QUEST_COUNT', 3, 'Nombre de quêtes quotidiennes assignées par joueur'],
            ['QUEST_WTF_STEPS_MIN', 7, 'Étapes minimum d\'une quête WTF'],
            ['QUEST_WTF_STEPS_MAX', 12, 'Étapes maximum d\'une quête WTF'],
            ['QUEST_EVENT_STEPS', 5, 'Étapes d\'une quête événementielle'],
            ['REPUTATION_MAX', 200, 'Score de réputation maximum par zone'],
            ['NPC_RELATION_MAX', 100, 'Score de relation maximum par PNJ'],
            ['NPC_GIFT_MAX_PER_DAY', 1, 'Cadeaux maximum par PNJ par jour'],
            ['NPC_GIFT_RELATION_GAIN', 5, 'Points de relation gagnés par cadeau offert'],
            ['ENCHANT_ADVANCED_UNLOCKED', 0, '1 si les enchantements avancés (Magus) sont débloqués'],
            ['IDLE_EVENT_INTERVAL', 30, 'Minutes entre chaque micro-événement idle'],
            ['IDLE_EVENT_REPEAT_PROTECTION', 5, 'Événements uniques avant qu\'un puisse se répéter'],

            // Crafting
            ['CRAFT_FUSION_COUNT', 3, 'Nombre d\'objets requis pour une fusion'],
            ['CRAFT_FUSION_SUCCESS', 85, '% de chance de succès de fusion'],
            ['CRAFT_FUSION_CRIT', 10, '% de chance de critique de fusion (+1 rareté bonus)'],
            ['CRAFT_DISMANTLE_MATERIAL_MIN', 1, 'Matériaux minimum par démontage'],
            ['CRAFT_DISMANTLE_MATERIAL_MAX', 5, 'Matériaux maximum par démontage'],
            ['CRAFT_RECIPE_DISCOVER_CHANCE', 5, '% de chance de découvrir une recette en craftant'],
            ['CRAFT_GERARD_HUMOR_CHANCE', 30, '% de chance que Gérard fasse un commentaire'],

            // Inventaire
            ['INVENTORY_MAX_ITEMS', 100, 'Limite d\'objets en inventaire'],
            ['INVENTORY_EXPAND_COST', 500, 'Coût d\'expansion par tranche de 20 slots'],
            ['INVENTORY_MAX_EXPANDED', 200, 'Limite maximale après expansion'],

            // Talents
            ['MAX_TALENT_POINTS', 20, 'Points de talent maximum par héros'],
            ['TALENT_RESET_BASE_COST', 200, 'Coût de base pour réinitialiser les talents (×1.5 par reset)'],
            ['TRAIT_DEFAUT_BRANCH_TRIGGER_BONUS', 5, 'Bonus % toutes stats par déclenchement (Branche du Défaut P3)'],

            // Durabilité
            ['LOOT_DURABILITY_BASE', 100, 'Durabilité de base des objets'],
            ['LOOT_DURABILITY_WTF', 30, 'Durabilité des objets WTF'],
            ['LOOT_DURABILITY_LOSS_PER_COMBAT', 1, 'Durabilité perdue par combat'],
            ['LOOT_REPAIR_COST_MULTIPLIER', 2, 'Multiplicateur du coût de réparation'],
        ];

        foreach ($settings as [$key, $value, $description]) {
            DB::table('game_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => $value, 'description' => $description]
            );
        }
    }
}
