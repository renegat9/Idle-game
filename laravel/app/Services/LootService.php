<?php

namespace App\Services;

use App\Jobs\GenerateLootImage;
use App\Models\Item;
use App\Models\ItemTemplate;
use App\Models\Monster;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

class LootService
{
    private const RARITY_MULTIPLIER = [
        'commun'     => 1,
        'peu_commun' => 2,
        'rare'       => 3,
        'epique'     => 4,
        'legendaire' => 5,
        'wtf'        => 6,
    ];

    // Prix_base = Niveau × this value (§7 LOOT_CRAFTING.md)
    private const RARITY_BASE_PRICE = [
        'commun'     => 5,
        'peu_commun' => 15,
        'rare'       => 40,
        'epique'     => 100,
        'legendaire' => 300,
        'wtf'        => 500,
    ];

    // Number of stats generated per rarity (§3.2)
    private const RARITY_STAT_COUNT = [
        'commun'     => 1,
        'peu_commun' => 2,
        'rare'       => 3,
        'epique'     => 4,
        'legendaire' => 5,
        'wtf'        => 5,
    ];

    // Primary stat and secondary pool per slot
    private const SLOT_STATS = [
        'arme'        => ['primary' => 'atq', 'secondary' => ['int', 'cha', 'vit', 'def', 'hp']],
        'armure'      => ['primary' => 'def', 'secondary' => ['hp', 'vit', 'int', 'cha', 'atq']],
        'casque'      => ['primary' => 'def', 'secondary' => ['int', 'cha', 'hp', 'vit', 'atq']],
        'bottes'      => ['primary' => 'vit', 'secondary' => ['def', 'cha', 'hp', 'int', 'atq']],
        'accessoire'  => ['primary' => 'cha', 'secondary' => ['int', 'hp', 'vit', 'def', 'atq']],
        'truc_bizarre'=> ['primary' => 'hp',  'secondary' => ['atq', 'def', 'vit', 'cha', 'int']],
    ];

    // Special-effect chance per rarity (% — 0 means no effect possible)
    private const EFFECT_CHANCE = [
        'commun'     => 0,
        'peu_commun' => 0,
        'rare'       => 30,
        'epique'     => 60,
        'legendaire' => 100,
        'wtf'        => 100,
    ];

