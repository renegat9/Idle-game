<?php

namespace Tests\Feature;

use App\Models\Hero;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsReferenceData;
use Tests\TestCase;

class HeroTest extends TestCase
{
    use RefreshDatabase, SeedsReferenceData;

    private User $user;
    private string $token;
    private int $raceId;
    private int $classId;
    private int $traitId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;

        $this->raceId  = \DB::table('races')->first()->id;
        $this->classId = \DB::table('classes')->first()->id;
        $this->traitId = \DB::table('traits')->first()->id;
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    // ─── Création ────────────────────────────────────────────────────────────

    public function test_create_hero_returns_201(): void
    {
        $response = $this->auth()->postJson('/api/heroes', [
            'name'     => 'Gruntak le Vaillant',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['hero' => ['id', 'name', 'level', 'computed_stats'], 'narrator_comment']);
    }

    public function test_create_hero_persists_to_db(): void
    {
        $this->auth()->postJson('/api/heroes', [
            'name'     => 'Fizzle',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ])->assertStatus(201);

        $this->assertDatabaseHas('heroes', [
            'user_id' => $this->user->id,
            'name'    => 'Fizzle',
            'level'   => 1,
        ]);
    }

    public function test_create_hero_starts_at_level_1(): void
    {
        $response = $this->auth()->postJson('/api/heroes', [
            'name'     => 'Borak',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ]);

        $this->assertSame(1, $response->json('hero.level'));
    }

    public function test_create_hero_has_positive_hp(): void
    {
        $response = $this->auth()->postJson('/api/heroes', [
            'name'     => 'Thorek',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ]);

        $stats = $response->json('hero.computed_stats');
        $this->assertGreaterThan(0, $stats['max_hp']);
    }

    public function test_create_hero_requires_name(): void
    {
        $this->auth()->postJson('/api/heroes', [
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ])->assertStatus(422);
    }

    public function test_create_hero_name_min_2_chars(): void
    {
        $this->auth()->postJson('/api/heroes', [
            'name'     => 'X',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ])->assertStatus(422);
    }

    public function test_cannot_exceed_hero_slot_limit(): void
    {
        // Fill up all 5 slots
        for ($i = 1; $i <= 5; $i++) {
            Hero::factory()->create(['user_id' => $this->user->id, 'slot_index' => $i]);
        }

        $this->auth()->postJson('/api/heroes', [
            'name'     => 'Trop',
            'race_id'  => $this->raceId,
            'class_id' => $this->classId,
            'trait_id' => $this->traitId,
        ])->assertStatus(422);
    }

    // ─── Liste ───────────────────────────────────────────────────────────────

    public function test_list_heroes_returns_own_heroes(): void
    {
        Hero::factory()->count(2)->create(['user_id' => $this->user->id]);

        $otherUser = User::factory()->create();
        Hero::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->auth()->getJson('/api/heroes');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('heroes'));
    }

    public function test_list_heroes_empty_for_new_user(): void
    {
        $response = $this->auth()->getJson('/api/heroes');
        $response->assertStatus(200)
                 ->assertJson(['heroes' => []]);
    }

    // ─── Équipement ──────────────────────────────────────────────────────────

    public function test_equip_item_on_hero(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);
        $item = Item::factory()->create([
            'user_id'            => $this->user->id,
            'slot'               => 'arme',
            'equipped_by_hero_id'=> null,
        ]);

        $response = $this->auth()->postJson("/api/heroes/{$hero->id}/equip", [
            'item_id' => $item->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'id'                  => $item->id,
            'equipped_by_hero_id' => $hero->id,
        ]);
    }

    public function test_equip_replaces_existing_item_in_slot(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);
        $old  = Item::factory()->create(['user_id' => $this->user->id, 'slot' => 'arme', 'equipped_by_hero_id' => $hero->id]);
        $new  = Item::factory()->create(['user_id' => $this->user->id, 'slot' => 'arme', 'equipped_by_hero_id' => null]);

        $this->auth()->postJson("/api/heroes/{$hero->id}/equip", ['item_id' => $new->id]);

        $this->assertNull($old->fresh()->equipped_by_hero_id);
        $this->assertSame($hero->id, $new->fresh()->equipped_by_hero_id);
    }

    public function test_cannot_equip_another_users_item(): void
    {
        $hero = Hero::factory()->create(['user_id' => $this->user->id]);
        $other = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $other->id, 'slot' => 'arme']);

        $this->auth()->postJson("/api/heroes/{$hero->id}/equip", ['item_id' => $item->id])
             ->assertStatus(404);
    }

    public function test_cannot_equip_on_another_users_hero(): void
    {
        $other = User::factory()->create();
        $hero  = Hero::factory()->create(['user_id' => $other->id]);
        $item  = Item::factory()->create(['user_id' => $this->user->id, 'slot' => 'arme']);

        $this->auth()->postJson("/api/heroes/{$hero->id}/equip", ['item_id' => $item->id])
             ->assertStatus(403);
    }
}
