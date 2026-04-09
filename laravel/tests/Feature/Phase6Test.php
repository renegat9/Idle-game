<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Services\CraftingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase6Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'GameSettingsSeeder']);
        $this->artisan('db:seed', ['--class' => 'MaterialSeeder']);
        $this->artisan('migrate');

        $this->user = User::factory()->create(['gold' => 100000]);
    }

    // ── Enchantment — Service ──────────────────────────────────────────────────

    public function test_get_available_enchantments_returns_base_and_elemental(): void
    {
        $service = app(CraftingService::class);
        $enchants = $service->getAvailableEnchantments($this->user);

        $slugs = array_column($enchants, 'slug');
        $this->assertContains('aiguisage', $slugs);
        $this->assertContains('renforcement', $slugs);
        $this->assertContains('flamme', $slugs);
        // Advanced locked by default
        $this->assertNotContains('vampirisme', $slugs);
    }

    public function test_advanced_enchantments_available_when_unlocked(): void
    {
        // Override setting
        DB::table('game_settings')->where('setting_key', 'ENCHANT_ADVANCED_UNLOCKED')->update(['setting_value' => 1]);
        app(SettingsService::class)->flush();

        $service = app(CraftingService::class);
        $enchants = $service->getAvailableEnchantments($this->user);
        $slugs = array_column($enchants, 'slug');
        $this->assertContains('vampirisme', $slugs);
        $this->assertContains('precision', $slugs);
    }

    public function test_enchant_fails_on_common_item(): void
    {
        $item = Item::factory()->create([
            'user_id' => $this->user->id,
            'rarity'  => 'commun',
            'atq'     => 20,
        ]);

        $service = app(CraftingService::class);
        $result  = $service->enchant($this->user, $item->id, 'aiguisage');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Rare', $result['error']);
    }

    public function test_enchant_fails_when_not_enough_materials(): void
    {
        $item = Item::factory()->create([
            'user_id' => $this->user->id,
            'rarity'  => 'rare',
            'atq'     => 50,
        ]);

        // Don't seed any materials — user has 0
        $service = app(CraftingService::class);
        $result  = $service->enchant($this->user, $item->id, 'aiguisage');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('insuffisants', $result['error']);
    }

    public function test_enchant_applies_stat_bonus_to_rare_item(): void
    {
        $item = Item::factory()->create([
            'user_id'           => $this->user->id,
            'rarity'            => 'rare',
            'atq'               => 100,
            'durability_current'=> 100,
            'durability_max'    => 100,
            'enchant_count'     => 0,
        ]);

        // Give materials: 5 ferraille + 2 essence_mineure
        $this->giveUserMaterials($this->user->id, ['ferraille' => 5, 'essence_mineure' => 2]);

        $service = app(CraftingService::class);
        $result  = $service->enchant($this->user, $item->id, 'aiguisage');

        $this->assertArrayNotHasKey('error', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Aiguisage', $result['enchantment']);

        // ATQ should have increased by 8%
        $item->refresh();
        $this->assertEquals(108, $item->atq); // 100 + 8%
        $this->assertEquals(1, $item->enchant_count);
    }

    public function test_enchant_creates_item_effect_row(): void
    {
        $item = Item::factory()->create([
            'user_id'           => $this->user->id,
            'rarity'            => 'rare',
            'atq'               => 100,
            'durability_current'=> 100,
            'durability_max'    => 100,
            'enchant_count'     => 0,
        ]);

        $this->giveUserMaterials($this->user->id, ['ferraille' => 5, 'essence_mineure' => 2]);

        app(CraftingService::class)->enchant($this->user, $item->id, 'aiguisage');

        $effects = DB::table('item_effects')->where('item_id', $item->id)->get();
        $this->assertCount(1, $effects);
        $this->assertEquals('aiguisage', $effects->first()->effect_key);
        $this->assertEquals(1, $effects->first()->is_enchantment);
    }

    public function test_enchant_replaces_last_effect_when_at_max(): void
    {
        $item = Item::factory()->create([
            'user_id'           => $this->user->id,
            'rarity'            => 'rare',
            'atq'               => 100,
            'def'               => 100,
            'durability_current'=> 100,
            'durability_max'    => 100,
            'enchant_count'     => 2,
        ]);

        // Add 2 existing effects
        DB::table('item_effects')->insert([
            ['item_id' => $item->id, 'effect_key' => 'existing_1', 'description' => 'Effect 1', 'effect_data' => '{}', 'is_enchantment' => 1],
            ['item_id' => $item->id, 'effect_key' => 'existing_2', 'description' => 'Effect 2', 'effect_data' => '{}', 'is_enchantment' => 1],
        ]);

        $this->giveUserMaterials($this->user->id, ['ferraille' => 5, 'cuir' => 2]);

        $result = app(CraftingService::class)->enchant($this->user, $item->id, 'renforcement');

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['replaced_effect']); // Should have replaced something

        // Still only 2 effects max
        $count = DB::table('item_effects')->where('item_id', $item->id)->count();
        $this->assertEquals(2, $count);
    }

    public function test_enchant_deducts_gold(): void
    {
        $item = Item::factory()->create([
            'user_id'           => $this->user->id,
            'rarity'            => 'rare',
            'atq'               => 50,
            'durability_current'=> 100,
            'durability_max'    => 100,
            'enchant_count'     => 0,
        ]);

        $this->giveUserMaterials($this->user->id, ['ferraille' => 5, 'essence_mineure' => 2]);

        $goldBefore = $this->user->gold;
        app(CraftingService::class)->enchant($this->user, $item->id, 'aiguisage');

        $this->user->refresh();
        $this->assertEquals($goldBefore - 2000, $this->user->gold);
    }

    public function test_enchant_unknown_slug_returns_error(): void
    {
        $item = Item::factory()->create(['user_id' => $this->user->id, 'rarity' => 'rare']);

        $result = app(CraftingService::class)->enchant($this->user, $item->id, 'enchantement_inexistant');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('inconnu', $result['error']);
    }

    // ── Enchantment — API ─────────────────────────────────────────────────────

    public function test_get_enchantments_endpoint_returns_200(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/crafting/enchantments');

        $response->assertStatus(200)
                 ->assertJsonStructure(['enchantments' => [['slug', 'name', 'gold', 'tier']]]);
    }

    public function test_enchant_endpoint_returns_422_for_common_item(): void
    {
        $item = Item::factory()->create(['user_id' => $this->user->id, 'rarity' => 'commun']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/crafting/enchant', [
                'item_id'     => $item->id,
                'enchantment' => 'aiguisage',
            ]);

        $response->assertStatus(422);
    }

    public function test_enchant_endpoint_requires_auth(): void
    {
        $response = $this->postJson('/api/crafting/enchant', [
            'item_id'     => 1,
            'enchantment' => 'aiguisage',
        ]);

        $response->assertStatus(401);
    }

    // ── WTF quests ────────────────────────────────────────────────────────────

    public function test_quest_seeder_includes_wtf_quests(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $this->artisan('db:seed', ['--class' => 'QuestSeeder']);

        $count = DB::table('quests')->where('type', 'wtf')->count();
        $this->assertEquals(5, $count);
    }

    public function test_wtf_quests_have_epic_rewards(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $this->artisan('db:seed', ['--class' => 'QuestSeeder']);

        $wtfQuests = DB::table('quests')->where('type', 'wtf')->get();

        foreach ($wtfQuests as $quest) {
            $this->assertContains($quest->reward_loot_rarity, ['epique', 'legendaire'],
                "WTF quest '{$quest->title}' should have epic+ reward");
        }
    }

    public function test_wtf_quests_have_steps(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $this->artisan('db:seed', ['--class' => 'QuestSeeder']);

        $wtfQuestIds = DB::table('quests')->where('type', 'wtf')->pluck('id');

        foreach ($wtfQuestIds as $questId) {
            $stepCount = DB::table('quest_steps')->where('quest_id', $questId)->count();
            $this->assertGreaterThanOrEqual(7, $stepCount,
                "WTF quest ID {$questId} should have at least 7 steps");
        }
    }

    public function test_enchant_solidite_increases_durability(): void
    {
        $item = Item::factory()->create([
            'user_id'           => $this->user->id,
            'rarity'            => 'rare',
            'atq'               => 50,
            'durability_current'=> 80,
            'durability_max'    => 100,
            'enchant_count'     => 0,
        ]);

        $this->giveUserMaterials($this->user->id, ['ferraille' => 8]);

        $result = app(CraftingService::class)->enchant($this->user, $item->id, 'solidite');

        $this->assertTrue($result['success']);
        $item->refresh();
        $this->assertEquals(150, $item->durability_max); // 100 + 50
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function giveUserMaterials(int $userId, array $materials): void
    {
        foreach ($materials as $slug => $qty) {
            $materialId = DB::table('materials')->where('slug', $slug)->value('id');
            if ($materialId) {
                DB::table('user_materials')->updateOrInsert(
                    ['user_id' => $userId, 'material_id' => $materialId],
                    ['quantity' => $qty]
                );
            }
        }
    }
}
