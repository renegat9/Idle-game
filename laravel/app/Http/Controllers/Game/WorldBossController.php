<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\WorldBossService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorldBossController extends Controller
{
    public function __construct(private WorldBossService $worldBossService) {}

    /**
     * GET /world-boss
     * Retourne les informations du boss actif, la contribution de l'utilisateur et le cooldown.
     */
    public function status(Request $request): JsonResponse
    {
        $boss = $this->worldBossService->getActiveBoss();

        if ($boss === null) {
            return response()->json([
                'active_boss'  => null,
                'message'      => 'Aucun boss mondial actif pour le moment. Le Narrateur attend.',
            ], 200);
        }

        $user         = $request->user();
        $contribution = $boss->contributions()->where('user_id', $user->id)->first();
        $cooldown     = $this->worldBossService->getCooldownRemaining($user, $boss);

        return response()->json([
            'active_boss' => [
                'id'               => $boss->id,
                'name'             => $boss->name,
                'slug'             => $boss->slug,
                'total_hp'         => $boss->total_hp,
                'current_hp'       => $boss->current_hp,
                'status'           => $boss->status,
                'special_mechanic' => $boss->special_mechanic,
                'spawned_at'       => $boss->spawned_at?->toIso8601String(),
                'hp_percent'       => intdiv($boss->current_hp * 100, max(1, $boss->total_hp)),
            ],
            'my_contribution' => [
                'damage_dealt'   => $contribution?->damage_dealt ?? 0,
                'hits_count'     => $contribution?->hits_count ?? 0,
                'reward_claimed' => $contribution?->reward_claimed ?? false,
            ],
            'cooldown_seconds' => $cooldown,
            'can_attack'       => $cooldown === 0,
        ]);
    }

    /**
     * POST /world-boss/attack
     * Lance une attaque de l'équipe de l'utilisateur sur le boss actif.
     */
    public function attack(Request $request): JsonResponse
    {
        $user = $request->user();
        $boss = $this->worldBossService->getActiveBoss();

        if ($boss === null) {
            return response()->json([
                'message' => 'Aucun boss mondial actif. Revenez plus tard.',
            ], 404);
        }

        $cooldown = $this->worldBossService->getCooldownRemaining($user, $boss);
        if ($cooldown > 0) {
            return response()->json([
                'message'           => 'Vos héros sont encore épuisés. Patience, ce n\'est pas leur fort.',
                'seconds_remaining' => $cooldown,
            ], 422);
        }

        $result = $this->worldBossService->attack($user);

        // En cas de race condition (boss vaincu entre la vérification et l'attaque)
        if (isset($result['error']) && $result['error'] === 'no_active_boss') {
            return response()->json([
                'message' => 'Le boss a déjà été vaincu. Trop lent.',
            ], 404);
        }

        if (isset($result['error']) && $result['error'] === 'cooldown') {
            return response()->json([
                'message'           => 'Vos héros sont encore épuisés. Patience, ce n\'est pas leur fort.',
                'seconds_remaining' => $result['seconds_remaining'],
            ], 422);
        }

        $status = $result['boss_defeated'] ? 200 : 200;

        return response()->json([
            'damage_dealt'    => $result['damage_dealt'],
            'boss_current_hp' => $result['boss_current_hp'],
            'boss_defeated'   => $result['boss_defeated'],
            'narration'       => $result['narration'],
        ], $status);
    }

    /**
     * GET /world-boss/leaderboard
     * Retourne le top 10 des contributeurs du boss actif ou du dernier boss.
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $bossId      = $request->query('boss_id') ? (int) $request->query('boss_id') : null;
        $leaderboard = $this->worldBossService->getLeaderboard($bossId);

        return response()->json([
            'leaderboard' => $leaderboard,
        ]);
    }
}
