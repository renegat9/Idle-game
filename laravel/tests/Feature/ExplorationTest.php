<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\User;
use App\Models\UserExploration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class ExplorationTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;
    private int $zone1Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->zone1Id = DB::table('zones')->where('order_index', 1)->value('id');
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    private function createHeroForUser(): Hero
    {
        return Hero::factory()->create(['user_id' => $this->user->id]);
    }

    // ─── Status ──────────────────────────────────────────────────────────────

    public function test_status_returns_not_exploring_when_no_exploration(): void
    {
        $response = $this->auth()->getJson('/api/exploration/status');

        $response->assertStatus(200)
                 ->assertJson(['is_exploring' => false, 'zone' => null]);
    }

    public function test_status_returns_zone_info_when_exploring(): void
    {
        $this->createHeroForUser();
        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id]);

        $response = $this->auth()->getJson('/api/exploration/status');

        $response->assertStatus(200)
                 ->assertJson(['is_exploring' => true])
                 ->assertJsonPath('zone.id', $this->zone1Id);
    }

    // ─── Start ───────────────────────────────────────────────────────────────

    public function test_start_exploration_returns_201(): void
    {
        $this->createHeroForUser();

        $response = $this->auth()->postJson('/api/exploration/start', [
            'zone_id' => $this->zone1Id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'zone' => ['id', 'name', 'slug']]);
    }

    public function test_start_exploration_requires_a_hero(): void
    {
        $response = $this->auth()->postJson('/api/exploration/start', [
            'zone_id' => $this->zone1Id,
        ]);

        $response->assertStatus(422);
    }

    public function test_start_exploration_requires_zone_id(): void
    {
        $this->createHeroForUser();

        $this->auth()->postJson('/api/exploration/start', [])
             ->assertStatus(422);
    }

    public function test_start_exploration_rejects_invalid_zone(): void
    {
        $this->createHeroForUser();

        $this->auth()->postJson('/api/exploration/start', ['zone_id' => 9999])
             ->assertStatus(422);
    }

    public function test_start_sets_user_current_zone(): void
    {
        $this->createHeroForUser();

        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id]);

        $this->assertDatabaseHas('users', [
            'id'              => $this->user->id,
            'current_zone_id' => $this->zone1Id,
        ]);
    }

    public function test_start_creates_user_exploration_record(): void
    {
        $this->createHeroForUser();

        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id]);

        $this->assertDatabaseHas('user_exploration', [
            'user_id' => $this->user->id,
            'zone_id' => $this->zone1Id,
            'is_active' => true,
        ]);
    }

    public function test_starting_new_exploration_deactivates_previous(): void
    {
        $this->createHeroForUser();

        // Start in zone 1 first
        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id])
             ->assertStatus(201);

        // Start again in zone 1 (re-start)
        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id])
             ->assertStatus(201);

        // Only one active exploration
        $this->assertSame(
            1,
            \DB::table('user_exploration')->where('user_id', $this->user->id)->where('is_active', true)->count()
        );
    }

    public function test_cannot_start_locked_zone(): void
    {
        $this->createHeroForUser();

        $zone3Id = DB::table('zones')->where('order_index', 3)->value('id');
        if (!$zone3Id) {
            $this->markTestSkipped('Zone 3 not seeded.');
        }

        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $zone3Id])
             ->assertStatus(422);
    }

    // ─── Collect ─────────────────────────────────────────────────────────────

    public function test_collect_returns_result_structure(): void
    {
        $this->createHeroForUser();
        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id]);

        // Move last_idle_calc_at back so there is time elapsed
        DB::table('users')
            ->where('id', $this->user->id)
            ->update(['last_idle_calc_at' => now()->subMinutes(10)]);

        $response = $this->auth()->postJson('/api/exploration/collect');

        $response->assertStatus(200)
                 ->assertJsonStructure(['result', 'user' => ['gold', 'level', 'xp'], 'heroes']);
    }

    public function test_collect_marks_events_as_read(): void
    {
        $this->createHeroForUser();
        $this->auth()->postJson('/api/exploration/start', ['zone_id' => $this->zone1Id]);

        DB::table('idle_event_log')->insert([
            'user_id'     => $this->user->id,
            'event_type'  => 'combat_win',
            'is_read'     => false,
            'occurred_at' => now(),
        ]);

        $this->auth()->postJson('/api/exploration/collect');

        $this->assertSame(
            0,
            DB::table('idle_event_log')
                ->where('user_id', $this->user->id)
                ->where('is_read', false)
                ->count()
        );
    }

    // ─── Auth guard ──────────────────────────────────────────────────────────

    public function test_exploration_routes_require_auth(): void
    {
        $this->getJson('/api/exploration/status')->assertStatus(401);
        $this->postJson('/api/exploration/start')->assertStatus(401);
        $this->postJson('/api/exploration/collect')->assertStatus(401);
    }
}
