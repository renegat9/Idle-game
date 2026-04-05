<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use App\Services\TalentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function __construct(private readonly TalentService $talents) {}

    /**
     * GET /heroes/{hero}/talents
     * Returns the full talent tree for the hero, with unlock status per talent.
     */
    public function index(Request $request, int $heroId): JsonResponse
    {
        $hero = Hero::where('id', $heroId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$hero) {
            return response()->json(['message' => 'Héros introuvable.'], 404);
        }

        return response()->json($this->talents->getTree($hero));
    }

    /**
     * POST /heroes/{hero}/talents/{talentId}/allocate
     * Allocates one talent point to the given talent.
     */
    public function allocate(Request $request, int $heroId, int $talentId): JsonResponse
    {
        $hero = Hero::where('id', $heroId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$hero) {
            return response()->json(['message' => 'Héros introuvable.'], 404);
        }

        $result = $this->talents->allocate($hero, $talentId);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json($result, 201);
    }

    /**
     * POST /heroes/{hero}/talents/reset
     * Resets all talents for the hero. Costs gold, scaled by reset count.
     */
    public function reset(Request $request, int $heroId): JsonResponse
    {
        $hero = Hero::where('id', $heroId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$hero) {
            return response()->json(['message' => 'Héros introuvable.'], 404);
        }

        $user   = $request->user();
        $result = $this->talents->reset($hero, $user);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $user->refresh();

        return response()->json([
            'message'        => $result['message'],
            'gold_spent'     => $result['gold_spent'],
            'new_gold_total' => $user->gold,
        ]);
    }
}
