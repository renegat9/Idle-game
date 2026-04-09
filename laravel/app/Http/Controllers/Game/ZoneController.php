<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $zones = Zone::orderBy('order_index')->get();

        $userProgress = $user->zoneProgress()->pluck('boss_defeated', 'zone_id');
        $userProgressCombats = $user->zoneProgress()->pluck('total_victories', 'zone_id');

        $zonesData = $zones->map(function ($zone) use ($userProgress, $userProgressCombats, $user) {
            $unlocked = $zone->order_index === 1 || isset($userProgress[$zone->id]);
            $bossDefeated = $userProgress[$zone->id] ?? false;

            return [
                'id' => $zone->id,
                'slug' => $zone->slug,
                'name' => $zone->name,
                'description' => $zone->description,
                'level_min' => $zone->level_min,
                'level_max' => $zone->level_max,
                'dominant_element' => $zone->dominant_element,
                'is_magical' => $zone->is_magical,
                'order_index' => $zone->order_index,
                'is_unlocked' => $unlocked,
                'boss_defeated' => (bool) $bossDefeated,
                'total_victories' => $userProgressCombats[$zone->id] ?? 0,
                'is_current' => $user->current_zone_id === $zone->id,
            ];
        });

        return response()->json(['zones' => $zonesData]);
    }
}
