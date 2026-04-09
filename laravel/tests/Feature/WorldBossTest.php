<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\User;
use App\Models\WorldBoss;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class WorldBossTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user = User::factory()->create(['gold' => 5000]);

        // Give user an active hero
        $raceId  = \Illuminate\Support\Facades\DB::table('races')->value('id');
        $classId = \Illuminate\Support\Facades\DB::table('classes')->value('id');
        $traitId = \Illuminate\Support\Facades\DB::table('traits')->value('id');

        Hero::create([
            'user_id'         => $this->user->id,
            'race_id'         => $raceId,
            'class_id'        => $classId,
            'trait_id'        => $traitId,
            'name'            => 'Héros de Test',
            'level'           => 10,
            'xp'              => 0,
            'xp_to_next_level' => 200,
            'current_hp'      => 200,
            'max_hp'          => 200,
            'slot_index'      => 0,
            'is_active'       => true,
        ]);
    }

    private function spawnBoss(): WorldBoss
    {
        return WorldBoss::create([
            'name'             => 'Le Monstre de Test',
            'slug'             => 'le-monstre-de-test',
            'total_hp'         => 1000,
            'current_hp'       => 1000,
            'status'           => 'active',
            'special_mechanic' => null,
            'spawned_at'       => now(),
        ]);
    }

    // ─── Status ──────────────────────────────────────────────────────────────

    public function test_status_returns_200_with_no_boss(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/world-boss')
             ->assertStatus(200)
             ->assertJsonPath('active_boss', null);
    }

    public function test_status_returns_active_boss_info(): void
    {
        $this->spawnBoss();

        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/world-boss');

        $response->assertStatus(200)
                 ->assertJsonStructure(['active_boss', 'my_contribution', 'cooldown_seconds', 'can_attack']);

        $this->assertNotNull($response->json('active_boss'));
        $this->assertSame(1000, $response->json('active_boss.total_hp'));
    }

    // ─── Attack ──────────────────────────────────────────────────────────────

    public function test_attack_returns_404_when_no_boss(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack')
             ->assertStatus(404);
    }

    public function test_attack_deals_damage(): void
    {
        $boss = $this->spawnBoss();

        $response = $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack');

        $response->assertStatus(200)
                 ->assertJsonStructure(['damage_dealt', 'boss_current_hp', 'boss_defeated', 'narration']);

        $this->assertGreaterThan(0, $response->json('damage_dealt'));
        $this->assertLessThan($boss->total_hp, $response->json('boss_current_hp'));
    }

    public function test_attack_updates_contribution(): void
    {
        $this->spawnBoss();

        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack');

        $this->assertDatabaseHas('boss_contributions', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_attack_returns_422_on_cooldown(): void
    {
        $this->spawnBoss();

        // First attack
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack');

        // Second attack immediately (should be on cooldown)
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack')
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'seconds_remaining']);
    }

    // ─── Leaderboard ─────────────────────────────────────────────────────────

    public function test_leaderboard_returns_200(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/world-boss/leaderboard')
             ->assertStatus(200)
             ->assertJsonStructure(['leaderboard']);
    }

    public function test_leaderboard_shows_contributors(): void
    {
        $this->spawnBoss();

        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/world-boss/attack');

        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/world-boss/leaderboard');

        $leaderboard = $response->json('leaderboard');
        $this->assertNotEmpty($leaderboard);
        $this->assertArrayHasKey('username', $leaderboard[0]);
        $this->assertArrayHasKey('damage_dealt', $leaderboard[0]);
    }

    // ─── Auth ────────────────────────────────────────────────────────────────

    public function test_world_boss_routes_require_auth(): void
    {
        $this->getJson('/api/world-boss')->assertStatus(401);
        $this->postJson('/api/world-boss/attack')->assertStatus(401);
        $this->getJson('/api/world-boss/leaderboard')->assertStatus(401);
    }
}
