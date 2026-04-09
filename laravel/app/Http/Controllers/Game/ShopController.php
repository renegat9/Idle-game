<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shop,
    ) {}

    /**
     * GET /shop?zone_id=1
     * Returns the active shop inventory for the authenticated user in the given zone.
     * Falls back to the user's current_zone_id when zone_id is omitted.
     */
    public function index(Request $request): JsonResponse
    {
        $user   = $request->user();
        $zoneId = (int) ($request->query('zone_id') ?: $user->current_zone_id);

        if (!$zoneId) {
            return response()->json(['message' => 'Zone non spécifiée et aucune zone courante.'], 422);
        }

        $shopData = $this->shop->getShop($user, $zoneId);

        return response()->json($shopData);
    }

    /**
     * POST /shop/buy
     * Body: { "item_id": int }
     * Purchases the specified shop item and adds it to the user's inventory.
     */
    public function buy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|integer',
        ]);

        $user   = $request->user();
        $result = $this->shop->buy($user, $validated['item_id']);

        if (!$result['success']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json([
            'message'        => "Achat effectué. Le marchand vous remercie avec un sourire douteux.",
            'item'           => $result['item'],
            'gold_spent'     => $result['gold_spent'],
            'new_gold_total' => $user->gold,
        ], 201);
    }
}