    // Special-effect pools, keyed by ID. null slots = applies to all.
    private const SPECIAL_EFFECTS = [
        // ── Rare ──────────────────────────────────────────────────────
        'R01' => ['name'=>'Vampirisme',           'desc'=>'Soigne 3% des dégâts infligés',                               'slots'=>['arme'],                   'data'=>['type'=>'lifesteal','pct'=>3]],
        'R02' => ['name'=>'Épines',               'desc'=>'Renvoie 5% des dégâts reçus à l\'attaquant',                  'slots'=>['armure','casque'],         'data'=>['type'=>'thorns','pct'=>5]],
        'R03' => ['name'=>'Célérité',             'desc'=>'+5% VIT',                                                     'slots'=>['bottes'],                 'data'=>['type'=>'stat_bonus','stat'=>'vit','pct'=>5]],
        'R04' => ['name'=>'Chercheur d\'Or',      'desc'=>'+10% or trouvé',                                              'slots'=>['accessoire'],             'data'=>['type'=>'gold_bonus','pct'=>10]],
        'R05' => ['name'=>'Résistance au Feu',    'desc'=>'Réduit les dégâts de feu de 20%',                            'slots'=>['armure','casque'],         'data'=>['type'=>'resist','element'=>'feu','pct'=>20]],
        'R06' => ['name'=>'Coup Double',          'desc'=>'5% de chance de frapper deux fois',                          'slots'=>['arme'],                   'data'=>['type'=>'double_strike','pct'=>5]],
        'R07' => ['name'=>'Régénération',         'desc'=>'Récupère 1% PV max par tour',                                'slots'=>['armure','accessoire'],     'data'=>['type'=>'regen','pct'=>1]],
        'R08' => ['name'=>'Chance du Débutant',   'desc'=>'+3% critique',                                               'slots'=>['arme','accessoire'],       'data'=>['type'=>'crit_bonus','pct'=>3]],
        'R09' => ['name'=>'Solidité',             'desc'=>'Durabilité +50%',                                             'slots'=>null,                       'data'=>['type'=>'durability_bonus','pct'=>50]],
        'R10' => ['name'=>'Chercheur de Loot',    'desc'=>'+5% de chance de loot',                                      'slots'=>['accessoire'],             'data'=>['type'=>'loot_bonus','pct'=>5]],

        // ── Épique ───────────────────────────────────────────────────
        'E01' => ['name'=>'Vampirisme Majeur',    'desc'=>'Soigne 6% des dégâts infligés',                              'slots'=>['arme'],                   'data'=>['type'=>'lifesteal','pct'=>6]],
        'E02' => ['name'=>'Bouclier Magique',     'desc'=>'10% de chance d\'annuler un sort ennemi',                    'slots'=>['armure','casque'],         'data'=>['type'=>'spell_block','pct'=>10]],
        'E03' => ['name'=>'Vitesse de l\'Éclair', 'desc'=>'+10% VIT + 5% esquive',                                      'slots'=>['bottes'],                 'data'=>['type'=>'multi_bonus','vit_pct'=>10,'dodge_pct'=>5]],
        'E04' => ['name'=>'Fortune',              'desc'=>'+20% or trouvé',                                             'slots'=>['accessoire'],             'data'=>['type'=>'gold_bonus','pct'=>20]],
        'E05' => ['name'=>'Exécuteur',            'desc'=>'+15% dégâts contre les ennemis sous 25% PV',                'slots'=>['arme'],                   'data'=>['type'=>'execute','bonus_pct'=>15,'threshold_pct'=>25]],
        'E06' => ['name'=>'Aura Glaciale',        'desc'=>'10% de chance de Ralentir l\'attaquant 1 tour',             'slots'=>['armure'],                 'data'=>['type'=>'on_hit_slow','pct'=>10,'turns'=>1]],
        'E07' => ['name'=>'Régénération Majeure', 'desc'=>'2% PV max par tour',                                        'slots'=>['armure','accessoire'],     'data'=>['type'=>'regen','pct'=>2]],
        'E08' => ['name'=>'Précision Mortelle',   'desc'=>'+8% critique, les critiques ignorent 10% DEF',             'slots'=>['arme'],                   'data'=>['type'=>'crit_bonus','pct'=>8,'def_ignore_pct'=>10]],
        'E09' => ['name'=>'Indestructible',       'desc'=>'Durabilité infinie',                                        'slots'=>null,                       'data'=>['type'=>'indestructible']],
        'E10' => ['name'=>'Magnétisme à Loot',    'desc'=>'+10% chance de loot + rareté améliorée 5%',                'slots'=>['accessoire'],             'data'=>['type'=>'loot_magnet','loot_pct'=>10,'rarity_upgrade_pct'=>5]],

        // ── Légendaire ───────────────────────────────────────────────
        'L01' => ['name'=>'Fléau des Dieux',      'desc'=>'10% de chance d\'infliger 200% des dégâts',                 'slots'=>['arme'],                   'data'=>['type'=>'smite','pct'=>10,'multiplier'=>200]],
        'L02' => ['name'=>'Immortalité Partielle','desc'=>'1× par combat, si PV = 0, revient à 20% PV',               'slots'=>['armure'],                 'data'=>['type'=>'near_death_save','revive_pct'=>20]],
        'L03' => ['name'=>'Hâte Absolue',         'desc'=>'+20% VIT, agit toujours en premier',                       'slots'=>['bottes'],                 'data'=>['type'=>'always_first','vit_pct'=>20]],
        'L04' => ['name'=>'Midas',                'desc'=>'Chaque ennemi tué rapporte le double d\'or',               'slots'=>['accessoire'],             'data'=>['type'=>'gold_double']],
        'L05' => ['name'=>'Absorption Vitale',    'desc'=>'10% des dégâts soignent toute l\'équipe',                  'slots'=>['arme'],                   'data'=>['type'=>'team_lifesteal','pct'=>10]],
        'L06' => ['name'=>'Reflet',               'desc'=>'20% de chance de renvoyer un sort ennemi',                 'slots'=>['armure','casque'],         'data'=>['type'=>'spell_reflect','pct'=>20]],
        'L07' => ['name'=>'Régénération Totale',  'desc'=>'3% PV max/tour + retire 1 effet négatif/tour',             'slots'=>['accessoire'],             'data'=>['type'=>'cleanse_regen','regen_pct'=>3]],
        'L08' => ['name'=>'Frappe Dimensionnelle','desc'=>'Les attaques ignorent 30% de la DEF',                      'slots'=>['arme'],                   'data'=>['type'=>'armor_pierce','pct'=>30]],
        'L09' => ['name'=>'Résonance de Groupe',  'desc'=>'+5% de la stat principale à toute l\'équipe',             'slots'=>null,                       'data'=>['type'=>'team_aura','pct'=>5]],
        'L10' => ['name'=>'Chance Insolente',     'desc'=>'+15% critique + +15% esquive',                             'slots'=>['accessoire'],             'data'=>['type'=>'multi_bonus','crit_pct'=>15,'dodge_pct'=>15]],

        // ── WTF ─────────────────────────────────────────────────────
        'W01' => ['name'=>'Inversion',            'desc'=>'Tours pairs : dégâts soignent, soins blessent',            'slots'=>null, 'data'=>['type'=>'inversion']],
        'W02' => ['name'=>'Polyglotte Involontaire','desc'=>'20% de chance d\'action aléatoire, buffs +30%',          'slots'=>null, 'data'=>['type'=>'polyglot','action_rng_pct'=>20,'buff_bonus_pct'=>30]],
        'W03' => ['name'=>'Loi de Murphy',        'desc'=>'+30% déclenchement trait négatif, Défaut ×2',              'slots'=>null, 'data'=>['type'=>'murphy','trait_pct'=>30]],
        'W04' => ['name'=>'Aura de Malchance',    'desc'=>'Ennemis -15% stats, alliés -8% stats',                    'slots'=>null, 'data'=>['type'=>'bad_aura','enemy_pct'=>-15,'ally_pct'=>-8]],
        'W05' => ['name'=>'Téléportation Aléatoire','desc'=>'15% par tour : esquive tout OU prend 20% PV',           'slots'=>null, 'data'=>['type'=>'teleport','pct'=>15]],
        'W06' => ['name'=>'Objection !',          'desc'=>'Annule la dernière action ennemie (CD 10 tours)',          'slots'=>null, 'data'=>['type'=>'objection','cooldown'=>10]],
        'W07' => ['name'=>'Clone Maladroit',      'desc'=>'Clone à 30% stats en début de combat, cible aléatoire',   'slots'=>null, 'data'=>['type'=>'clone','stat_pct'=>30]],
        'W08' => ['name'=>'Monologue du Méchant', 'desc'=>'Boss sous 50% PV : monologue 2 tours (pas d\'attaque)',   'slots'=>null, 'data'=>['type'=>'boss_monologue','hp_threshold_pct'=>50,'turns'=>2]],
        'W09' => ['name'=>'Gravité Optionnelle',  'desc'=>'+30% esquive, -20% ATQ physique, +20% ATQ magique',       'slots'=>null, 'data'=>['type'=>'weightless','dodge_pct'=>30,'patq_pct'=>-20,'matq_pct'=>20]],
        'W10' => ['name'=>'Quatrième Mur',        'desc'=>'+10% toutes stats, 15%/tour de -5% stats alliés 1 tour', 'slots'=>null, 'data'=>['type'=>'fourth_wall','self_pct'=>10,'ally_debuff_pct'=>5,'ally_debuff_chance'=>15]],
    ];

