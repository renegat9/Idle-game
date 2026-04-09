<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\ReputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReputationController extends Controller
{
    public function __construct(
        private readonly ReputationService $reputationService
    ) {}

    /**
     * GET /api/reputation
     * Retourne la réputation du joueur pour toutes les zones.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $reputations = $this->reputationService->getReputation($user->id);

        return response()->json([
            'reputations' => $reputations,
        ]);
    }

    /**
     * GET /api/reputation/{zoneId}
     * Retourne la réputation du joueur pour une zone spécifique.
     */
    public function show(Request $request, int $zoneId): JsonResponse
    {
        $user = $request->user();

        // Vérifier que la zone existe
        $zone = \Illuminate\Support\Facades\DB::table('zones')->find($zoneId);
        if (!$zone) {
            return response()->json(['message' => 'Zone introuvable.'], 404);
        }

        $reputations = $this->reputationService->getReputation($user->id, $zoneId);

        if (empty($reputations)) {
            return response()->json([
                'zone_id'    => $zoneId,
                'zone_name'  => $zone->name,
                'zone_slug'  => $zone->slug,
                'reputation' => 0,
                'tier'       => $this->reputationService->getReputationTier(0),
            ]);
        }

        return response()->json($reputations[0]);
    }
}
