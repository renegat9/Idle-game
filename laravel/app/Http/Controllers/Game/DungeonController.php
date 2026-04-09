<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\DungeonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DungeonController extends Controller
{
    public function __construct(
        private readonly DungeonService $dungeonService,
    ) {}

    /**
     * GET /dungeon
     * Returns current active dungeon status, or cooldown info if none is active.
     */
    public function status(Request $request): JsonResponse
    {
        $user   = $request->user();
        $status = $this->dungeonService->getStatus($user);

        return response()->json($status);
    }

    /**
     * POST /dungeon/start
     * Body: { "zone_id": int }
     * Creates a new dungeon session for the authenticated user.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|integer|min:1',
        ]);

        $user   = $request->user();
        $result = $this->dungeonService->startDungeon($user, $validated['zone_id']);

        if (!$result['success']) {
            return response()->json(['message' => $result['error'], 'available_at' => $result['available_at'] ?? null], 422);
        }

        return response()->json($result, 201);
    }

    /**
     * POST /dungeon/{dungeonId}/enter
     * Resolves the current room and advances the dungeon state.
     */
    public function enter(Request $request, int $dungeonId): JsonResponse
    {
        $user   = $request->user();
        $result = $this->dungeonService->enterRoom($user, $dungeonId);

        if (!$result['success']) {
            return response()->json(['message' => $result['error']], 422);
        }

        $status = ($result['dungeon_over'] ?? false) ? 200 : 200;

        return response()->json($result, $status);
    }

    /**
     * POST /dungeon/{dungeonId}/abandon
     * Abandons the active dungeon, losing all accumulated progress.
     */
    public function abandon(Request $request, int $dungeonId): JsonResponse
    {
        $user   = $request->user();
        $result = $this->dungeonService->abandon($user, $dungeonId);

        if (!$result['success']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result);
    }
}