    private const EFFECTS_BY_RARITY = [
        'rare'       => ['R01','R02','R03','R04','R05','R06','R07','R08','R09','R10'],
        'epique'     => ['E01','E02','E03','E04','E05','E06','E07','E08','E09','E10'],
        'legendaire' => ['L01','L02','L03','L04','L05','L06','L07','L08','L09','L10'],
        'wtf'        => ['W01','W02','W03','W04','W05','W06','W07','W08','W09','W10'],
    ];

    private array $rareNameTemplates = [
        'arme'        => ['L\'Épée qui Coupe (Parfois)','Le Marteau du Destin Approximatif','La Dague du Voleur Repenti','Le Bâton du Mage Distrait','L\'Arc de la Précision Relative'],
        'armure'      => ['La Cuirasse du Brave (Pas Très)','La Robe de l\'Académie Douteuse','Le Hauberk du Combattant Occasionnel','L\'Armure Légère de la Discrétion'],
        'casque'      => ['Le Casque de la Pensée Profonde','La Coiffe du Sage Amateur','Le Heaume du Courage Relatif'],
        'bottes'      => ['Les Bottes de la Fuite Rapide','Les Sandales de l\'Aventurier Survivant','Les Bottines du Voleur Chanceux'],
        'accessoire'  => ['L\'Amulette du Chance-Peut-Être','L\'Anneau du Pouvoir Modeste','Le Talisman du Destin Flou'],
        'truc_bizarre'=> ['L\'Objet de Fonction Inconnue','Le Truc Brillant Inexplicable','La Chose du Donjon Profond'],
    ];

