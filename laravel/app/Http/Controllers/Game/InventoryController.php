<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->items()
            ->with('effects')
            ->orderByRaw("CASE rarity WHEN 'wtf' THEN 0 WHEN 'legendaire' THEN 1 WHEN 'epique' THEN 2 WHEN 'rare' THEN 3 WHEN 'peu_commun' THEN 4 ELSE 5 END")
            ->orderBy('item_level', 'desc')
            ->get();

        $equipped = $items->whereNotNull('equipped_by_hero_id')->values();
        $unequipped = $items->whereNull('equipped_by_hero_id')->values();

        return response()->json([
            'equipped' => $equipped->map(fn($i) => $this->itemResponse($i)),
            'unequipped' => $unequipped->map(fn($i) => $this->itemResponse($i)),
            'total_count' => $items->count(),
        ]);
    }

    public function sell(Request $request): JsonResponse
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

        if ($item->equipped_by_hero_id !== null) {
            return response()->json([
                'message' => 'Impossible de vendre un objet équipé. Déséquipez-le d\'abord.',
            ], 422);
        }

        $sellValue = $item->sell_value;

        DB::transaction(function () use ($user, $item, $sellValue) {
            $user->gold += $sellValue;
            $user->save();

            DB::table('economy_log')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'gain',
                'source' => 'vente_objet',
                'amount' => $sellValue,
                'balance_after' => $user->gold,
                'description' => 'Vendu : ' . $item->name,
            ]);

            $item->delete();
        });

        $user->refresh();

        return response()->json([
            'message' => $item->name . ' vendu pour ' . $sellValue . ' or. Gérard aurait pleuré.',
            'gold_earned' => $sellValue,
            'new_gold_total' => $user->gold,
        ]);
    }

    private function itemResponse(Item $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'rarity' => $item->rarity,
            'slot' => $item->slot,
            'element' => $item->element,
            'item_level' => $item->item_level,
            'atq' => $item->atq,
            'def' => $item->def,
            'hp' => $item->hp,
            'vit' => $item->vit,
            'cha' => $item->cha,
            'int' => $item->int,
            'sell_value' => $item->sell_value,
            'equipped_by_hero_id' => $item->equipped_by_hero_id,
            'is_ai_generated' => $item->is_ai_generated,
            'effects' => $item->effects->map(fn($e) => [
                'key' => $e->effect_key,
                'description' => $e->description,
            ])->values(),
        ];
    }
}
