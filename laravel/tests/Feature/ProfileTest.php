<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->user = User::factory()->create(['gold' => 500]);
    }

    public function test_profile_returns_200(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/profile')
             ->assertStatus(200)
             ->assertJsonStructure(['user', 'heroes', 'stats', 'economy_log', 'ai_budget']);
    }

    public function test_profile_contains_user_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/profile');

        $response->assertJsonPath('user.username', $this->user->username)
                 ->assertJsonPath('user.gold', 500);
    }

    public function test_profile_stats_are_integers(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/profile');

        $stats = $response->json('stats');
        $this->assertIsInt($stats['total_kills']);
        $this->assertIsInt($stats['quests_done']);
        $this->assertIsInt($stats['gold_earned']);
    }

    public function test_profile_update_narrator_frequency(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->patchJson('/api/profile', ['narrator_frequency' => 'rare'])
             ->assertStatus(200)
             ->assertJsonPath('user.narrator_frequency', 'rare');

        $this->assertDatabaseHas('users', [
            'id'                 => $this->user->id,
            'narrator_frequency' => 'rare',
        ]);
    }

    public function test_profile_update_rejects_invalid_frequency(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->patchJson('/api/profile', ['narrator_frequency' => 'SCREAMING'])
             ->assertStatus(422);
    }

    public function test_profile_requires_auth(): void
    {
        $this->getJson('/api/profile')->assertStatus(401);
        $this->patchJson('/api/profile', [])->assertStatus(401);
    }
}
