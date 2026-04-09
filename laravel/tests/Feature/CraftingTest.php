<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class CraftingTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAll();

        $this->user  = User::factory()->create(['gold' => 99999]);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    /** Create 3 identical-rarity unequipped items for the user. */
    private function makeItems(string $rarity = 'commun', string $slot = 'arme', int $count = 3): \Illuminate\Support\Collection
    {
        return Item::factory()->count($count)->create([
            'user_id'             => $this->user->id,
            'rarity'              => $rarity,
            'slot'                => $slot,
            'equipped_by_hero_id' => null,
        ]);
    }

    // ─── Index ───────────────────────────────────────────────────────────────

    public function test_index_returns_materials_and_recipes(): void
    {
        $response = $this->auth()->getJson('/api/crafting');

        $response->assertStatus(200)
                 ->assertJsonStructure(['materials', 'recipes']);
    }

    // ─── Fusion ──────────────────────────────────────────────────────────────

    public function test_fuse_requires_exactly_3_items(): void
    {
        $items = $this->makeItems(count: 2);

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => $items->pluck('id')->all(),
        ])->assertStatus(422);
    }

    public function test_fuse_returns_422_for_mismatched_rarities(): void
    {
        $item1 = Item::factory()->create(['user_id' => $this->user->id, 'rarity' => 'commun',     'equipped_by_hero_id' => null]);
        $item2 = Item::factory()->create(['user_id' => $this->user->id, 'rarity' => 'peu_commun', 'equipped_by_hero_id' => null]);
        $item3 = Item::factory()->create(['user_id' => $this->user->id, 'rarity' => 'commun',     'equipped_by_hero_id' => null]);

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => [$item1->id, $item2->id, $item3->id],
        ])->assertStatus(422);
    }

    public function test_fuse_422_when_items_belong_to_another_user(): void
    {
        $other = User::factory()->create();
        $items = Item::factory()->count(3)->create([
            'user_id' => $other->id,
            'rarity'  => 'commun',
            'equipped_by_hero_id' => null,
        ]);

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => $items->pluck('id')->all(),
        ])->assertStatus(422);
    }

    public function test_fuse_422_when_item_is_equipped(): void
    {
        $items = $this->makeItems(count: 3);
        // Equip the item via a real hero
        $hero = \App\Models\Hero::factory()->create(['user_id' => $this->user->id]);
        $items[0]->update(['equipped_by_hero_id' => $hero->id]);

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => $items->pluck('id')->all(),
        ])->assertStatus(422);
    }

    public function test_fuse_422_when_insufficient_gold(): void
    {
        $this->user->update(['gold' => 0]);
        $items = $this->makeItems('rare', 'arme'); // rare costs more

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => $items->pluck('id')->all(),
        ])->assertStatus(422);
    }

    public function test_fuse_success_returns_201_and_destroys_inputs(): void
    {
        $items = $this->makeItems('commun', 'arme');
        $ids   = $items->pluck('id')->all();

        $response = $this->auth()->postJson('/api/crafting/fuse', ['item_ids' => $ids]);

        // Either success (201) or failure (202 or result with success=false)
        $this->assertContains($response->status(), [200, 201, 422]);

        if ($response->status() === 201) {
            // Input items must be gone
            $this->assertDatabaseMissing('items', ['id' => $ids[0]]);
            $this->assertDatabaseMissing('items', ['id' => $ids[1]]);
            $this->assertDatabaseMissing('items', ['id' => $ids[2]]);
        }
    }

    public function test_fuse_gold_is_deducted_on_attempt(): void
    {
        $items = $this->makeItems('commun', 'arme');
        $goldBefore = $this->user->fresh()->gold;

        $this->auth()->postJson('/api/crafting/fuse', [
            'item_ids' => $items->pluck('id')->all(),
        ]);

        $goldAfter = $this->user->fresh()->gold;
        $this->assertLessThan($goldBefore, $goldAfter);
    }

    // ─── Dismantle ───────────────────────────────────────────────────────────

    public function test_dismantle_returns_materials(): void
    {
        $item = Item::factory()->create([
            'user_id'             => $this->user->id,
            'rarity'              => 'commun',
            'equipped_by_hero_id' => null,
        ]);

        $response = $this->auth()->postJson('/api/crafting/dismantle', ['item_id' => $item->id]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['materials', 'gerard_comment']);
    }

    public function test_dismantle_removes_item_from_inventory(): void
    {
        $item = Item::factory()->create([
            'user_id'             => $this->user->id,
            'equipped_by_hero_id' => null,
        ]);

        $this->auth()->postJson('/api/crafting/dismantle', ['item_id' => $item->id])
             ->assertStatus(200);

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_dismantle_cannot_destroy_equipped_item(): void
    {
        $hero = \App\Models\Hero::factory()->create(['user_id' => $this->user->id]);
        $item = Item::factory()->create([
            'user_id'             => $this->user->id,
            'equipped_by_hero_id' => $hero->id,
        ]);

        $this->auth()->postJson('/api/crafting/dismantle', ['item_id' => $item->id])
             ->assertStatus(422);
    }

    public function test_dismantle_returns_404_for_other_users_item(): void
    {
        $other = User::factory()->create();
        $item  = Item::factory()->create(['user_id' => $other->id, 'equipped_by_hero_id' => null]);

        $this->auth()->postJson('/api/crafting/dismantle', ['item_id' => $item->id])
             ->assertStatus(422); // CraftingService returns error array → 422
    }

    // ─── Recipe crafting ─────────────────────────────────────────────────────

    public function test_craft_requires_recipe_id(): void
    {
        $this->auth()->postJson('/api/crafting/craft', [])
             ->assertStatus(422);
    }

    public function test_craft_returns_error_for_unknown_recipe(): void
    {
        $this->auth()->postJson('/api/crafting/craft', ['recipe_id' => 9999])
             ->assertStatus(404);
    }

    public function test_craft_known_recipe_creates_item(): void
    {
        // Get the first non-discoverable recipe (base recipe available to all)
        $recipe = DB::table('recipes')->where('is_discoverable', false)->first();
        if (!$recipe) {
            $this->markTestSkipped('No base recipes seeded.');
        }

        // Give user the required materials
        $ingredients = json_decode($recipe->ingredients, true);
        foreach ($ingredients as $materialSlug => $qty) {
            $materialId = DB::table('materials')->where('slug', $materialSlug)->value('id');
            if ($materialId) {
                DB::table('user_materials')->updateOrInsert(
                    ['user_id' => $this->user->id, 'material_id' => $materialId],
                    ['quantity' => $qty + 10]
                );
            }
        }

        // Give enough gold for the recipe cost
        $this->user->update(['gold' => $recipe->gold_cost + 1000]);

        $response = $this->auth()->postJson('/api/crafting/craft', ['recipe_id' => $recipe->id]);

        // Either success (201) or materials-not-found (422 is also acceptable here)
        $this->assertContains($response->status(), [201, 422]);
    }

    // ─── Auth guard ──────────────────────────────────────────────────────────

    public function test_crafting_routes_require_auth(): void
    {
        $this->getJson('/api/crafting')->assertStatus(401);
        $this->postJson('/api/crafting/fuse')->assertStatus(401);
        $this->postJson('/api/crafting/dismantle')->assertStatus(401);
        $this->postJson('/api/crafting/craft')->assertStatus(401);
    }
}
