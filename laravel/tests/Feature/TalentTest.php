<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\HeroTalent;
use App\Models\Talent;
use App\Models\User;
use Database\Seeders\TalentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class TalentTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private Hero $hero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->seed(TalentSeeder::class);

        $this->user = User::factory()->create(['gold' => 10000]);

        $classId = \Illuminate\Support\Facades\DB::table('classes')->value('id');
        $raceId  = \Illuminate\Support\Facades\DB::table('races')->value('id');
        $traitId = \Illuminate\Support\Facades\DB::table('traits')->value('id');

        $this->hero = Hero::create([
            'user_id'          => $this->user->id,
            'race_id'          => $raceId,
            'class_id'         => $classId,
            'trait_id'         => $traitId,
            'name'             => 'Héros Talentueux',
            'level'            => 10,
            'xp'               => 0,
            'xp_to_next_level' => 200,
            'current_hp'       => 100,
            'max_hp'           => 100,
            'talent_points'    => 2,
            'slot_index'       => 0,
            'is_active'        => true,
        ]);
    }

    // ─── Tree ────────────────────────────────────────────────────────────────

    public function test_talent_tree_returns_200(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson("/api/heroes/{$this->hero->id}/talents")
             ->assertStatus(200)
             ->assertJsonStructure(['hero', 'points_available', 'reset_cost', 'branches']);
    }

    public function test_talent_tree_has_three_branches(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson("/api/heroes/{$this->hero->id}/talents");

        $branches = $response->json('branches');
        $this->assertArrayHasKey('offensive', $branches);
        $this->assertArrayHasKey('defensive', $branches);
        $this->assertArrayHasKey('defaut', $branches);
    }

    public function test_talent_tree_returns_404_for_unknown_hero(): void
    {
        $this->actingAs($this->user, 'sanctum')
             ->getJson('/api/heroes/9999/talents')
             ->assertStatus(404);
    }

    public function test_cannot_view_another_users_talent_tree(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other, 'sanctum')
             ->getJson("/api/heroes/{$this->hero->id}/talents")
             ->assertStatus(404);
    }

    // ─── Allocate ────────────────────────────────────────────────────────────

    public function test_allocate_unlocks_tier1_talent(): void
    {
        $talent = Talent::where('class_id', $this->hero->class_id)
            ->where('tier', 1)
            ->first();

        $response = $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$talent->id}/allocate");

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseHas('hero_talents', [
            'hero_id'   => $this->hero->id,
            'talent_id' => $talent->id,
        ]);
    }

    public function test_allocate_fails_without_enough_points(): void
    {
        $this->hero->update(['talent_points' => 0]);

        $talent = Talent::where('class_id', $this->hero->class_id)
            ->where('tier', 1)
            ->first();

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$talent->id}/allocate")
             ->assertStatus(422);
    }

    public function test_allocate_fails_for_duplicate(): void
    {
        $talent = Talent::where('class_id', $this->hero->class_id)
            ->where('tier', 1)
            ->first();

        // First allocation
        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$talent->id}/allocate");

        // Duplicate
        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$talent->id}/allocate")
             ->assertStatus(422);
    }

    public function test_allocate_fails_for_talent_of_wrong_class(): void
    {
        $wrongClassId = \Illuminate\Support\Facades\DB::table('classes')
            ->where('id', '!=', $this->hero->class_id)
            ->value('id');

        if (!$wrongClassId) {
            $this->markTestSkipped('Only one class available.');
        }

        $wrongTalent = Talent::where('class_id', $wrongClassId)->first();

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$wrongTalent->id}/allocate")
             ->assertStatus(422);
    }

    public function test_allocate_fails_tier2_without_enough_branch_points(): void
    {
        $talent = Talent::where('class_id', $this->hero->class_id)
            ->where('tier', 2)
            ->first();

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/{$talent->id}/allocate")
             ->assertStatus(422);
    }

    // ─── Reset ───────────────────────────────────────────────────────────────

    public function test_reset_removes_all_talents(): void
    {
        // Unlock a talent first
        $talent = Talent::where('class_id', $this->hero->class_id)->where('tier', 1)->first();
        HeroTalent::create(['hero_id' => $this->hero->id, 'talent_id' => $talent->id, 'unlocked_at' => now()]);

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/reset")
             ->assertStatus(200)
             ->assertJsonStructure(['message', 'gold_spent', 'new_gold_total']);

        $this->assertDatabaseMissing('hero_talents', ['hero_id' => $this->hero->id]);
    }

    public function test_reset_deducts_gold(): void
    {
        $goldBefore = $this->user->gold;

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/reset");

        $this->assertLessThan($goldBefore, $this->user->fresh()->gold);
    }

    public function test_reset_fails_with_insufficient_gold(): void
    {
        $this->user->update(['gold' => 0]);

        $this->actingAs($this->user, 'sanctum')
             ->postJson("/api/heroes/{$this->hero->id}/talents/reset")
             ->assertStatus(422);
    }

    // ─── Auth ────────────────────────────────────────────────────────────────

    public function test_talent_routes_require_auth(): void
    {
        $this->getJson("/api/heroes/1/talents")->assertStatus(401);
        $this->postJson("/api/heroes/1/talents/1/allocate")->assertStatus(401);
        $this->postJson("/api/heroes/1/talents/reset")->assertStatus(401);
    }
}
