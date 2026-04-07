<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\ConsumableService;
use App\Services\NarratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsumableController extends Controller
{
    public function __construct(
        private readonly ConsumableService $consumables,
        private readonly NarratorService $narrator
    ) {}

    /**
     * GET /consumables — inventaire consommables du joueur
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->consumables->inventory($request->user());

        return response()->json(['consumables' => $items]);
    }

    /**
     * GET /consumables/catalog — catalogue complet (pour la boutique / quêtes)
     */
    public function catalog(): JsonResponse
    {
        $catalog = \Illuminate\Support\Facades\DB::table('consumables')
            ->orderBy('rarity')
            ->orderBy('buy_price')
            ->get()
            ->map(fn($c) => (array) $c)
            ->values();

        return response()->json(['catalog' => $catalog]);
    }

    /**
     * POST /consumables/{slug}/use — utiliser un consommable
     */
    public function use(Request $request, string $slug): JsonResponse
    {
        try {
            $result = $this->consumables->use($request->user(), $slug);
            $comment = $this->narrator->getComment('consumable_used', []);

            return response()->json([
                'message'          => $result['consumable_name'] . ' utilisé !',
                'narrator_comment' => $comment,
                'result'           => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
