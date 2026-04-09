<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\HeroBuff;
use App\Models\TavernRecruit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class TavernTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user  = User::factory()->create(['gold' => 10000]);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    // ─── Index ───────────────────────────────────────────────────────────────

    public function test_tavern_index_returns_200(): void
    {
        $response = $this->auth()->getJson('/api/tavern');

        $response->assertStatus(200)
                 ->assertJsonStructure(['recruits', 'hero_debuffs', 'narrator_comment']);
    }

    public function test_tavern_generates_recruits_if_empty(): void
    {
        $response = $this->auth()->getJson('/api/tavern');

        $recruits = $response->json('recruits');
        $this->assertNotEmpty($recruits);
        $this->assertArrayHasKey('name', $recruits[0]);
        $this->assertArrayHasKey('hire_cost', $recruits[0]);
    }

    public function test_tavern_purges_expired_recruits(): void
    {
        // Insert an expired recruit directly
        $raceId  = \DB::table('races')->first()->id;
        $classId = \DB::table('classes')->first()->id;
        $traitId = \DB::table('traits')->first()->id;

        TavernRecruit::create([
            'user_id'    => $this->user->id,
            'race_id'    => $raceId,
            'class_id'   => $classId,
            'trait_id'   => $traitId,
            'name'       => 'Expired McDead',
            'hire_cost'  => 100,
            'is_hired'   => false,
            'expires_at' => now()->subDay(),
        ]);

        $this->auth()->getJson('/api/tavern');

        $this->assertDatabaseMissing('tavern_recruits', ['name' => 'Expired McDead']);
    }

    public function test_tavern_shows_hero_debuffs(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);

        HeroBuff::create([
            'hero_id'           => $hero->id,
            'buff_key'          => 'D01',
            'name'              => 'Peur',
            'source'            => 'quest_test',
            'stat_affected'     => 'atq',
            'value'             => -5,
            'modifier_percent'  => -10,
            'remaining_combats' => 5,
            'is_debuff'         => true,
        ]);

        $response = $this->auth()->getJson('/api/tavern');
        $heroDebuffs = $response->json('hero_debuffs');

        $this->assertNotEmpty($heroDebuffs);
        $this->assertSame($hero->id, $heroDebuffs[0]['hero_id']);
        $this->assertNotEmpty($heroDebuffs[0]['debuffs']);
    }

    // ─── Hire ────────────────────────────────────────────────────────────────

    public function test_hire_returns_201_and_creates_hero(): void
    {
        $this->auth()->getJson('/api/tavern'); // generate recruits
        $recruitId = TavernRecruit::where('user_id', $this->user->id)->first()->id;

        $response = $this->auth()->postJson("/api/tavern/hire/{$recruitId}");

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'hero_id', 'gold_spent', 'narrator_comment']);

        $this->assertDatabaseHas('heroes', ['user_id' => $this->user->id]);
    }

    public function test_hire_deducts_gold(): void
    {
        $this->auth()->getJson('/api/tavern');
        $recruit = TavernRecruit::where('user_id', $this->user->id)->first();
        $goldBefore = $this->user->fresh()->gold;

        $this->auth()->postJson("/api/tavern/hire/{$recruit->id}");

        $this->assertSame($goldBefore - $recruit->hire_cost, $this->user->fresh()->gold);
    }

    public function test_hire_marks_recruit_as_hired(): void
    {
        $this->auth()->getJson('/api/tavern');
        $recruitId = TavernRecruit::where('user_id', $this->user->id)->first()->id;

        $this->auth()->postJson("/api/tavern/hire/{$recruitId}");

        $this->assertDatabaseHas('tavern_recruits', [
            'id'       => $recruitId,
            'is_hired' => true,
        ]);
    }

    public function test_hire_returns_404_for_unknown_recruit(): void
    {
        $this->auth()->postJson('/api/tavern/hire/9999')
             ->assertStatus(404);
    }

    public function test_hire_returns_422_when_team_is_full(): void
    {
        // Fill all 5 hero slots
        for ($i = 1; $i <= 5; $i++) {
            Hero::factory()->create(['user_id' => $this->user->id, 'slot_index' => $i]);
        }

        $this->auth()->getJson('/api/tavern');
        $recruitId = TavernRecruit::where('user_id', $this->user->id)->first()->id;

        $this->auth()->postJson("/api/tavern/hire/{$recruitId}")
             ->assertStatus(422);
    }

    public function test_hire_returns_422_when_insufficient_gold(): void
    {
        $this->user->update(['gold' => 0]);

        $this->auth()->getJson('/api/tavern');
        $recruitId = TavernRecruit::where('user_id', $this->user->id)->first()->id;

        $this->auth()->postJson("/api/tavern/hire/{$recruitId}")
             ->assertStatus(422);
    }

    public function test_cannot_hire_another_users_recruit(): void
    {
        $other = User::factory()->create(['gold' => 10000]);
        $raceId  = \DB::table('races')->first()->id;
        $classId = \DB::table('classes')->first()->id;
        $traitId = \DB::table('traits')->first()->id;

        // Create a recruit directly for the other user
        $otherRecruit = TavernRecruit::create([
            'user_id'    => $other->id,
            'race_id'    => $raceId,
            'class_id'   => $classId,
            'trait_id'   => $traitId,
            'name'       => 'NotForYou le Pas Doué',
            'hire_cost'  => 100,
            'is_hired'   => false,
            'expires_at' => now()->addDay(),
        ]);

        // Our user tries to hire the other user's recruit
        $this->auth()->postJson("/api/tavern/hire/{$otherRecruit->id}")
             ->assertStatus(404);
    }

    // ─── Remove Debuff ───────────────────────────────────────────────────────

    public function test_remove_debuff_returns_200_and_deletes_buff(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);
        $buff = HeroBuff::create([
            'hero_id'           => $hero->id,
            'buff_key'          => 'D01',
            'name'              => 'Malédiction de Déf',
            'source'            => 'quest_D01',
            'stat_affected'     => 'def',
            'value'             => -3,
            'modifier_percent'  => -15,
            'remaining_combats' => 10,
            'is_debuff'         => true,
        ]);

        $response = $this->auth()->postJson('/api/tavern/remove-debuff', [
            'hero_id' => $hero->id,
            'buff_id' => $buff->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'gold_spent']);

        $this->assertDatabaseMissing('hero_buffs', ['id' => $buff->id]);
    }

    public function test_remove_debuff_deducts_gold(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id, 'level' => 2]);
        $buff = HeroBuff::create([
            'hero_id'           => $hero->id,
            'buff_key'          => 'D01',
            'name'              => 'Faiblesse',
            'source'            => 'D01',
            'stat_affected'     => 'atq',
            'value'             => -5,
            'modifier_percent'  => -10,
            'remaining_combats' => 5,
            'is_debuff'         => true,
        ]);

        $goldBefore = $this->user->fresh()->gold;

        $this->auth()->postJson('/api/tavern/remove-debuff', [
            'hero_id' => $hero->id,
            'buff_id' => $buff->id,
        ]);

        $this->assertLessThan($goldBefore, $this->user->fresh()->gold);
    }

    public function test_remove_debuff_returns_404_for_unknown_hero(): void
    {
        $this->auth()->postJson('/api/tavern/remove-debuff', [
            'hero_id' => 9999,
            'buff_id' => 1,
        ])->assertStatus(404);
    }

    public function test_remove_debuff_returns_422_when_insufficient_gold(): void
    {
        $this->user->update(['gold' => 0]);
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);
        $buff = HeroBuff::create([
            'hero_id'           => $hero->id,
            'buff_key'          => 'D02',
            'name'              => 'Lenteur',
            'source'            => 'D02',
            'stat_affected'     => 'vit',
            'value'             => -2,
            'modifier_percent'  => -5,
            'remaining_combats' => 3,
            'is_debuff'         => true,
        ]);

        $this->auth()->postJson('/api/tavern/remove-debuff', [
            'hero_id' => $hero->id,
            'buff_id' => $buff->id,
        ])->assertStatus(422);
    }

    public function test_cannot_remove_another_users_hero_debuff(): void
    {
        $other = User::factory()->create();
        $hero  = Hero::factory()->create(['user_id' => $other->id]);
        $buff  = HeroBuff::create([
            'hero_id'           => $hero->id,
            'buff_key'          => 'D03',
            'name'              => 'Fragilité',
            'source'            => 'D03',
            'stat_affected'     => 'hp',
            'value'             => -10,
            'modifier_percent'  => -5,
            'remaining_combats' => 5,
            'is_debuff'         => true,
        ]);

        $this->auth()->postJson('/api/tavern/remove-debuff', [
            'hero_id' => $hero->id,
            'buff_id' => $buff->id,
        ])->assertStatus(404);
    }

    // ─── Auth guard ──────────────────────────────────────────────────────────

    public function test_tavern_routes_require_auth(): void
    {
        $this->getJson('/api/tavern')->assertStatus(401);
        $this->postJson('/api/tavern/hire/1')->assertStatus(401);
        $this->postJson('/api/tavern/remove-debuff')->assertStatus(401);
    }
}
