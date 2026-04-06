<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\WorldBoss;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/music/current
 *
 * Returns the appropriate music track based on the user's current game state.
 * Priority: world_boss_active > dungeon > tavern > exploration > idle
 *
 * Leverages GeminiService::generateTavernMusic() which always uses static fallback
 * since MusicFX is not publicly available.
 */
class MusicController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    /**
     * Determine and return the current music track for the authenticated user.
     *
     * GET /api/music/current
     */
    public function current(Request $request): JsonResponse
    {
        $user  = $request->user();
        $style = $this->resolveStyle($user);
        $track = $this->gemini->generateTavernMusic($style);

        return response()->json([
            'style'     => $track['style'],
            'file_path' => $track['file_path'],
            'context'   => $style,
        ]);
    }

    /**
     * Resolve the appropriate music style based on user game context.
     */
    private function resolveStyle(\App\Models\User $user): string
    {
        // World boss active → boss music
        $bossActive = WorldBoss::where('status', 'active')->exists();
        if ($bossActive) {
            $isContributing = DB::table('boss_contributions')
                ->join('world_bosses', 'world_bosses.id', '=', 'boss_contributions.boss_id')
                ->where('boss_contributions.user_id', $user->id)
                ->where('world_bosses.status', 'active')
                ->exists();

            if ($isContributing) {
                return 'boss';
            }
        }

        // User currently in a quest (in_progress)
        $inQuest = DB::table('user_quests')
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->exists();

        if ($inQuest) {
            return 'exploration';
        }

        // User recently completed a combat (check idle/exploration activity within last hour)
        $recentWin = DB::table('combat_log')
            ->where('user_id', $user->id)
            ->where('result', 'victory')
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if ($recentWin) {
            return 'victoire_epique';
        }

        // Recent defeat
        $recentLoss = DB::table('combat_log')
            ->where('user_id', $user->id)
            ->where('result', 'defeat')
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if ($recentLoss) {
            return 'defaite';
        }

        // Idle / at tavern or no specific context
        return 'taverne';
    }
}
