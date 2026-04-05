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
}