    public function __construct(
        private readonly SettingsService $settings
    ) {}

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Tente de générer un objet loot pour un monstre donné.
     * Retourne null si aucun objet ne drop.
     */
    public function rollLoot(Zone $zone, Monster $monster, User $user): ?Item
    {
        $dropChance = (int) $this->settings->get('LOOT_DROP_CHANCE', 60);
        $bonus      = (int) ($monster->loot_bonus ?? 0);
        $effective  = min(100, intdiv($dropChance * (100 + $bonus), 100));

        if (random_int(1, 100) > $effective) {
            return null;
        }

        $rarity    = $this->rollRarity();
        $slot      = $this->rollSlot();
        $itemLevel = $this->rollItemLevel($zone);

        return $this->generateItem($zone, $rarity, $slot, $itemLevel, $user);
    }

    /**
     * Génère un objet loot pour une récompense de quête (sans zone ni monstre).
     */
    public function rollQuestLoot(User $user, string $rarity): Item
    {
        $slot      = $this->rollSlot();
        $heroLevel = (int) ($user->activeHeroes()->avg('level') ?? 1);
        return $this->generateItemForCrafting($user, $rarity, $slot, max(1, $heroLevel));
    }

    /**
     * Roule la rareté selon les poids de game_settings.
     */
    public function rollRarity(): string
    {
        $weights = [
            'commun'     => (int) $this->settings->get('LOOT_RARITY_COMMUN', 50),
            'peu_commun' => (int) $this->settings->get('LOOT_RARITY_PEU_COMMUN', 25),
            'rare'       => (int) $this->settings->get('LOOT_RARITY_RARE', 14),
            'epique'     => (int) $this->settings->get('LOOT_RARITY_EPIQUE', 7),
            'legendaire' => (int) $this->settings->get('LOOT_RARITY_LEGENDAIRE', 3),
            'wtf'        => (int) $this->settings->get('LOOT_RARITY_WTF', 1),
        ];

        $total      = array_sum($weights);
        $roll       = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $rarity => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $rarity;
            }
        }

        return 'commun';
    }

    /**
     * Roule le slot avec les poids définis dans LOOT_CRAFTING.md §3.1.
     */
    public function rollSlot(): string
    {
        $weights = [
            'arme'         => 25,
            'armure'       => 20,
            'casque'       => 15,
            'bottes'       => 15,
            'accessoire'   => 15,
            'truc_bizarre' => 10,
        ];

        $total      = array_sum($weights);
        $roll       = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $slot => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $slot;
            }
        }

        return 'accessoire';
    }

    /**
     * Génère un item directement (crafting, quêtes…) sans zone obligatoire.
     */
    public function generateItemForCrafting(User $user, string $rarity, string $slot, int $itemLevel): Item
    {
        $variance = (int) $this->settings->get('LOOT_STAT_VARIANCE', 15);
        $template = ItemTemplate::where('rarity', $rarity)->where('slot', $slot)->inRandomOrder()->first();

        if ($template) {
            return $this->generateFromTemplate($template, $itemLevel, $variance, $user);
        }

        return $this->buildItem($user, $rarity, $slot, $itemLevel, $variance, 'physique');
    }

    /**
     * Calcule le prix de réparation d'un objet.
     * Coût = (Dura_max - Dura_actuelle) × Niveau × REPAIR_MULT
     */
    public function repairCost(Item $item): int
    {
        $mult    = (int) $this->settings->get('LOOT_REPAIR_COST_MULTIPLIER', 2);
        $missing = max(0, ($item->durability_max ?? 100) - ($item->durability_current ?? 100));
        return $missing * ($item->item_level ?? 1) * $mult;
    }

    /**
     * Calcule le prix de vente d'un objet (avec réduction si cassé).
     * Prix = Niveau × BaseRareté × LOOT_SELL_PERCENT / 100
     */
    public function calculateSellValue(Item $item): int
    {
        $sellPct   = (int) $this->settings->get('LOOT_SELL_PERCENT', 30);
        $basePrice = self::RARITY_BASE_PRICE[$item->rarity] ?? 5;
        $value     = intdiv(($item->item_level ?? 1) * $basePrice * $sellPct, 100);

        if (($item->durability_current ?? 1) === 0) {
            $value = intdiv($value, 2);
        }

        return max(1, $value);
    }

    // ─── Special effects ─────────────────────────────────────────────────────

    /**
     * Rolls for a special effect appropriate to rarity and slot.
     * Returns effect array from SPECIAL_EFFECTS or null.
     */
    public function rollSpecialEffect(string $rarity, string $slot): ?array
    {
        $chance = self::EFFECT_CHANCE[$rarity] ?? 0;
        if ($chance === 0 || random_int(1, 100) > $chance) {
            return null;
        }

        $pool = self::EFFECTS_BY_RARITY[$rarity] ?? [];
        if (empty($pool)) {
            return null;
        }

        // Filter by slot compatibility
        $eligible = array_filter($pool, function (string $id) use ($slot) {
            $allowedSlots = self::SPECIAL_EFFECTS[$id]['slots'] ?? null;
            return $allowedSlots === null || in_array($slot, $allowedSlots, true);
        });

        if (empty($eligible)) {
            return null;
        }

        $id = array_values($eligible)[array_rand(array_values($eligible))];
        return array_merge(['id' => $id], self::SPECIAL_EFFECTS[$id]);
    }

    /**
     * Inserts a special effect row for the given item.
     */
    public function applySpecialEffect(int $itemId, array $effect): void
    {
        DB::table('item_effects')->insert([
            'item_id'       => $itemId,
            'effect_key'    => $effect['id'],
            'description'   => $effect['name'] . ' — ' . $effect['desc'],
            'effect_data'   => json_encode($effect['data']),
            'is_enchantment'=> 0,
        ]);

        // R09 / E09 — adjust durability immediately
        if (in_array($effect['id'], ['R09', 'E09'], true)) {
            if ($effect['id'] === 'E09') {
                DB::table('items')->where('id', $itemId)->update(['durability_max' => 999, 'durability_current' => 999]);
            } else {
                // +50% durability
                $item = DB::table('items')->where('id', $itemId)->first();
                if ($item) {
                    $newMax = min(999, intdiv($item->durability_max * 150, 100));
                    DB::table('items')->where('id', $itemId)->update([
                        'durability_max'     => $newMax,
                        'durability_current' => $newMax,
                    ]);
                }
            }
        }
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function rollItemLevel(Zone $zone): int
    {
        $range = (int) $this->settings->get('LOOT_LEVEL_RANGE', 3);
        $base  = intdiv($zone->level_min + $zone->level_max, 2);
        return max(1, $base + random_int(-$range, $range));
    }

    private function generateItem(Zone $zone, string $rarity, string $slot, int $itemLevel, User $user): Item
    {
        $variance = (int) $this->settings->get('LOOT_STAT_VARIANCE', 15);

        if (in_array($rarity, ['commun', 'peu_commun'], true)) {
            $template = ItemTemplate::where('zone_id', $zone->id)
                ->where('rarity', $rarity)
                ->where('slot', $slot)
                ->inRandomOrder()
                ->first();

            if ($template) {
                return $this->generateFromTemplate($template, $itemLevel, $variance, $user);
            }
        }

        return $this->buildItem($user, $rarity, $slot, $itemLevel, $variance, $zone->dominant_element ?? 'physique', $zone->name ?? '');
    }

    private function generateFromTemplate(ItemTemplate $template, int $itemLevel, int $variance, User $user): Item
    {
        $mult      = self::RARITY_MULTIPLIER[$template->rarity] ?? 1;
        $sellPct   = (int) $this->settings->get('LOOT_SELL_PERCENT', 30);
        $basePrice = self::RARITY_BASE_PRICE[$template->rarity] ?? 5;

        $applyVar = fn(int $base) => max(0, intdiv($base * $itemLevel * random_int(100 - $variance, 100 + $variance), 100));

        $item = Item::create([
            'user_id'             => $user->id,
            'template_id'         => $template->id,
            'name'                => $template->name,
            'description'         => $template->description,
            'rarity'              => $template->rarity,
            'slot'                => $template->slot,
            'element'             => $template->element,
            'item_level'          => $itemLevel,
            'atq'                 => $applyVar($template->base_atq),
            'def'                 => $applyVar($template->base_def),
            'hp'                  => $applyVar($template->base_hp),
            'vit'                 => $applyVar($template->base_vit),
            'cha'                 => $applyVar($template->base_cha),
            'int'                 => $applyVar($template->base_int),
            'sell_value'          => max(1, intdiv($itemLevel * $basePrice * $sellPct, 100)),
            'durability_current'  => $this->baseDurability($template->rarity),
            'durability_max'      => $this->baseDurability($template->rarity),
            'is_ai_generated'     => false,
        ]);

        $this->rollAndApplyEffect($item);
        $this->dispatchImageJob($item);

        return $item;
    }

    /**
     * Core stat-generation path for items without a template.
     */
    private function buildItem(
        User   $user,
        string $rarity,
        string $slot,
        int    $itemLevel,
        int    $variance,
        string $element,
        string $zoneName = ''
    ): Item {
        $mult      = self::RARITY_MULTIPLIER[$rarity] ?? 1;
        $statCount = self::RARITY_STAT_COUNT[$rarity] ?? 1;
        $sellPct   = (int) $this->settings->get('LOOT_SELL_PERCENT', 30);
        $basePrice = self::RARITY_BASE_PRICE[$rarity] ?? 5;

        $stats     = $this->generateStats($itemLevel, $mult, $variance, $slot, $statCount);
        $sellValue = max(1, intdiv($itemLevel * $basePrice * $sellPct, 100));
        $durability = $this->baseDurability($rarity);

        $name = $this->buildName($rarity, $slot);
        $desc = $zoneName
            ? "Trouvé dans {$zoneName}. Gérard en serait jaloux."
            : 'Créé par fusion à la forge. Gérard est impressionné.';

        $item = Item::create([
            'user_id'            => $user->id,
            'name'               => $name,
            'description'        => $desc,
            'rarity'             => $rarity,
            'slot'               => $slot,
            'element'            => $element,
            'item_level'         => $itemLevel,
            'atq'                => $stats['atq'],
            'def'                => $stats['def'],
            'hp'                 => $stats['hp'],
            'vit'                => $stats['vit'],
            'cha'                => $stats['cha'],
            'int'                => $stats['int'],
            'sell_value'         => $sellValue,
            'durability_current' => $durability,
            'durability_max'     => $durability,
            'is_ai_generated'    => false,
        ]);

        $this->rollAndApplyEffect($item);
        $this->dispatchImageJob($item);

        return $item;
    }

    /**
     * Generates stats array for a given slot and rarity.
     * Primary stat is always included; additional stats are taken from the secondary pool.
     */
    private function generateStats(int $level, int $mult, int $variance, string $slot, int $statCount): array
    {
        $statMap = self::SLOT_STATS[$slot] ?? self::SLOT_STATS['truc_bizarre'];

        $calcStat = fn(int $isMain) => max(0, intdiv(
            $level * $mult * ($isMain ? 1 : 1) * random_int(100 - $variance, 100 + $variance),
            100
        ));

        $base     = ['atq' => 0, 'def' => 0, 'hp' => 0, 'vit' => 0, 'cha' => 0, 'int' => 0];
        $primary  = $statMap['primary'];
        $secondary = $statMap['secondary'];

        // Primary stat (always at full value)
        $base[$primary] = max(1, intdiv($level * $mult * random_int(100 - $variance, 100 + $variance), 100));

        // Additional stats at 60% of the base to keep primary dominant
        $extraCount = min($statCount - 1, count($secondary));
        $picked     = array_slice($secondary, 0, $extraCount);

        foreach ($picked as $stat) {
            $base[$stat] = max(0, intdiv($level * $mult * 60 * random_int(100 - $variance, 100 + $variance), 10000));
        }

        return $base;
    }

    private function baseDurability(string $rarity): int
    {
        if ($rarity === 'wtf') {
            return (int) $this->settings->get('LOOT_DURABILITY_WTF', 30);
        }
        return (int) $this->settings->get('LOOT_DURABILITY_BASE', 100);
    }

    private function buildName(string $rarity, string $slot): string
    {
        $templates = $this->rareNameTemplates[$slot] ?? ['Objet Mystérieux'];
        $name      = $templates[array_rand($templates)];

        return match ($rarity) {
            'epique'     => $name . ' (Épique)',
            'legendaire' => $name . ' (LÉGENDAIRE)',
            'wtf'        => '??? ' . $name . ' ???',
            default      => $name,
        };
    }

    private function rollAndApplyEffect(Item $item): void
    {
        $effect = $this->rollSpecialEffect($item->rarity, $item->slot);
        if ($effect !== null) {
            $this->applySpecialEffect($item->id, $effect);
        }
    }

    private function dispatchImageJob(Item $item): void
    {
        $minRarity = (int) $this->settings->get('LOOT_AI_GENERATION_MIN_RARITY', 3);
        $rarityIndex = array_search($item->rarity, array_keys(self::RARITY_MULTIPLIER));

        if ($rarityIndex !== false && $rarityIndex >= $minRarity - 1) {
            GenerateLootImage::dispatch($item->id, $item->slot, $item->rarity);
        }
    }

    /**
     * Tente de dropper un matériau de forge parmi les matériaux disponibles pour cette zone.
     * Retourne [slug, name, qty] ou null.
     *
     * @param \Illuminate\Support\Collection $availableMaterials  (slug, name, drop_chance)
     * @param int $eliteLootMult  multiplicateur élite (100 = normal)
     */
    public function rollMaterialDrop(\Illuminate\Support\Collection $availableMaterials, int $eliteLootMult = 100): ?array
    {
        $baseChance = $this->settings->get('EXPLORATION_MATERIAL_CHANCE', 50);
        $effectiveChance = min(100, intdiv($baseChance * $eliteLootMult, 100));

        if (random_int(1, 100) > $effectiveChance) {
            return null;
        }

        $droppable = $availableMaterials->where('drop_chance', '>', 0);
        if ($droppable->isEmpty()) {
            return null;
        }

        // Sélection pondérée par drop_chance
        $totalWeight = (int) $droppable->sum('drop_chance');
        $roll = random_int(1, max(1, $totalWeight));
        $cumulative = 0;
        foreach ($droppable as $mat) {
            $cumulative += $mat->drop_chance;
            if ($roll <= $cumulative) {
                // Matériaux communs (drop_chance élevé) → plus grande quantité
                $maxQty = $mat->drop_chance >= 20 ? 3 : ($mat->drop_chance >= 5 ? 2 : 1);
                return [
                    'slug' => $mat->slug,
                    'name' => $mat->name,
                    'qty'  => random_int(1, $maxQty),
                ];
            }
        }

        return null;
    }
}
