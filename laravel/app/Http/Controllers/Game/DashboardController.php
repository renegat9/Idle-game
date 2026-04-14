<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\NarratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly NarratorService $narrator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();
        $user->load([
            'activeHeroes.race',
            'activeHeroes.gameClass',
            'activeHeroes.trait_',
            'activeHeroes.equippedItems',
            'currentZone',
        ]);

        // Ne pas déclencher le calcul offline ici — uniquement via POST /exploration/collect.
        // Sinon chaque chargement du dashboard consomme le temps accumulé.
        $offlineResult = null;

        $exploration = $user->activeExploration()->with('zone')->first();

        $recentEvents = DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        $unreadCount = DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'gold' => $user->gold,
                'level' => $user->level,
                'xp' => $user->xp,
                'xp_to_next_level' => $user->xp_to_next_level,
                'narrator_frequency' => $user->narrator_frequency,
            ],
            'heroes' => $user->activeHeroes->map(function ($hero) {
                $stats = $hero->computedStats();
                return [
                    'id' => $hero->id,
                    'name' => $hero->name,
                    'level' => $hero->level,
                    'race' => ['name' => $hero->race->name, 'slug' => $hero->race->slug],
                    'class' => ['name' => $hero->gameClass->name, 'slug' => $hero->gameClass->slug, 'role' => $hero->gameClass->role],
                    'trait' => $hero->trait_ ? ['name' => $hero->trait_->name, 'slug' => $hero->trait_->slug] : null,
                    'image_path' => $hero->image_path,
                    'current_hp' => $hero->current_hp,
                    'max_hp' => $stats['max_hp'],
                    'atq' => $stats['atq'],
                    'def' => $stats['def'],
                    'xp' => $hero->xp,
                    'xp_to_next_level' => $hero->xp_to_next_level,
                ];
            })->values(),
            'exploration' => $exploration ? [
                'is_active' => true,
                'zone_id' => $exploration->zone_id,
                'zone_name' => $exploration->zone->name,
                'started_at' => $exploration->started_at,
                'last_collected_at' => $exploration->last_collected_at,
            ] : ['is_active' => false],
            'offline_result' => $offlineResult,
            'recent_events' => $recentEvents,
            'unread_events_count' => $unreadCount,
            'narrator_comment' => $this->narrator->getComment(
                $offlineResult && $offlineResult['combats_simulated'] > 0 ? 'offline_return' : 'default'
            ),
        ]);
    }

    /**
     * Endpoint de polling léger — < 50ms.
     * NE déclenche PAS le calcul offline.
     */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();

        $unreadCount = DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $latestEvent = DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->orderByDesc('occurred_at')
            ->first();

        $heroHps = DB::table('heroes')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'name', 'current_hp', 'max_hp', 'level')
            ->get();

        return response()->json([
            'unread_events_count' => $unreadCount,
            'latest_narrator' => $latestEvent?->narrator_text,
            'gold' => (int) DB::table('users')->where('id', $user->id)->value('gold'),
            'hero_status' => $heroHps,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
