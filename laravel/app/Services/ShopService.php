<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ShopInventory;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

class ShopService
{
    // Base prices per rarity (before markup and level scaling)
    private const BASE_PRICES = [
        'commun'      => 10,
        'peu_commun'  => 25,
        'rare'        => 60,
        'epique'      => 150,
        'legendaire'  => 500,
        'wtf'         => 500,
    ];

    // Stock composition: [rarity => count]
    private const STOCK_COMMUN     = 2;
    private const STOCK_PEU_COMMUN = 2;
    private const STOCK_RARE       = 1;
    private const STOCK_EPIC_LEVEL = 20; // zone level_min threshold for epic

    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $loot,
    ) {}

    /**
     * Returns active shop items for user in given zone.
     * Generates a new stock if none or if the current stock is expired/incomplete.
     */
    public function getShop(User $user, int $zoneId): array
    {
        $itemsCount = $this->settings->get('SHOP_ITEMS_COUNT', 6);

        $activeItems = ShopInventory::where('zone_id', $zoneId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_sold', false)
            ->where('expires_at', '>', now())
            ->get();

        if ($activeItems->count() < $itemsCount) {
            $this->refreshShop($zoneId, $user->id);

            $activeItems = ShopInventory::where('zone_id', $zoneId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('is_sold', false)
                ->where('expires_at', '>', now())
                ->get();
        }

        $zone = Zone::find($zoneId);

        return [
            'zone_id'    => $zoneId,
            'zone_name'  => $zone?->name,
            'expires_at' => $activeItems->first()?->expires_at,
            'items'      => $activeItems->map(fn(ShopInventory $s) => $this->shopItemResponse($s))->values()->all(),
        ];
    }

    /**
     * Purchases a shop item for the user.
     * Returns array with keys: success (bool), item (array|null), gold_spent (int), error (string|null).
     */
    public function buy(User $user, int $shopItemId): array
    {
        $shopItem = ShopInventory::where('id', $shopItemId)
            ->where('user_id', $user->id)
            ->where('is_sold', false)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$shopItem) {
            return ['success' => false, 'item' => null, 'gold_spent' => 0, 'error' => 'Cet article n\'est plus disponible.'];
        }

        $maxItems = $this->settings->get('INVENTORY_MAX_ITEMS', 100);
        $currentCount = $user->items()->count();

        if ($currentCount >= $maxItems) {
            return ['success' => false, 'item' => null, 'gold_spent' => 0, 'error' => "Inventaire plein ({$maxItems} objets maximum). Vendez quelque chose d'abord."];
        }

        if ($user->gold < $shopItem->shop_price) {
            return ['success' => false, 'item' => null, 'gold_spent' => 0, 'error' => "Or insuffisant. Prix : {$shopItem->shop_price} or, vous avez : {$user->gold} or."];
        }

        $item = DB::transaction(function () use ($user, $shopItem) {
            $user->decrement('gold', $shopItem->shop_price);
            $shopItem->update(['is_sold' => true]);

            $newItem = Item::create([
                'user_id'         => $user->id,
                'name'            => $shopItem->name,
                'description'     => 'Acheté chez le marchand local. Il avait l\'air suspect.',
                'rarity'          => $shopItem->rarity,
                'slot'            => $shopItem->slot,
                'element'         => 'physique',
                'item_level'      => $shopItem->item_level,
                'atq'             => $shopItem->atq,
                'def'             => $shopItem->def,
                'hp'              => $shopItem->hp,
                'vit'             => $shopItem->vit,
                'cha'             => $shopItem->cha,
                'int'             => $shopItem->int,
                'sell_value'      => $shopItem->sell_value,
                'is_ai_generated' => false,
            ]);

            // Apply the pre-rolled special effect stored at stock generation
            if ($shopItem->effect_key && $shopItem->effect_description) {
                DB::table('item_effects')->insert([
                    'item_id'        => $newItem->id,
                    'effect_key'     => $shopItem->effect_key,
                    'description'    => $shopItem->effect_description,
                    'effect_data'    => $shopItem->effect_data
                        ? (is_string($shopItem->effect_data) ? $shopItem->effect_data : json_encode($shopItem->effect_data))
                        : '{}',
                    'is_enchantment' => 0,
                ]);
            }

            DB::table('economy_log')->insert([
                'user_id'          => $user->id,
                'transaction_type' => 'depense',
                'source'           => 'achat_boutique',
                'amount'           => $shopItem->shop_price,
                'balance_after'    => $user->gold - $shopItem->shop_price,
                'description'      => 'Acheté : ' . $shopItem->name,
            ]);

            return $newItem;
        });

        $user->refresh();

        return [
            'success'    => true,
            'item'       => $this->itemResponse($item),
            'gold_spent' => $shopItem->shop_price,
            'error'      => null,
        ];
    }

    /**
     * Expires old items and generates a fresh stock for the given zone/user.
     * Called by cron (ShopRefreshCommand) or on-demand from getShop().
     */
    public function refreshShop(int $zoneId, int $userId): void
    {
        // Expire all current active items for this user+zone
        ShopInventory::where('zone_id', $zoneId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $zone = Zone::find($zoneId);
        if (!$zone) {
            return;
        }

        $refreshHours = $this->settings->get('SHOP_REFRESH_HOURS', 6);
        $expiresAt    = now()->addHours($refreshHours);
        $markup       = $this->settings->get('SHOP_PRICE_MARKUP', 300);

        // Determine zone level for epic eligibility
        $zoneLevel     = intdiv($zone->level_min + $zone->level_max, 2);
        $lastSlotRarity = ($zoneLevel >= self::STOCK_EPIC_LEVEL) ? 'epique' : 'rare';

        $stock = [];
        for ($i = 0; $i < self::STOCK_COMMUN; $i++) {
            $stock[] = 'commun';
        }
        for ($i = 0; $i < self::STOCK_PEU_COMMUN; $i++) {
            $stock[] = 'peu_commun';
        }
        $stock[] = 'rare';
        $stock[] = $lastSlotRarity;

        // Create a throwaway User object to satisfy LootService::generateItemForCrafting
        // which persists an Item to DB; we read stats then delete it immediately.
        $tempUser = User::find($userId);
        if (!$tempUser) {
            return;
        }

        foreach ($stock as $rarity) {
            $slot      = $this->loot->rollSlot();
            $itemLevel = max(1, $zoneLevel);

            // Generate a temporary Item to derive stats and roll effect
            $tempItem  = $this->loot->generateItemForCrafting($tempUser, $rarity, $slot, $itemLevel);
            $shopPrice = $this->calculateShopPrice($rarity, $itemLevel, $markup);

            // Read any effect generated on the temp item, then delete it
            $tempEffect = DB::table('item_effects')->where('item_id', $tempItem->id)->first();

            ShopInventory::create([
                'zone_id'            => $zoneId,
                'user_id'            => $userId,
                'name'               => $tempItem->name,
                'rarity'             => $rarity,
                'slot'               => $slot,
                'item_level'         => $itemLevel,
                'atq'                => $tempItem->atq,
                'def'                => $tempItem->def,
                'hp'                 => $tempItem->hp,
                'vit'                => $tempItem->vit,
                'cha'                => $tempItem->cha,
                'int'                => $tempItem->int,
                'sell_value'         => $tempItem->sell_value,
                'effect_key'         => $tempEffect?->effect_key,
                'effect_description' => $tempEffect?->description,
                'effect_data'        => $tempEffect ? $tempEffect->effect_data : null,
                'shop_price'         => $shopPrice,
                'is_sold'            => false,
                'is_active'          => true,
                'expires_at'         => $expiresAt,
            ]);

            // Remove the temporary item (and its cascaded item_effects row)
            $tempItem->delete();
        }
    }

    // ─── Price formula ────────────────────────────────────────────────────────

    /**
     * shop_price = base_rarity_price × item_level × markup / 100
     * All integer arithmetic — no floats.
     */
    private function calculateShopPrice(string $rarity, int $itemLevel, int $markup): int
    {
        $base = self::BASE_PRICES[$rarity] ?? 10;
        return max(1, intdiv($base * $itemLevel * $markup, 100));
    }

    // ─── Response helpers ─────────────────────────────────────────────────────

    private function shopItemResponse(ShopInventory $s): array
    {
        // Look up shared template image for this slot+rarity combination
        $templateImage = \Illuminate\Support\Facades\DB::table('item_templates')
            ->where('slot', $s->slot)
            ->where('rarity', $s->rarity)
            ->whereNotNull('image_path')
            ->where('image_path', 'not like', 'images/placeholders/%')
            ->value('image_path');

        return [
            'id'                 => $s->id,
            'name'               => $s->name,
            'rarity'             => $s->rarity,
            'slot'               => $s->slot,
            'item_level'         => $s->item_level,
            'atq'                => $s->atq,
            'def'                => $s->def,
            'hp'                 => $s->hp,
            'vit'                => $s->vit,
            'cha'                => $s->cha,
            'int'                => $s->int,
            'sell_value'         => $s->sell_value,
            'shop_price'         => $s->shop_price,
            'expires_at'         => $s->expires_at,
            'image_url'          => $templateImage,
            'effect_description' => $s->effect_description,
        ];
    }

    private function itemResponse(Item $item): array
    {
        return [
            'id'         => $item->id,
            'name'       => $item->name,
            'rarity'     => $item->rarity,
            'slot'       => $item->slot,
            'item_level' => $item->item_level,
            'atq'        => $item->atq,
            'def'        => $item->def,
            'hp'         => $item->hp,
            'vit'        => $item->vit,
            'cha'        => $item->cha,
            'int'        => $item->int,
            'sell_value' => $item->sell_value,
        ];
    }
}
