<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\QuestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    public function __construct(private readonly QuestService $questService) {}

    public function index(Request $request): JsonResponse
    {
        $quests = $this->questService->availableQuests($request->user());
        return response()->json(['quests' => $quests]);
    }

    public function start(Request $request, int $questId): JsonResponse
    {
        $result = $this->questService->startQuest($request->user(), $questId);
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }
        return response()->json($result, 201);
    }

    public function daily(Request $request): JsonResponse
    {
        $result = $this->questService->dailyQuests($request->user());
        return response()->json($result);
    }

    public function choose(Request $request, int $userQuestId): JsonResponse
    {
        $validated = $request->validate([
            'choice_id' => 'required|string|in:A,B,C,D',
            'hero_id'   => 'nullable|integer',
        ]);

        $result = $this->questService->chooseOption(
            $request->user(),
            $userQuestId,
            $validated['choice_id'],
            $validated['hero_id'] ?? null,
        );

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result);
    }
}
