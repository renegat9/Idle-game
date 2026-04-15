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
        private readonly NarratorService $narrator,
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
                $trait = $hero->trait_;
                return [
                    'id'                       => $hero->id,
                    'name'                     => $hero->name,
                    'level'                    => $hero->level,
                    'xp'                       => $hero->xp,
                    'xp_to_next_level'         => $hero->xp_to_next_level,
                    'slot_index'               => $hero->slot_index,
                    'is_active'                => $hero->is_active,
                    'deaths'                   => $hero->deaths,
                    'image_path'               => $hero->image_path,
                    'talent_points'            => $hero->talent_points,
                    'race'  => ['id' => $hero->race->id,      'name' => $hero->race->name,      'slug' => $hero->race->slug],
                    'class' => ['id' => $hero->gameClass->id, 'name' => $hero->gameClass->name, 'slug' => $hero->gameClass->slug, 'role' => $hero->gameClass->role, 'key_skill_name' => $hero->gameClass->key_skill_name],
                    'trait' => $trait ? ['id' => $trait->id, 'name' => $trait->name, 'slug' => $trait->slug, 'description' => $trait->description, 'flavor_text' => $trait->flavor_text] : null,
                    'computed_stats'           => $stats,
                    'equipped_items'           => $hero->equippedItems->map(fn($item) => [
                        'id' => $item->id, 'name' => $item->name, 'rarity' => $item->rarity,
                        'slot' => $item->slot, 'element' => $item->element,
                        'atq' => $item->atq, 'def' => $item->def, 'hp' => $item->hp,
                        'vit' => $item->vit, 'cha' => $item->cha, 'int' => $item->int,
                        'image_url' => $item->image_url,
                    ])->values(),
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
     * NE déclenche PAS le calcul offline (géré par heroes:heal-at-rest via cron).
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
