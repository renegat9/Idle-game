<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipe;
use Illuminate\Support\Facades\DB;

class CraftingService
{
    private const RARITY_ORDER = ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'];

    private const RARITY_COST_MULT = [
        'commun'      => 1,
        'peu_commun'  => 2,
        'rare'        => 4,
        'epique'      => 8,
        'legendaire'  => 20,
    ];

    private const RARITY_BASE_SELL = [
        'commun'      => 5,
        'peu_commun'  => 15,
        'rare'        => 40,
        'epique'      => 100,
        'legendaire'  => 300,
        'wtf'         => 500,
    ];

    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $loot,
        private readonly NarratorService $narrator,
    ) {}

    // ─── Fusion ──────────────────────────────────────────────────────────────

    public function fuse(User $user, array $itemIds): array
    {
        if (count($itemIds) !== $this->settings->get('CRAFT_FUSION_COUNT', 3)) {
            return ['error' => 'La fusion requiert exactement 3 objets.'];
        }

        $items = Item::whereIn('id', $itemIds)
            ->where('user_id', $user->id)
            ->whereNull('equipped_by_hero_id')
            ->get();

        if ($items->count() !== 3) {
            return ['error' => 'Certains objets sont introuvables ou équipés.'];
        }

        $rarities = $items->pluck('rarity')->unique();
        if ($rarities->count() !== 1) {
            return ['error' => 'Les 3 objets doivent avoir la même rareté.'];
        }

        $rarity = $rarities->first();
        $rarityIdx = array_search($rarity, self::RARITY_ORDER);
        if ($rarityIdx === false || $rarityIdx >= count(self::RARITY_ORDER) - 1) {
            return ['error' => 'Cette rareté ne peut pas être fusionnée davantage.'];
        }

        $avgLevel = intdiv($items->sum('item_level'), 3);
        $fusionCost = $avgLevel * 50 * (self::RARITY_COST_MULT[$rarity] ?? 1);

        if ($user->gold < $fusionCost) {
            return ['error' => "Fusion insuffisamment financée. Coût : {$fusionCost} or."];
        }

        $successChance = $this->settings->get('CRAFT_FUSION_SUCCESS', 85);
        $critChance    = $this->settings->get('CRAFT_FUSION_CRIT', 10);
        $sameSlot      = $items->pluck('slot')->unique()->count() === 1;
        if ($sameSlot) $successChance = min(95, $successChance + 10);

        $roll    = rand(1, 100);
        $success = $roll <= $successChance;

        $gerardComment = $this->gerardComment($success, false);

        if (!$success) {
            // Failure: destroy all 3, refund 1 material; 10% chance to drop Larme de Gérard
            $dropLarme = rand(1, 100) <= 10;
            DB::transaction(function () use ($user, $items, $fusionCost, $dropLarme) {
                $user->decrement('gold', $fusionCost);
                Item::whereIn('id', $items->pluck('id'))->delete();
                $this->grantMaterialForRarity($user, $items->first()->rarity, 1);
                if ($dropLarme) {
                    $this->addMaterialBySlug($user->id, 'larme_gerard', 1);
                }
            });

            return [
                'success'          => false,
                'message'          => 'La fusion a échoué. Les objets sont partis en fumée.',
                'drop_larme'       => $dropLarme,
                'gerard_comment'   => $gerardComment,
                'narrator_comment' => $this->narrator->getComment('craft_failure', []),
            ];
        }

        // Success: determine result rarity
        $critRoll        = rand(1, 100);
        $isCrit          = ($critRoll <= $critChance);
        $resultRarityIdx = min($rarityIdx + ($isCrit ? 2 : 1), count(self::RARITY_ORDER) - 1);
        $resultRarity    = self::RARITY_ORDER[$resultRarityIdx];

        // Determine result slot
        $slots      = $items->pluck('slot')->values()->toArray();
        $resultSlot = $sameSlot ? $slots[0] : $slots[array_rand($slots)];

        $resultLevel = min($avgLevel + rand(0, 3), 99);
        $resultItem  = $this->loot->generateItemForCrafting($user, $resultRarity, $resultSlot, $resultLevel);

        // Check if all 3 input items have special effects → 40% chance to inherit one
        $inheritedEffect = $this->maybeInheritEffect($items->pluck('id')->toArray(), $resultItem);

        DB::transaction(function () use ($user, $items, $fusionCost, $resultItem) {
            $user->decrement('gold', $fusionCost);
            Item::whereIn('id', $items->pluck('id'))->delete();
            $resultItem->user_id = $user->id;
            $resultItem->save();
        });

        // Chance to discover a recipe
        $newRecipe = null;
        if (rand(1, 100) <= $this->settings->get('CRAFT_RECIPE_DISCOVER_CHANCE', 5)) {
            $newRecipe = $this->discoverRecipe($user);
        }

        return [
            'success'          => true,
            'is_critical'      => $isCrit,
            'result_item'      => $this->itemResponse($resultItem),
            'gold_spent'       => $fusionCost,
            'inherited_effect' => $inheritedEffect,
            'gerard_comment'   => $this->gerardComment(true, $isCrit),
            'new_recipe'       => $newRecipe?->name,
        ];
    }

    /**
     * If all 3 input items have a special effect, there is a 40% chance the result
     * inherits one of those effects (in addition to any rolled for its own rarity).
     */
    private function maybeInheritEffect(array $itemIds, Item $resultItem): ?string
    {
        $effects = DB::table('item_effects')
            ->whereIn('item_id', $itemIds)
            ->where('is_enchantment', false)
            ->get();

        // Need at least one effect per input item (3 effects total minimum)
        $byItem = $effects->groupBy('item_id');
        if ($byItem->count() < 3) {
            return null;
        }

        if (rand(1, 100) > 40) {
            return null;
        }

        $chosen = $effects->random();
        DB::table('item_effects')->insert([
            'item_id'       => $resultItem->id,
            'effect_key'    => $chosen->effect_key . '_inherited',
            'description'   => '[Hérité] ' . $chosen->description,
            'effect_data'   => $chosen->effect_data,
            'is_enchantment'=> 0,
        ]);

        return $chosen->description;
    }

    // ─── Dismantling ─────────────────────────────────────────────────────────

    public function dismantle(User $user, int $itemId): array
    {
        $item = Item::where('id', $itemId)
            ->where('user_id', $user->id)
            ->whereNull('equipped_by_hero_id')
            ->first();

        if (!$item) {
            return ['error' => 'Objet introuvable ou équipé.'];
        }

        $materials = $this->getMaterialsForDismantle($item);

        DB::transaction(function () use ($user, $item, $materials) {
            $item->delete();
            foreach ($materials as ['slug' => $slug, 'qty' => $qty]) {
                $this->addMaterialBySlug($user->id, $slug, $qty);
            }
        });

        return [
            'success'        => true,
            'materials'      => $materials,
            'gerard_comment' => $this->gerardComment(true, false, 'dismantle'),
        ];
    }

    public function dismantleBulk(User $user, array $itemIds): array
    {
        $items = Item::whereIn('id', $itemIds)
            ->where('user_id', $user->id)
            ->whereNull('equipped_by_hero_id')
            ->get();

        if ($items->isEmpty()) {
            return ['error' => 'Aucun objet valide à démonter.'];
        }

        // Agréger tous les matériaux
        $totals = []; // slug => qty
        foreach ($items as $item) {
            foreach ($this->getMaterialsForDismantle($item) as ['slug' => $slug, 'qty' => $qty]) {
                $totals[$slug] = ($totals[$slug] ?? 0) + $qty;
            }
        }

        DB::transaction(function () use ($user, $items, $totals) {
            foreach ($items as $item) {
                $item->delete();
            }
            foreach ($totals as $slug => $qty) {
                $this->addMaterialBySlug($user->id, $slug, $qty);
            }
        });

        $materials = array_map(fn($slug, $qty) => ['slug' => $slug, 'qty' => $qty], array_keys($totals), $totals);

        return [
            'success'        => true,
            'dismantled'     => $items->count(),
            'materials'      => $materials,
            'gerard_comment' => $this->gerardComment(true, false, 'dismantle'),
        ];
    }

    // ─── Enchantment ─────────────────────────────────────────────────────────

    /** Catalogue des enchantements disponibles (slug → config) */
    private const ENCHANTMENTS = [
        // Enchantements de base (toujours disponibles)
        'aiguisage'    => ['name' => 'Aiguisage',     'gold' => 2000, 'tier' => 'base',  'stat' => 'atq', 'bonus_pct' => 8,  'materials' => ['ferraille' => 5, 'essence_mineure' => 2]],
        'renforcement' => ['name' => 'Renforcement',  'gold' => 2000, 'tier' => 'base',  'stat' => 'def', 'bonus_pct' => 8,  'materials' => ['ferraille' => 5, 'cuir' => 2]],
        'allegement'   => ['name' => 'Allègement',    'gold' => 2000, 'tier' => 'base',  'stat' => 'vit', 'bonus_pct' => 8,  'materials' => ['cuir' => 3, 'gemme_brute' => 2]],
        'chance'       => ['name' => 'Chance',        'gold' => 2500, 'tier' => 'base',  'stat' => 'cha', 'bonus_pct' => 8,  'materials' => ['gemme_brute' => 3, 'essence_mineure' => 1]],
        'vitalite'     => ['name' => 'Vitalité',      'gold' => 2500, 'tier' => 'base',  'stat' => 'hp',  'bonus_pct' => 10, 'materials' => ['cuir' => 5, 'essence_mineure' => 2]],
        'sagesse'      => ['name' => 'Sagesse',       'gold' => 2500, 'tier' => 'base',  'stat' => 'int', 'bonus_pct' => 8,  'materials' => ['essence_mineure' => 3, 'cristal_brut' => 1]],
        'solidite'     => ['name' => 'Solidité',      'gold' => 1500, 'tier' => 'base',  'stat' => 'durability', 'flat' => 50, 'materials' => ['ferraille' => 8]],
        // Enchantements avancés (Magus, non débloqués par défaut)
        'vampirisme'   => ['name' => 'Vampirisme',    'gold' => 8000, 'tier' => 'avance', 'effect' => 'lifesteal_5pct',    'materials' => ['cristal_brut' => 3, 'essence_majeure' => 1]],
        'precision'    => ['name' => 'Précision',     'gold' => 6000, 'tier' => 'avance', 'effect' => 'crit_plus_8',       'materials' => ['cristal_brut' => 2, 'gemme_brute' => 2]],
        'esquive'      => ['name' => 'Esquive',       'gold' => 6000, 'tier' => 'avance', 'effect' => 'dodge_plus_6',      'materials' => ['cristal_brut' => 2, 'cuir' => 3]],
        'indestructible' => ['name' => 'Indestructible', 'gold' => 10000, 'tier' => 'avance', 'stat' => 'durability', 'flat' => 999, 'materials' => ['cristal_brut' => 5, 'essence_majeure' => 2]],
        'prosperite'   => ['name' => 'Prospérité',   'gold' => 5000, 'tier' => 'avance', 'effect' => 'gold_plus_10',      'materials' => ['cristal_brut' => 2, 'gemme_brute' => 3]],
        // Enchantements élémentaires
        'flamme'       => ['name' => 'Flamme',        'gold' => 4000, 'tier' => 'elementaire', 'effect' => 'element_feu',   'materials' => ['cendre_volcanique' => 3, 'cristal_brut' => 1]],
        'givre'        => ['name' => 'Givre',         'gold' => 4000, 'tier' => 'elementaire', 'effect' => 'element_glace', 'materials' => ['glace_eternelle' => 3, 'cristal_brut' => 1]],
        'foudre'       => ['name' => 'Foudre',        'gold' => 4000, 'tier' => 'elementaire', 'effect' => 'element_foudre','materials' => ['eclat_foudre' => 3, 'cristal_brut' => 1]],
        'venin'        => ['name' => 'Venin',         'gold' => 4000, 'tier' => 'elementaire', 'effect' => 'element_poison','materials' => ['seve_toxique' => 3, 'cristal_brut' => 1]],
        'sacre'        => ['name' => 'Sacré',         'gold' => 5000, 'tier' => 'elementaire', 'effect' => 'element_sacre', 'materials' => ['essence_ombre' => 3, 'essence_majeure' => 1]],
    ];

    private const ENCHANT_MAX_EFFECTS = 2;
    private const ENCHANT_MIN_RARITY = ['rare', 'epique', 'legendaire', 'wtf'];

    /**
     * Retourne les enchantements disponibles pour l'utilisateur.
     * Les enchantements avancés ne sont disponibles que si ENCHANT_ADVANCED_UNLOCKED est vrai.
     */
    public function getAvailableEnchantments(User $user): array
    {
        $advancedUnlocked = (bool) $this->settings->get('ENCHANT_ADVANCED_UNLOCKED', 0);

        return collect(self::ENCHANTMENTS)
            ->filter(fn ($e) => $advancedUnlocked || $e['tier'] === 'base' || $e['tier'] === 'elementaire')
            ->map(fn ($e, $slug) => array_merge($e, ['slug' => $slug]))
            ->values()
            ->toArray();
    }

    /**
     * Applique un enchantement à un objet.
     * Conditions : objet Rare+, max 2 effets, si 2 effets → remplace le dernier.
     */
    public function enchant(User $user, int $itemId, string $enchantSlug): array
    {
        // Validate enchantment
        if (!isset(self::ENCHANTMENTS[$enchantSlug])) {
            return ['error' => "Enchantement '{$enchantSlug}' inconnu."];
        }
        $ench = self::ENCHANTMENTS[$enchantSlug];

        // Check advanced lock
        if ($ench['tier'] === 'avance' && !$this->settings->get('ENCHANT_ADVANCED_UNLOCKED', 0)) {
            return ['error' => 'Cet enchantement requiert la relation avec le Magus (niveau 75+).'];
        }

        // Load item
        $item = Item::where('id', $itemId)
            ->where('user_id', $user->id)
            ->whereNull('equipped_by_hero_id')
            ->first();

        if (!$item) {
            return ['error' => "Objet introuvable ou équipé."];
        }

        // Rarity check
        if (!in_array($item->rarity, self::ENCHANT_MIN_RARITY, true)) {
            return ['error' => "L'enchantement nécessite un objet Rare ou supérieur. Gérard regarde votre {$item->rarity} avec pitié."];
        }

        // Check materials
        foreach ($ench['materials'] as $slug => $qty) {
            $materialId = DB::table('materials')->where('slug', $slug)->value('id');
            $have = $materialId
                ? (DB::table('user_materials')->where('user_id', $user->id)->where('material_id', $materialId)->value('quantity') ?? 0)
                : 0;
            if ($have < $qty) {
                $name = DB::table('materials')->where('slug', $slug)->value('name') ?? $slug;
                return ['error' => "Matériaux insuffisants : {$name} (besoin {$qty}, vous avez {$have})."];
            }
        }

        if ($user->gold < $ench['gold']) {
            return ['error' => "Or insuffisant. Coût : {$ench['gold']} or."];
        }

        // Check existing effects
        $existingEffects = DB::table('item_effects')->where('item_id', $item->id)->orderBy('id')->get();
        $effectCount = $existingEffects->count();
        $replacedEffect = null;

        if ($effectCount >= self::ENCHANT_MAX_EFFECTS) {
            // Replace last enchantment
            $lastEffect = $existingEffects->last();
            if ($lastEffect) {
                $replacedEffect = $lastEffect->description;
                DB::table('item_effects')->where('id', $lastEffect->id)->delete();
            }
        }

        // Apply enchantment in transaction
        DB::transaction(function () use ($user, $item, $ench, $enchantSlug) {
            // Deduct gold
            $user->decrement('gold', $ench['gold']);

            // Deduct materials
            foreach ($ench['materials'] as $slug => $qty) {
                $materialId = DB::table('materials')->where('slug', $slug)->value('id');
                if ($materialId) {
                    DB::table('user_materials')
                        ->where('user_id', $user->id)
                        ->where('material_id', $materialId)
                        ->decrement('quantity', $qty);
                }
            }

            // Apply stat bonus if it's a stat enchantment
            if (isset($ench['stat'])) {
                if ($ench['stat'] === 'durability') {
                    $flat = $ench['flat'] ?? 50;
                    $newDurability = ($flat === 999)
                        ? 999
                        : min(999, $item->durability_max + $flat);
                    $item->durability_max = $newDurability;
                    $item->durability_current = min($item->durability_current + $flat, $newDurability);
                } else {
                    $bonusPct = $ench['bonus_pct'] ?? 8;
                    $statValue = $item->{$ench['stat']} ?? 0;
                    $bonus = max(1, intdiv($statValue * $bonusPct, 100));
                    $item->{$ench['stat']} = $statValue + $bonus;
                }
                $item->enchant_count = ($item->enchant_count ?? 0) + 1;
                $item->save();
            } else {
                $item->enchant_count = ($item->enchant_count ?? 0) + 1;
                $item->save();
            }

            // Add effect row
            $effectData = $this->buildEffectData($ench, $item);
            DB::table('item_effects')->insert([
                'item_id'       => $item->id,
                'effect_key'    => $enchantSlug,
                'description'   => $this->buildEffectDescription($ench),
                'effect_data'   => json_encode($effectData),
                'is_enchantment'=> 1,
            ]);
        });

        return [
            'success'         => true,
            'item_id'         => $item->id,
            'enchantment'     => $ench['name'],
            'gold_spent'      => $ench['gold'],
            'replaced_effect' => $replacedEffect,
            'gerard_comment'  => $this->gerardEnchantComment($enchantSlug),
        ];
    }

    private function buildEffectData(array $ench, Item $item): array
    {
        if (isset($ench['stat']) && $ench['stat'] !== 'durability') {
            return ['stat' => $ench['stat'], 'bonus_pct' => $ench['bonus_pct'] ?? 8];
        }
        if (isset($ench['effect'])) {
            if (str_starts_with($ench['effect'], 'element_')) {
                $element = substr($ench['effect'], strlen('element_'));
                return ['type' => 'element_change', 'element' => $element, 'dmg_bonus_pct' => 10];
            }
            return ['type' => $ench['effect']];
        }
        return ['type' => 'durability_bonus', 'flat' => $ench['flat'] ?? 50];
    }

    private function buildEffectDescription(array $ench): string
    {
        if (isset($ench['stat']) && $ench['stat'] !== 'durability') {
            $pct = $ench['bonus_pct'] ?? 8;
            return strtoupper($ench['stat']) . " +{$pct}%";
        }
        if (isset($ench['flat'])) {
            return $ench['flat'] === 999 ? 'Durabilité infinie' : "Durabilité +{$ench['flat']}";
        }
        $effectDescriptions = [
            'lifesteal_5pct'  => 'Soigne 5% des dégâts infligés',
            'crit_plus_8'     => 'Critique +8%',
            'dodge_plus_6'    => 'Esquive +6%',
            'gold_plus_10'    => '+10% or trouvé',
            'element_feu'     => 'Dégâts Feu +10%',
            'element_glace'   => 'Dégâts Glace, 8% chance Ralenti',
            'element_foudre'  => 'Dégâts Foudre, 5% chance Étourdi',
            'element_poison'  => 'Dégâts Poison, empoisonne 2 tours',
            'element_sacre'   => 'Dégâts Sacré, +15% dégâts morts-vivants',
        ];
        return $effectDescriptions[$ench['effect'] ?? ''] ?? $ench['name'];
    }

    private function gerardEnchantComment(string $slug): ?string
    {
        if (rand(1, 100) > $this->settings->get('CRAFT_GERARD_HUMOR_CHANCE', 30)) {
            return null;
        }
        return collect([
            "Enchantement '{$slug}' appliqué. Je comprends pas comment ça marche. Mais ça marche.",
            "Voilà. J'ai suivi la procédure. Si quelque chose brûle, c'est pas ma faute.",
            "Normalement c'est mieux maintenant. Normalement.",
            "J'ai mis des runes dessus. Les runes c'est ma passion. Ma passion incomprise.",
            "L'objet est enchanté. Il vous regarde différemment maintenant.",
        ])->random();
    }

    // ─── Recipe craft ────────────────────────────────────────────────────────

    public function craftRecipe(User $user, int $recipeId): array
    {
        $recipe = Recipe::findOrFail($recipeId);

        // Check if discovered (unless non-discoverable base recipe)
        if ($recipe->is_discoverable) {
            $known = DB::table('user_recipes')
                ->where('user_id', $user->id)
                ->where('recipe_id', $recipeId)
                ->exists();
            if (!$known) {
                return ['error' => 'Recette inconnue. Continuez à crafter pour la découvrir.'];
            }
        }

        // Check materials
        foreach ($recipe->ingredients as $ing) {
            $materialId = DB::table('materials')->where('slug', $ing['slug'])->value('id');
            $have = $materialId
                ? (DB::table('user_materials')->where('user_id', $user->id)->where('material_id', $materialId)->value('quantity') ?? 0)
                : 0;
            if ($have < $ing['qty']) {
                $mat = DB::table('materials')->where('slug', $ing['slug'])->value('name') ?? $ing['slug'];
                return ['error' => "Matériaux insuffisants : {$mat} (besoin {$ing['qty']}, vous avez {$have})."];
            }
        }

        if ($user->gold < $recipe->gold_cost) {
            return ['error' => "Or insuffisant. Coût : {$recipe->gold_cost} or."];
        }

        // Deduct materials and gold, create item
        $resultItem = DB::transaction(function () use ($user, $recipe) {
            $user->decrement('gold', $recipe->gold_cost);
            foreach ($recipe->ingredients as $ing) {
                $materialId = DB::table('materials')->where('slug', $ing['slug'])->value('id');
                if ($materialId) {
                    DB::table('user_materials')
                        ->where('user_id', $user->id)
                        ->where('material_id', $materialId)
                        ->decrement('quantity', $ing['qty']);
                }
            }
            return $this->createRecipeItem($user, $recipe);
        });

        $gerardComment = rand(1, 100) <= $this->settings->get('CRAFT_GERARD_HUMOR_CHANCE', 30)
            ? $this->gerardCommentRecipe()
            : null;

        return [
            'success'        => true,
            'result_item'    => $this->itemResponse($resultItem),
            'gold_spent'     => $recipe->gold_cost,
            'gerard_comment' => $gerardComment,
        ];
    }

    // ─── Inventory helpers ───────────────────────────────────────────────────

    public function getUserMaterials(User $user): array
    {
        return DB::table('user_materials')
            ->join('materials', 'user_materials.material_id', '=', 'materials.id')
            ->where('user_materials.user_id', $user->id)
            ->where('user_materials.quantity', '>', 0)
            ->select('materials.name', 'materials.slug', 'materials.description', 'user_materials.quantity')
            ->get()
            ->toArray();
    }

    public function getKnownRecipes(User $user): array
    {
        $baseRecipes = Recipe::where('is_discoverable', false)->get();
        $learnedIds  = DB::table('user_recipes')->where('user_id', $user->id)->pluck('recipe_id');
        $learnedRecipes = Recipe::whereIn('id', $learnedIds)->get();

        return $baseRecipes->merge($learnedRecipes)->map(fn($r) => [
            'id'              => $r->id,
            'name'            => $r->name,
            'description'     => $r->description,
            'ingredients'     => $r->ingredients,
            'gold_cost'       => $r->gold_cost,
            'result_name'     => $r->result_name,
            'result_rarity'   => $r->result_rarity,
            'result_slot'     => $r->result_slot,
            'result_type'     => $r->result_type,
        ])->values()->toArray();
    }

    // ─── Internals ───────────────────────────────────────────────────────────

    private function getMaterialsForDismantle(Item $item): array
    {
        $min = $this->settings->get('CRAFT_DISMANTLE_MATERIAL_MIN', 1);
        $max = $this->settings->get('CRAFT_DISMANTLE_MATERIAL_MAX', 5);
        $qty = rand($min, $max);

        $rarityMaterials = [
            'commun'      => ['ferraille'],
            'peu_commun'  => ['ferraille', 'essence_mineure'],
            'rare'        => ['essence_mineure', 'cristal_brut'],
            'epique'      => ['cristal_brut', 'essence_majeure'],
            'legendaire'  => ['essence_majeure', 'fragment_stellaire'],
            'wtf'         => ['fragment_stellaire', 'ficelle_cosmique'],
        ];

        $slugs = $rarityMaterials[$item->rarity] ?? ['ferraille'];
        $materials = [];

        // Primary material
        $primary = $slugs[0];
        $primaryQty = $qty;
        if (count($slugs) > 1 && rand(1, 100) <= 30) {
            $primaryQty = intdiv($qty * 2, 3);
        }
        $materials[] = ['slug' => $primary, 'qty' => max(1, $primaryQty)];

        // Secondary bonus material
        if (count($slugs) > 1) {
            $materials[] = ['slug' => $slugs[1], 'qty' => 1];
        }

        // Slot bonus
        $slotBonus = match ($item->slot) {
            'arme', 'armure' => ['ferraille', 1],
            'bottes'         => ['cuir', 1],
            'accessoire'     => ['gemme_brute', 1],
            default          => null,
        };
        if ($slotBonus) {
            $materials[] = ['slug' => $slotBonus[0], 'qty' => $slotBonus[1]];
        }

        return $materials;
    }

    private function grantMaterialForRarity(User $user, string $rarity, int $qty): void
    {
        $slug = match ($rarity) {
            'peu_commun'  => 'essence_mineure',
            'rare'        => 'cristal_brut',
            'epique'      => 'essence_majeure',
            'legendaire'  => 'fragment_stellaire',
            default       => 'ferraille',
        };
        $this->addMaterialBySlug($user->id, $slug, $qty);
    }

    private function addMaterialBySlug(int $userId, string $slug, int $qty): void
    {
        $materialId = DB::table('materials')->where('slug', $slug)->value('id');
        if (!$materialId) {
            return;
        }
        $existing = DB::table('user_materials')
            ->where('user_id', $userId)
            ->where('material_id', $materialId)
            ->first();
        if ($existing) {
            DB::table('user_materials')
                ->where('user_id', $userId)
                ->where('material_id', $materialId)
                ->update(['quantity' => $existing->quantity + $qty, 'updated_at' => now()]);
        } else {
            DB::table('user_materials')->insert([
                'user_id'     => $userId,
                'material_id' => $materialId,
                'quantity'    => $qty,
                'updated_at'  => now(),
            ]);
        }
    }

    private function discoverRecipe(User $user): ?Recipe
    {
        // Find an undiscovered discoverable recipe in user's zone
        $known = DB::table('user_recipes')->where('user_id', $user->id)->pluck('recipe_id');
        $recipe = Recipe::where('is_discoverable', true)
            ->whereNotIn('id', $known)
            ->inRandomOrder()
            ->first();

        if ($recipe) {
            DB::table('user_recipes')->insert([
                'user_id'       => $user->id,
                'recipe_id'     => $recipe->id,
                'discovered_at' => now(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
        return $recipe;
    }

    private function createRecipeItem(User $user, Recipe $recipe): Item
    {
        $stats  = $recipe->result_stats ?? [];
        $level  = $recipe->result_level;
        $rarity = $recipe->result_rarity;
        $mult   = [
            'commun' => 1, 'peu_commun' => 2, 'rare' => 3,
            'epique' => 4, 'legendaire' => 5, 'wtf' => 6,
        ][$rarity] ?? 1;
        $sellBase = self::RARITY_BASE_SELL[$rarity] ?? 5;

        return Item::create([
            'user_id'     => $user->id,
            'name'        => $recipe->result_name,
            'description' => $recipe->result_description,
            'rarity'      => $rarity,
            'slot'        => $recipe->result_slot ?? 'accessoire',
            'element'     => 'physique',
            'item_level'  => $level,
            'atq'         => $stats['atq'] ?? 0,
            'def'         => $stats['def'] ?? 0,
            'hp'          => $stats['hp'] ?? 0,
            'vit'         => $stats['vit'] ?? 0,
            'cha'         => $stats['cha'] ?? 0,
            'int'         => $stats['int'] ?? 0,
            'durability_max'     => (int) $this->settings->get('LOOT_DURABILITY_BASE', 100),
            'durability_current' => (int) $this->settings->get('LOOT_DURABILITY_BASE', 100),
            'sell_value'         => max(1, intdiv($level * $sellBase * 30, 100)),
            'is_ai_generated'    => false,
        ]);
    }

    private function gerardComment(bool $success, bool $isCrit, string $context = 'fusion'): ?string
    {
        if (rand(1, 100) > $this->settings->get('CRAFT_GERARD_HUMOR_CHANCE', 30)) {
            return null;
        }
        if ($context === 'dismantle') {
            return collect([
                'Vous êtes sûr ? Il était pas si mal ce... ah, c\'est fait. Bon.',
                'Démontage express ! Par contre j\'ai mis le feu à l\'établi. C\'est normal.',
                'Ça fait des beaux matériaux. Et un tas de regrets.',
            ])->random();
        }
        if (!$success) {
            return collect([
                'Oups. C\'est... fondu. Désolé. Enfin bon, c\'était déjà pas terrible.',
                'J\'ai cassé vos trois trucs. Par contre j\'ai trouvé ce bout de ferraille, ça vous dit ?',
                'Fusion ratée. Je suis surpris. Enfin non.',
            ])->random();
        }
        if ($isCrit) {
            return collect([
                'ATTENDEZ — ça brille ?! J\'ai jamais fait ça de ma vie !',
                'Alors là... je sais pas ce que j\'ai fait, mais c\'est magnifique.',
                'Un critique ! Vous avez vu ? Moi j\'ai les mains qui tremblent.',
            ])->random();
        }
        return collect([
            'Eh ben, ça a marché ! J\'suis aussi surpris que vous.',
            'Un chef-d\'œuvre ! Enfin, c\'est un mot fort. Disons que ça coupe.',
            'Voilà. C\'est fait. J\'en reviens toujours pas.',
        ])->random();
    }

    private function gerardCommentRecipe(): ?string
    {
        return collect([
            'Oh, j\'ai trouvé un truc ! Enfin, je crois. C\'est soit une recette, soit une liste de courses.',
            'Fait selon les règles de l\'art. L\'art de Gérard. C\'est un art très particulier.',
            'C\'est exactement ce que demandait la recette. Je pense. Je la relis demain.',
        ])->random();
    }

    private function itemResponse(Item $item): array
    {
        return [
            'id'       => $item->id,
            'name'     => $item->name,
            'rarity'   => $item->rarity,
            'slot'     => $item->slot,
            'element'  => $item->element,
            'item_level' => $item->item_level,
            'atq' => $item->atq, 'def' => $item->def, 'hp' => $item->hp,
            'vit' => $item->vit, 'cha' => $item->cha, 'int' => $item->int,
            'sell_value' => $item->sell_value,
        ];
    }
}
