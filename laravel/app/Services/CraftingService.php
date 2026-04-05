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
            // Failure: destroy all 3, refund 1 material
            DB::transaction(function () use ($user, $items, $fusionCost) {
                $user->decrement('gold', $fusionCost);
                Item::whereIn('id', $items->pluck('id'))->delete();
                // Give back 1 material of appropriate type
                $this->grantMaterialForRarity($user, $items->first()->rarity, 1);
            });

            return [
                'success' => false,
                'message' => 'La fusion a échoué. Les objets sont partis en fumée.',
                'gerard_comment' => $gerardComment,
                'narrator_comment' => $this->narrator->getComment('craft_failure', []),
            ];
        }

        // Success: determine result rarity
        $critRoll       = rand(1, 100);
        $isCrit         = ($critRoll <= $critChance);
        $resultRarityIdx = min($rarityIdx + ($isCrit ? 2 : 1), count(self::RARITY_ORDER) - 1);
        $resultRarity    = self::RARITY_ORDER[$resultRarityIdx];

        // Determine result slot
        $slots = $items->pluck('slot')->values()->toArray();
        $resultSlot = $sameSlot ? $slots[0] : $slots[array_rand($slots)];

        $resultLevel = min($avgLevel + rand(0, 3), 99);
        $resultItem  = $this->loot->generateItemForCrafting($user, $resultRarity, $resultSlot, $resultLevel);

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
            'success'        => true,
            'is_critical'    => $isCrit,
            'result_item'    => $this->itemResponse($resultItem),
            'gold_spent'     => $fusionCost,
            'gerard_comment' => $this->gerardComment(true, $isCrit),
            'new_recipe'     => $newRecipe?->name,
        ];
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
            'max_durability' => $this->settings->get('LOOT_DURABILITY_BASE', 100),
            'current_durability' => $this->settings->get('LOOT_DURABILITY_BASE', 100),
            'sell_value'  => intdiv($level * $sellBase * 30, 100),
            'ai_generated' => false,
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
