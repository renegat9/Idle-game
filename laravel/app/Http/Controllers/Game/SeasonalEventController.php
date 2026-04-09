<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\SeasonalEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonalEventController extends Controller
{
    public function __construct(private readonly SeasonalEventService $events) {}

    /**
     * GET /api/events/current
     * Returns active seasonal events and aggregated modifiers.
     */
    public function current(Request $request): JsonResponse
    {
        $active    = $this->events->getActiveEvents();
        $modifiers = $this->events->getActiveModifiers();

        return response()->json([
            'active_events' => array_map(fn($e) => [
                'slug'                => $e->slug,
                'name'                => $e->name,
                'description'         => $e->description,
                'flavor_text'         => $e->flavor_text,
                'xp_bonus_pct'        => $e->xp_bonus_pct,
                'gold_bonus_pct'      => $e->gold_bonus_pct,
                'loot_bonus_pct'      => $e->loot_bonus_pct,
                'rare_loot_bonus_pct' => $e->rare_loot_bonus_pct,
                'quest_type_unlock'   => $e->quest_type_unlock,
                'ends_day'            => $e->end_day,
                'ends_month'          => $e->end_month,
            ], $active),
            'modifiers'     => $modifiers,
            'has_event'     => count($active) > 0,
        ]);
    }

    /**
     * GET /api/events
     * Returns all seasonal events (for calendar display).
     */
    public function index(Request $request): JsonResponse
    {
        $events = DB::table('seasonal_events')
            ->where('is_active', true)
            ->orderBy('start_month')
            ->orderBy('start_day')
            ->get();

        return response()->json([
            'events' => $events->map(fn($e) => [
                'slug'        => $e->slug,
                'name'        => $e->name,
                'description' => $e->description,
                'start_month' => $e->start_month,
                'start_day'   => $e->start_day,
                'end_month'   => $e->end_month,
                'end_day'     => $e->end_day,
                'xp_bonus_pct'  => $e->xp_bonus_pct,
                'gold_bonus_pct'=> $e->gold_bonus_pct,
                'loot_bonus_pct'=> $e->loot_bonus_pct,
            ])->values(),
        ]);
    }
}
