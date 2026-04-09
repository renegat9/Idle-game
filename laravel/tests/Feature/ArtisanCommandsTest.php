<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests for Phase 4 Artisan commands.
 * These verify the commands execute without exception.
 */
class ArtisanCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\GameSettingsSeeder::class);
    }

    public function test_logs_cleanup_runs_successfully(): void
    {
        $this->artisan('logs:cleanup')->assertSuccessful();
    }

    public function test_shop_refresh_runs_successfully(): void
    {
        $this->artisan('shop:refresh')->assertSuccessful();
    }

    public function test_quests_generate_runs_successfully(): void
    {
        // No zones seeded — command should gracefully handle empty zone list
        $this->artisan('quests:generate')->assertSuccessful();
    }

    public function test_world_boss_spawn_runs_successfully(): void
    {
        $this->artisan('world-boss:spawn')->assertSuccessful();
    }

    public function test_logs_cleanup_deletes_old_combat_logs(): void
    {
        // Create a user to satisfy FK
        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'username' => 'test', 'email' => 'test@test.com',
            'password' => bcrypt('pw'), 'gold' => 0, 'level' => 1,
        ]);

        // Insert an old combat_log entry using correct schema
        \Illuminate\Support\Facades\DB::table('combat_log')->insert([
            'user_id'     => $userId,
            'zone_id'     => null,
            'combat_type' => 'idle',
            'result'      => 'victory',
            'turns'       => 3,
            'xp_gained'   => 10,
            'gold_gained' => 5,
            'occurred_at'  => now()->subDays(45),
        ]);

        $this->artisan('logs:cleanup');

        $this->assertDatabaseCount('combat_log', 0);
    }

    public function test_shop_refresh_deletes_expired_items(): void
    {
        $zoneId = \Illuminate\Support\Facades\DB::table('zones')->insertGetId([
            'name'              => 'Zone Test',
            'slug'              => 'zone-test',
            'description'       => 'test',
            'order_index'       => 999,
            'level_min' => 1,
            'level_max' => 5,
            'dominant_element' => 'physique',
        ]);

        // Insert an expired shop item using correct schema
        \Illuminate\Support\Facades\DB::table('shop_inventories')->insert([
            'zone_id'    => $zoneId,
            'user_id'    => null,
            'name'       => 'Item Expiré',
            'rarity'     => 'commun',
            'slot'       => 'arme',
            'item_level' => 1,
            'shop_price' => 100,
            'sell_value' => 30,
            'is_sold'    => false,
            'is_active'  => true,
            'expires_at' => now()->subHours(12),
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);

        $this->artisan('shop:refresh');

        $this->assertDatabaseCount('shop_inventories', 0);
    }
}
