<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\CraftingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CraftingController extends Controller
{
    public function __construct(private readonly CraftingService $craftingService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'materials' => $this->craftingService->getUserMaterials($user),
            'recipes'   => $this->craftingService->getKnownRecipes($user),
        ]);
    }

    public function fuse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|size:3',
            'item_ids.*' => 'required|integer',
        ]);

        $result = $this->craftingService->fuse($request->user(), $validated['item_ids']);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result, 201);
    }

    public function dismantle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|integer',
        ]);

        $result = $this->craftingService->dismantle($request->user(), $validated['item_id']);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result);
    }

    public function craft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipe_id' => 'required|integer',
        ]);

        $result = $this->craftingService->craftRecipe($request->user(), $validated['recipe_id']);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result, 201);
    }

    /**
     * GET /api/crafting/enchantments
     * Retourne les enchantements disponibles pour le joueur.
     */
    public function enchantments(Request $request): JsonResponse
    {
        $available = $this->craftingService->getAvailableEnchantments($request->user());
        return response()->json(['enchantments' => $available]);
    }

    /**
     * POST /api/crafting/enchant
     * Applique un enchantement à un objet.
     */
    public function enchant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id'       => 'required|integer',
            'enchantment'   => 'required|string|max:50',
        ]);

        $result = $this->craftingService->enchant(
            $request->user(),
            $validated['item_id'],
            $validated['enchantment']
        );

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result, 201);
    }
}
