<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CombatService;
use App\Services\ReputationService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase5Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'GameSettingsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ElementChartSeeder']);

        $this->user = User::factory()->create(['gold' => 1000]);
    }

    // ── Elemental combat ──────────────────────────────────────────────────────

    public function test_elemental_multiplier_returns_100_for_same_element(): void
    {
        $combatService = app(CombatService::class);
        $mult = $combatService->applyElementalMultiplier('physique', 'physique');
        $this->assertEquals(100, $mult);
    }

    public function test_elemental_multiplier_loaded_from_db(): void
    {
        // Seed one explicit row to verify loading
        DB::table('element_chart')->updateOrInsert(
            ['attacker_element' => 'feu', 'defender_element' => 'glace'],
            ['damage_multiplier' => 150]
        );

        $combatService = app(CombatService::class);
        $mult = $combatService->applyElementalMultiplier('feu', 'glace');
        $this->assertEquals(150, $mult);
    }

    public function test_elemental_multiplier_defaults_to_100_for_unknown_pair(): void
    {
        $combatService = app(CombatService::class);
        $mult = $combatService->applyElementalMultiplier('feu', 'unknown_element');
        $this->assertEquals(100, $mult);
    }

    // ── Elite prefix seeder ───────────────────────────────────────────────────

    public function test_elite_prefix_seeder_inserts_10_prefixes(): void
    {
        $this->artisan('db:seed', ['--class' => 'ElitePrefixSeeder']);
        $count = DB::table('elite_prefixes')->count();
        $this->assertEquals(10, $count);
    }

    public function test_elite_prefix_has_required_fields(): void
    {
        $this->artisan('db:seed', ['--class' => 'ElitePrefixSeeder']);
        $prefix = DB::table('elite_prefixes')->where('slug', 'enrage')->first();

        $this->assertNotNull($prefix);
        $this->assertEquals('Enragé', $prefix->name);
        $this->assertEquals(180, $prefix->atq_multiplier);
        $this->assertEquals(90, $prefix->def_multiplier);
        $this->assertGreaterThan(100, $prefix->xp_multiplier);
    }

    // ── Reputation service ────────────────────────────────────────────────────

    public function test_reputation_starts_at_zero(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $reputations = app(ReputationService::class)->getReputation($this->user->id, $zoneId);
        $this->assertEmpty($reputations); // No row yet = 0
    }

    public function test_add_reputation_creates_row(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $result = app(ReputationService::class)->addReputation($this->user->id, $zoneId, 30);

        $this->assertEquals(30, $result['reputation']);
        $this->assertEquals('neutre', $result['tier']);
        $this->assertFalse($result['capped']);
    }

    public function test_add_reputation_tier_transition(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $service = app(ReputationService::class);
        $service->addReputation($this->user->id, $zoneId, 40);  // → neutre

        $result = $service->addReputation($this->user->id, $zoneId, 20); // → ami
        $this->assertEquals(60, $result['reputation']);
        $this->assertEquals('ami', $result['tier']);
        $this->assertTrue($result['tier_up']);
    }

    public function test_reputation_capped_at_max(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $service = app(ReputationService::class);
        $result  = $service->addReputation($this->user->id, $zoneId, 999);

        $max = app(SettingsService::class)->get('REPUTATION_MAX', 200);
        $this->assertEquals($max, $result['reputation']);
        $this->assertTrue($result['capped']);
    }

    public function test_reputation_tiers_correct(): void
    {
        $service = app(ReputationService::class);

        $this->assertEquals('etranger', $service->getReputationTier(0));
        $this->assertEquals('etranger', $service->getReputationTier(24));
        $this->assertEquals('neutre',   $service->getReputationTier(25));
        $this->assertEquals('ami',      $service->getReputationTier(50));
        $this->assertEquals('honore',   $service->getReputationTier(100));
        $this->assertEquals('revere',   $service->getReputationTier(150));
        $this->assertEquals('exalte',   $service->getReputationTier(200));
    }

    public function test_reputation_loot_bonus_by_tier(): void
    {
        $service = app(ReputationService::class);
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        // No reputation = 0 bonus
        $this->assertEquals(0, $service->getLootBonus($this->user->id, $zoneId));

        // Ami (50+) = +5%
        $service->addReputation($this->user->id, $zoneId, 60);
        $this->assertEquals(5, $service->getLootBonus($this->user->id, $zoneId));
    }

    // ── Reputation API endpoints ──────────────────────────────────────────────

    public function test_reputation_index_returns_array(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/reputation');

        $response->assertStatus(200)
                 ->assertJsonStructure(['reputations']);
    }

    public function test_reputation_show_returns_404_for_invalid_zone(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/reputation/99999');

        $response->assertStatus(404);
    }

    public function test_reputation_show_returns_zero_for_unvisited_zone(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $zoneId = DB::table('zones')->where('slug', 'prairie')->value('id');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/reputation/{$zoneId}");

        $response->assertStatus(200)
                 ->assertJson(['reputation' => 0, 'tier' => 'etranger']);
    }

    // ── Zone seeders ──────────────────────────────────────────────────────────

    public function test_zone_seeder_creates_8_zones(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $count = DB::table('zones')->count();
        $this->assertEquals(8, $count);
    }

    public function test_all_8_zones_have_correct_order(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $orders = DB::table('zones')->orderBy('order_index')->pluck('order_index')->toArray();
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $orders);
    }

    public function test_monster_seeder_includes_zones_5_to_8(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $this->artisan('db:seed', ['--class' => 'MonsterSeeder']);

        foreach (['tour_mage_distrait', 'cimetiere_syndique', 'volcan_dragon_retraite', 'capitale_incompetents'] as $slug) {
            $zoneId = DB::table('zones')->where('slug', $slug)->value('id');
            $count  = DB::table('monsters')->where('zone_id', $zoneId)->count();
            $this->assertGreaterThanOrEqual(6, $count, "Zone {$slug} should have at least 6 monsters");
        }
    }

    public function test_item_template_seeder_includes_zones_5_to_8(): void
    {
        $this->artisan('db:seed', ['--class' => 'ZoneSeeder']);
        $this->artisan('db:seed', ['--class' => 'MonsterSeeder']);
        $this->artisan('db:seed', ['--class' => 'ItemTemplateSeeder']);

        foreach (['tour_mage_distrait', 'cimetiere_syndique', 'volcan_dragon_retraite', 'capitale_incompetents'] as $slug) {
            $zoneId = DB::table('zones')->where('slug', $slug)->value('id');
            $count  = DB::table('item_templates')->where('zone_id', $zoneId)->count();
            $this->assertGreaterThanOrEqual(8, $count, "Zone {$slug} should have at least 8 item templates");
        }
    }
}
