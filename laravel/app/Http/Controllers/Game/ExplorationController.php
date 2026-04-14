<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\UserExploration;
use App\Models\UserZoneProgress;
use App\Models\Zone;
use App\Services\IdleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExplorationController extends Controller
{
    public function __construct(
        private readonly IdleService $idleService
    ) {}

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $exploration = $user->activeExploration()->with('zone')->first();

        if (!$exploration) {
            return response()->json([
                'is_exploring' => false,
                'zone' => null,
                'started_at' => null,
                'last_collected_at' => null,
            ]);
        }

        $recentEvents = DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get();

        return response()->json([
            'is_exploring' => true,
            'zone' => [
                'id' => $exploration->zone->id,
                'name' => $exploration->zone->name,
                'slug' => $exploration->zone->slug,
                'level_min' => $exploration->zone->level_min,
                'level_max' => $exploration->zone->level_max,
            ],
            'started_at' => $exploration->started_at,
            'last_collected_at' => $exploration->last_collected_at,
            'unread_events' => $recentEvents,
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'zone_id' => 'required|integer|exists:zones,id',
        ]);

        $zone = Zone::findOrFail($validated['zone_id']);

        // Vérifier que la zone est débloquée (Zone 1 toujours accessible)
        if ($zone->order_index > 1) {
            $progress = UserZoneProgress::where('user_id', $user->id)
                ->where('zone_id', $zone->id)
                ->first();

            if (!$progress) {
                return response()->json([
                    'message' => $zone->name . ' n\'est pas encore débloquée. Le Narrateur suggère de finir ce que vous avez commencé.',
                ], 422);
            }
        }

        // Vérifier qu'il y a au moins un héros
        if ($user->activeHeroes()->count() === 0) {
            return response()->json([
                'message' => 'Aucun héros disponible. Le Narrateur est navré pour vous.',
            ], 422);
        }

        DB::transaction(function () use ($user, $zone) {
            // Collecter la progression existante si elle existe
            if ($user->activeExploration()->exists()) {
                $user->activeExploration()->update(['is_active' => false]);
            }

            // Créer nouvelle exploration
            UserExploration::create([
                'user_id' => $user->id,
                'zone_id' => $zone->id,
                'is_active' => true,
            ]);

            // Mettre à jour la zone courante
            $user->current_zone_id = $zone->id;
            $user->last_idle_calc_at = now();
            $user->save();

            // Créer progression de zone si nécessaire
            UserZoneProgress::firstOrCreate(
                ['user_id' => $user->id, 'zone_id' => $zone->id],
                ['total_combats' => 0, 'total_victories' => 0, 'boss_defeated' => false]
            );
        });

        return response()->json([
            'message' => 'Exploration de ' . $zone->name . ' commencée. Le Narrateur prépare ses condoléances.',
            'zone' => ['id' => $zone->id, 'name' => $zone->name, 'slug' => $zone->slug],
        ], 201);
    }

    public function collect(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();
        $user->load(['activeHeroes.race', 'activeHeroes.gameClass', 'activeHeroes.trait_', 'activeHeroes.equippedItems']);

        $result = $this->idleService->calculateOfflineProgress($user);

        // Marquer les événements comme lus
        DB::table('idle_event_log')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $user->refresh();

        return response()->json([
            'result' => $result,
            'user' => [
                'gold' => $user->gold,
                'level' => $user->level,
                'xp' => $user->xp,
                'xp_to_next_level' => $user->xp_to_next_level,
            ],
            'heroes' => $user->activeHeroes()->with(['race', 'gameClass', 'trait_', 'equippedItems'])->get()->map(function ($hero) {
                return [
                    'id' => $hero->id,
                    'name' => $hero->name,
                    'image_path' => $hero->image_path,
                    'level' => $hero->level,
                    'xp' => $hero->xp,
                    'xp_to_next_level' => $hero->xp_to_next_level,
                    'current_hp' => $hero->current_hp,
                    'max_hp' => $hero->max_hp,
                ];
            }),
        ]);
    }
}
