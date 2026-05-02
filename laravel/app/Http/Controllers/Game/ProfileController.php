<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    /**
     * GET /api/profile
     * Returns user profile, hero stats summary, and recent economy history.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load(['activeHeroes.race', 'activeHeroes.gameClass']);

        // Economy history (last 20 transactions)
        $economyLog = DB::table('economy_log')
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get(['transaction_type', 'source', 'amount', 'balance_after', 'description', 'occurred_at']);

        // Basic stats counters
        $stats = [
            'total_kills'   => (int) DB::table('combat_log')
                ->where('user_id', $user->id)
                ->where('result', 'victory')
                ->count(),
            'total_defeats' => (int) DB::table('combat_log')
                ->where('user_id', $user->id)
                ->where('result', 'defeat')
                ->count(),
            'quests_done'   => (int) DB::table('user_quests')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'items_crafted' => (int) DB::table('economy_log')
                ->where('user_id', $user->id)
                ->where('source', 'crafting')
                ->count(),
            'dungeons_done' => (int) DB::table('dungeons')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'gold_earned'   => (int) DB::table('economy_log')
                ->where('user_id', $user->id)
                ->where('transaction_type', 'gain')
                ->sum('amount'),
            'gold_spent'    => (int) DB::table('economy_log')
                ->where('user_id', $user->id)
                ->where('transaction_type', 'depense')
                ->sum('amount'),
        ];

        return response()->json([
            'user' => [
                'id'                  => $user->id,
                'username'            => $user->username,
                'email'               => $user->email,
                'level'               => $user->level,
                'xp'                  => $user->xp,
                'xp_to_next_level'    => $user->xp_to_next_level,
                'gold'                => $user->gold,
                'narrator_frequency'  => $user->narrator_frequency,
                'created_at'          => $user->created_at,
            ],
            'heroes'           => $user->activeHeroes->map(fn($h) => [
                'id'    => $h->id,
                'name'  => $h->name,
                'level' => $h->level,
                'race'  => $h->race->name,
                'class' => $h->gameClass->name,
            ])->values(),
            'stats'            => $stats,
            'economy_log'      => $economyLog,
            'ai_budget'        => $this->gemini->budgetStatus(),
        ]);
    }

    /**
     * PATCH /api/profile
     * Update narrator_frequency preference.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'narrator_frequency' => ['sometimes', Rule::in(['never', 'rare', 'normal', 'annoying'])],
            'username'           => ['sometimes', 'string', 'min:3', 'max:30',
                Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour. Le Narrateur prend note.',
            'user'    => [
                'username'           => $user->username,
                'narrator_frequency' => $user->narrator_frequency,
            ],
        ]);
    }
}
