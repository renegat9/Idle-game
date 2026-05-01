<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\LootService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    public function __construct(private readonly LootService $loot) {}

    public function repair(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
        ]);

        $item = Item::where('id', $validated['item_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Objet introuvable.'], 404);
        }

        if ($item->durability_current >= $item->durability_max) {
            return response()->json(['message' => 'Cet objet est déjà en parfait état. Gérard vous regarde d\'un air suspicieux.'], 422);
        }

        if ($item->durability_max >= 999) {
            return response()->json(['message' => 'Les objets Indestructibles n\'ont pas besoin de réparation. C\'est le principe.'], 422);
        }

        $cost = $this->loot->repairCost($item);

        if ($cost === 0) {
            return response()->json(['message' => 'Aucune réparation nécessaire.'], 422);
        }

        if ($user->gold < $cost) {
            return response()->json([
                'message' => "Or insuffisant. Gérard exige {$cost} or. Vous en avez {$user->gold}.",
                'cost'    => $cost,
                'have'    => $user->gold,
            ], 422);
        }

        DB::transaction(function () use ($user, $item, $cost) {
            $user->gold -= $cost;
            $user->save();

            $item->durability_current = $item->durability_max;
            $item->save();

            DB::table('economy_log')->insert([
                'user_id'          => $user->id,
                'transaction_type' => 'depense',
                'source'           => 'reparation',
                'amount'           => $cost,
                'balance_after'    => $user->gold,
                'description'      => 'Réparation : ' . $item->name,
                'occurred_at'      => now(),
            ]);
        });

        return response()->json([
            'message'             => '"' . $item->name . '" a été réparé(e). Gérard grogne de satisfaction.',
            'item_id'             => $item->id,
            'durability_current'  => $item->durability_current,
            'durability_max'      => $item->durability_max,
            'gold_spent'          => $cost,
            'new_gold'            => $user->gold,
        ]);
    }

    public function repairAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = Item::where('user_id', $user->id)
            ->whereColumn('durability_current', '<', 'durability_max')
            ->where('durability_max', '<', 999)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Tous vos objets sont en parfait état. Gérard est au chômage.'], 422);
        }

        $totalCost = $items->sum(fn($i) => $this->loot->repairCost($i));

        if ($user->gold < $totalCost) {
            return response()->json([
                'message'    => "Or insuffisant pour tout réparer. Coût total : {$totalCost} or. Vous en avez {$user->gold}.",
                'cost'       => $totalCost,
                'have'       => $user->gold,
                'item_count' => $items->count(),
            ], 422);
        }

        DB::transaction(function () use ($user, $items, $totalCost) {
            $user->gold -= $totalCost;
            $user->save();

            foreach ($items as $item) {
                $item->durability_current = $item->durability_max;
                $item->save();
            }

            DB::table('economy_log')->insert([
                'user_id'          => $user->id,
                'transaction_type' => 'depense',
                'source'           => 'reparation_all',
                'amount'           => $totalCost,
                'balance_after'    => $user->gold,
                'description'      => 'Réparation de ' . $items->count() . ' objet(s)',
                'occurred_at'      => now(),
            ]);
        });

        return response()->json([
            'message'    => "Tout l'équipement réparé ({$items->count()} objets). Gérard sourit (un peu).",
            'item_count' => $items->count(),
            'gold_spent' => $totalCost,
            'new_gold'   => $user->gold,
        ]);
    }
}
