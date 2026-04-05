<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ShopInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;
    private int $zone1Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user  = User::factory()->create(['gold' => 10000]);
        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->zone1Id = DB::table('zones')->where('order_index', 1)->value('id');
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    // ─── Index ───────────────────────────────────────────────────────────────

    public function test_shop_index_returns_200_with_zone_id(): void
    {
        $response = $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['zone_id', 'zone_name', 'expires_at', 'items']);
    }

    public function test_shop_generates_items_on_first_visit(): void
    {
        $response = $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");

        $items = $response->json('items');
        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('name', $items[0]);
        $this->assertArrayHasKey('shop_price', $items[0]);
        $this->assertArrayHasKey('rarity', $items[0]);
    }

    public function test_shop_returns_422_without_zone_and_no_current_zone(): void
    {
        $this->user->update(['current_zone_id' => null]);

        $this->auth()->getJson('/api/shop')
             ->assertStatus(422);
    }

    public function test_shop_falls_back_to_current_zone(): void
    {
        $this->user->update(['current_zone_id' => $this->zone1Id]);

        $response = $this->auth()->getJson('/api/shop');
        $response->assertStatus(200)
                 ->assertJsonPath('zone_id', $this->zone1Id);
    }

    public function test_shop_does_not_regenerate_when_stock_is_full(): void
    {
        // First request generates the stock
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $firstCount = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->count();

        // Second request should return same items (no new generation)
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $secondCount = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->count();

        $this->assertSame($firstCount, $secondCount);
    }

    // ─── Buy ─────────────────────────────────────────────────────────────────

    public function test_buy_returns_201_and_creates_item(): void
    {
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $shopItem = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->first();

        $response = $this->auth()->postJson('/api/shop/buy', ['item_id' => $shopItem->id]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'item', 'gold_spent', 'new_gold_total']);

        $this->assertDatabaseHas('items', [
            'user_id' => $this->user->id,
            'name'    => $shopItem->name,
        ]);
    }

    public function test_buy_deducts_gold(): void
    {
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $shopItem   = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->first();
        $goldBefore = $this->user->fresh()->gold;

        $this->auth()->postJson('/api/shop/buy', ['item_id' => $shopItem->id]);

        $this->assertSame($goldBefore - $shopItem->shop_price, $this->user->fresh()->gold);
    }

    public function test_buy_marks_item_as_sold(): void
    {
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $shopItem = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->first();

        $this->auth()->postJson('/api/shop/buy', ['item_id' => $shopItem->id]);

        $this->assertDatabaseHas('shop_inventories', [
            'id'      => $shopItem->id,
            'is_sold' => true,
        ]);
    }

    public function test_buy_returns_422_when_insufficient_gold(): void
    {
        $this->user->update(['gold' => 0]);

        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $shopItem = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->first();

        $this->auth()->postJson('/api/shop/buy', ['item_id' => $shopItem->id])
             ->assertStatus(422);
    }

    public function test_buy_returns_422_for_unknown_item(): void
    {
        $this->auth()->postJson('/api/shop/buy', ['item_id' => 9999])
             ->assertStatus(422);
    }

    public function test_cannot_buy_another_users_shop_item(): void
    {
        $other = User::factory()->create(['gold' => 10000]);

        // Generate shop for other user using actingAs (avoids auth cache pollution)
        $this->actingAs($other, 'sanctum')
             ->getJson("/api/shop?zone_id={$this->zone1Id}");

        $otherItem = ShopInventory::where('user_id', $other->id)->where('is_active', true)->first();
        $this->assertNotNull($otherItem);

        // Re-authenticate as our user and try to buy the other user's item
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/shop/buy', ['item_id' => $otherItem->id])
             ->assertStatus(422);
    }

    public function test_buy_logs_economy_transaction(): void
    {
        $this->auth()->getJson("/api/shop?zone_id={$this->zone1Id}");
        $shopItem = ShopInventory::where('user_id', $this->user->id)->where('is_active', true)->first();

        $this->auth()->postJson('/api/shop/buy', ['item_id' => $shopItem->id]);

        $this->assertDatabaseHas('economy_log', [
            'user_id'          => $this->user->id,
            'transaction_type' => 'depense',
            'source'           => 'achat_boutique',
        ]);
    }

    // ─── Auth guard ──────────────────────────────────────────────────────────

    public function test_shop_routes_require_auth(): void
    {
        $this->getJson('/api/shop')->assertStatus(401);
        $this->postJson('/api/shop/buy')->assertStatus(401);
    }
}
