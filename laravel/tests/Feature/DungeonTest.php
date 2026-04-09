<?php

namespace Tests\Feature;

use App\Models\Dungeon;
use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class DungeonTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private int $zone1Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user    = User::factory()->create(['gold' => 5000]);
        $this->zone1Id = \Illuminate\Support\Facades\DB::table('zones')->where('order_index', 1)->value('id');

        // Give the user a hero so dungeon can start
        $raceId  = \Illuminate\Support\Facades\DB::table('races')->value('id');
        $classId = \Illuminate\Support\Facades\DB::table('classes')->value('id');
        $traitId = \Illuminate\Support\Facades\DB::table('traits')->value('id');

        Hero::create([
            'user_id'         => $this->user->id,
            'race_id'         => $raceId,
            'class_id'        => $classId,
            'trait_id'        => $traitId,
            'name'            => 'Héros Test',
            'level'           => 5,
            'xp'              => 0,
            'xp_to_next_level' => 100,
            'current_hp'      => 100,
            'max_hp'          => 100,
            'slot_index'      => 0,
            'is_active'       => true,
        ]);
    }

    // ─── Status ──────────────────────────────────────────────────────────────

    public function test_status_returns_200_when_no_active_dungeon(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/dungeon')
             ->assertStatus(200)
             ->assertJsonPath('active', false);
    }

    // ─── Start ───────────────────────────────────────────────────────────────

    public function test_start_creates_new_dungeon(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['dungeon_id', 'total_rooms', 'room_preview']);

        $this->assertDatabaseHas('dungeons', [
            'user_id' => $this->user->id,
            'status'  => 'active',
        ]);
    }

    public function test_start_returns_422_when_dungeon_already_active(): void
    {
        // Start a dungeon
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id]);

        // Try to start another
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id])
             ->assertStatus(422);
    }

    public function test_start_returns_422_for_invalid_zone(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => 99999])
             ->assertStatus(422);
    }

    // ─── Enter ───────────────────────────────────────────────────────────────

    public function test_enter_advances_dungeon(): void
    {
        $start = $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id]);
        $dungeonId = $start->json('dungeon_id');

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/dungeon/{$dungeonId}/enter")
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_enter_returns_422_for_unknown_dungeon(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/9999/enter')
             ->assertStatus(422);
    }

    public function test_cannot_enter_another_users_dungeon(): void
    {
        $other = User::factory()->create();
        $raceId  = \Illuminate\Support\Facades\DB::table('races')->value('id');
        $classId = \Illuminate\Support\Facades\DB::table('classes')->value('id');
        $traitId = \Illuminate\Support\Facades\DB::table('traits')->value('id');
        Hero::create(['user_id' => $other->id, 'race_id' => $raceId, 'class_id' => $classId, 'trait_id' => $traitId, 'name' => 'Autre', 'level' => 1, 'xp' => 0, 'xp_to_next_level' => 100, 'current_hp' => 50, 'max_hp' => 50, 'slot_index' => 0, 'is_active' => true]);

        $start = $this->actingAs($other, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id]);
        $dungeonId = $start->json('dungeon_id');

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/dungeon/{$dungeonId}/enter")
             ->assertStatus(422);
    }

    // ─── Abandon ─────────────────────────────────────────────────────────────

    public function test_abandon_marks_dungeon_as_abandoned(): void
    {
        $start = $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/dungeon/start', ['zone_id' => $this->zone1Id]);
        $dungeonId = $start->json('dungeon_id');

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/dungeon/{$dungeonId}/abandon")
             ->assertStatus(200);

        $this->assertDatabaseHas('dungeons', [
            'id'     => $dungeonId,
            'status' => 'abandoned',
        ]);
    }

    // ─── Auth ────────────────────────────────────────────────────────────────

    public function test_dungeon_routes_require_auth(): void
    {
        $this->getJson('/api/dungeon')->assertStatus(401);
        $this->postJson('/api/dungeon/start')->assertStatus(401);
        $this->postJson('/api/dungeon/1/enter')->assertStatus(401);
        $this->postJson('/api/dungeon/1/abandon')->assertStatus(401);
    }
}
