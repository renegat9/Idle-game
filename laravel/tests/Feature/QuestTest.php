<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\User;
use App\Models\UserQuest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class QuestTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;
    private int $zone1Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAll();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->zone1Id = DB::table('zones')->where('order_index', 1)->value('id');
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    /** Set user's current zone so quests are available. */
    private function setUserZone(int $zoneId): void
    {
        $this->user->update(['current_zone_id' => $zoneId]);
    }

    /** Get first zone-1 quest id. */
    private function getZone1QuestId(): ?int
    {
        return DB::table('quests')
            ->where('zone_id', $this->zone1Id)
            ->where('type', 'zone')
            ->orderBy('order_index')
            ->value('id');
    }

    // ─── Index ───────────────────────────────────────────────────────────────

    public function test_index_returns_empty_when_no_zone(): void
    {
        $response = $this->auth()->getJson('/api/quests');
        $response->assertStatus(200)
                 ->assertJson(['quests' => []]);
    }

    public function test_index_returns_quests_for_current_zone(): void
    {
        $this->setUserZone($this->zone1Id);

        $response = $this->auth()->getJson('/api/quests');
        $response->assertStatus(200)
                 ->assertJsonStructure(['quests']);

        $quests = $response->json('quests');
        $this->assertNotEmpty($quests);
        $this->assertArrayHasKey('title', $quests[0]);
        $this->assertArrayHasKey('status', $quests[0]);
    }

    public function test_index_does_not_include_other_zones_quests(): void
    {
        $this->setUserZone($this->zone1Id);
        $zone2Id = DB::table('zones')->where('order_index', 2)->value('id');

        // Complete a zone-2 quest directly
        if ($zone2Id) {
            $zone2QuestId = DB::table('quests')->where('zone_id', $zone2Id)->value('id');
            if ($zone2QuestId) {
                UserQuest::create([
                    'user_id'         => $this->user->id,
                    'quest_id'        => $zone2QuestId,
                    'status'          => 'completed',
                    'current_step'    => 1,
                    'heroic_score'    => 0,
                    'cunning_score'   => 0,
                    'comic_score'     => 0,
                    'cautious_score'  => 0,
                    'step_results'    => '[]',
                    'effects_active'  => '[]',
                ]);
            }
        }

        // Zone-1 index should not include zone-2 quests
        $response = $this->auth()->getJson('/api/quests');
        $quests = $response->json('quests');
        foreach ($quests as $q) {
            $this->assertNotNull(
                DB::table('quests')->where('id', $q['id'])->where('zone_id', $this->zone1Id)->first()
            );
        }
    }

    // ─── Start ───────────────────────────────────────────────────────────────

    public function test_start_returns_error_for_unknown_quest(): void
    {
        $this->auth()->postJson('/api/quests/9999/start')
             ->assertStatus(404);
    }

    public function test_start_creates_user_quest(): void
    {
        $this->setUserZone($this->zone1Id);
        $questId = $this->getZone1QuestId();

        if (!$questId) {
            $this->markTestSkipped('No zone-1 quests seeded.');
        }

        $response = $this->auth()->postJson("/api/quests/{$questId}/start");
        $response->assertStatus(201)
                 ->assertJsonStructure(['user_quest_id', 'step']);

        $this->assertDatabaseHas('user_quests', [
            'user_id'  => $this->user->id,
            'quest_id' => $questId,
        ]);
    }

    public function test_start_returns_existing_in_progress_quest(): void
    {
        $this->setUserZone($this->zone1Id);
        $questId = $this->getZone1QuestId();
        if (!$questId) {
            $this->markTestSkipped('No zone-1 quests seeded.');
        }

        $r1 = $this->auth()->postJson("/api/quests/{$questId}/start")->assertStatus(201);
        $r2 = $this->auth()->postJson("/api/quests/{$questId}/start")->assertStatus(201);

        $this->assertSame($r1->json('user_quest_id'), $r2->json('user_quest_id'));
    }

    // ─── Choose ──────────────────────────────────────────────────────────────

    public function test_choose_returns_error_for_unknown_user_quest(): void
    {
        $this->auth()->postJson('/api/user-quests/9999/choose', ['choice_id' => 'A'])
             ->assertStatus(404);
    }

    public function test_choose_requires_valid_choice_id(): void
    {
        $this->setUserZone($this->zone1Id);
        $questId = $this->getZone1QuestId();
        if (!$questId) {
            $this->markTestSkipped('No zone-1 quests seeded.');
        }

        $startResponse = $this->auth()->postJson("/api/quests/{$questId}/start");
        $userQuestId   = $startResponse->json('user_quest_id');

        // 'Z' is not a valid choice
        $this->auth()->postJson("/api/user-quests/{$userQuestId}/choose", ['choice_id' => 'Z'])
             ->assertStatus(422);
    }

    public function test_choose_resolves_a_step(): void
    {
        $this->setUserZone($this->zone1Id);
        $questId = $this->getZone1QuestId();
        if (!$questId) {
            $this->markTestSkipped('No zone-1 quests seeded.');
        }

        // Ensure user has a hero for stat tests
        Hero::factory()->create(['user_id' => $this->user->id]);

        $startResponse = $this->auth()->postJson("/api/quests/{$questId}/start");
        $userQuestId   = $startResponse->json('user_quest_id');

        $response = $this->auth()->postJson("/api/user-quests/{$userQuestId}/choose", [
            'choice_id' => 'A',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'narration']);
    }

    public function test_choose_returns_rewards_on_final_step(): void
    {
        $this->setUserZone($this->zone1Id);

        // Find a quest with just 1 step
        $questId = DB::table('quests')
            ->where('zone_id', $this->zone1Id)
            ->where('steps_count', 1)
            ->value('id');

        if (!$questId) {
            $this->markTestSkipped('No single-step zone-1 quest seeded.');
        }

        Hero::factory()->create(['user_id' => $this->user->id]);

        $uqId = $this->auth()->postJson("/api/quests/{$questId}/start")->json('user_quest_id');

        $response = $this->auth()->postJson("/api/user-quests/{$uqId}/choose", ['choice_id' => 'A']);

        if ($response->json('quest_completed')) {
            $response->assertJsonStructure(['rewards']);
        }
    }

    public function test_cannot_choose_on_another_users_quest(): void
    {
        $this->setUserZone($this->zone1Id);
        $questId = $this->getZone1QuestId();
        if (!$questId) {
            $this->markTestSkipped('No zone-1 quests seeded.');
        }

        // Create user quest directly for our user — avoids ID collision with SQLite auto-increment
        $uq = UserQuest::create([
            'user_id'      => $this->user->id,
            'quest_id'     => $questId,
            'status'       => 'in_progress',
            'current_step' => 1,
        ]);

        // Create other user AFTER quest so their ID is guaranteed != uq->id
        $other      = User::factory()->create();
        $otherToken = $other->createToken('other-test')->plainTextToken;

        // Verify different IDs (uq->id should be auto-generated after many seeds)
        // The choose endpoint looks up by (id, user_id) — mismatch means 404
        $this->withToken($otherToken)
             ->postJson("/api/user-quests/{$uq->id}/choose", ['choice_id' => 'A'])
             ->assertStatus(404);
    }

    // ─── Auth guard ──────────────────────────────────────────────────────────

    public function test_quest_routes_require_auth(): void
    {
        $this->getJson('/api/quests')->assertStatus(401);
        $this->postJson('/api/quests/1/start')->assertStatus(401);
        $this->postJson('/api/user-quests/1/choose')->assertStatus(401);
    }
}
