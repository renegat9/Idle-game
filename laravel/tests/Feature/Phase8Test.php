<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorldBoss;
use App\Services\GeminiService;
use App\Services\SeasonalEventService;
use App\Services\ZoneGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 4 GDD tests:
 * - Zones générées par IA (ZoneGeneratorService)
 * - Boss IA (description via generateBossText)
 * - Ambiance musicale dynamique (GET /api/music/current)
 * - Événements saisonniers (SeasonalEventService + API)
 */
class Phase8Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::factory()->create(['gold' => 10000, 'current_zone_id' => 1]);
    }

    // ─── Zone générée par IA ──────────────────────────────────────────────────

    public function test_zone_generator_creates_zone_in_db(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $generator = app(ZoneGeneratorService::class);
        $zoneId    = $generator->generate();

        $this->assertNotNull($zoneId);
        $zone = DB::table('zones')->where('id', $zoneId)->first();
        $this->assertNotNull($zone);
        $this->assertEquals(1, $zone->ai_generated);
    }

    public function test_zone_generator_creates_monsters(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $generator = app(ZoneGeneratorService::class);
        $zoneId    = $generator->generate();

        $monsterCount = DB::table('monsters')->where('zone_id', $zoneId)->count();
        $this->assertEquals(6, $monsterCount, 'Should generate 4 normal + 1 mini_boss + 1 boss');
    }

    public function test_zone_generator_creates_item_templates(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $generator = app(ZoneGeneratorService::class);
        $zoneId    = $generator->generate();

        $templateCount = DB::table('item_templates')->where('zone_id', $zoneId)->count();
        $this->assertEquals(8, $templateCount, 'Should generate 8 item templates');
    }

    public function test_zone_generator_increments_order_index(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $maxBefore = (int) DB::table('zones')->max('order_index');
        $generator  = app(ZoneGeneratorService::class);
        $generator->generate();

        $maxAfter = (int) DB::table('zones')->max('order_index');
        $this->assertGreaterThan($maxBefore, $maxAfter);
    }

    public function test_zone_generator_valid_element(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $validElements = ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'];
        $generator     = app(ZoneGeneratorService::class);
        $zoneId        = $generator->generate();

        $zone = DB::table('zones')->where('id', $zoneId)->first();
        $this->assertContains($zone->dominant_element, $validElements);
    }

    public function test_generate_zone_fallback_returns_valid_structure(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $gemini = app(GeminiService::class);
        $zone   = $gemini->generateZone(9, 45, 49);

        $this->assertArrayHasKey('name', $zone);
        $this->assertArrayHasKey('slug', $zone);
        $this->assertArrayHasKey('element', $zone);
        $this->assertArrayHasKey('monster_theme', $zone);
        $validElements = ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'];
        $this->assertContains($zone['element'], $validElements);
    }

    // ─── Boss IA ──────────────────────────────────────────────────────────────

    public function test_world_boss_status_includes_description_field(): void
    {
        WorldBoss::create([
            'name'        => 'Le Dragon Distrait',
            'slug'        => 'le-dragon-distrait',
            'total_hp'    => 50000,
            'current_hp'  => 50000,
            'status'      => 'active',
            'description' => 'Un dragon qui ne se souvient plus pourquoi il est là.',
            'spawned_at'  => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/world-boss');

        $response->assertStatus(200)
            ->assertJsonPath('active_boss.description', 'Un dragon qui ne se souvient plus pourquoi il est là.');
    }

    public function test_gemini_fallback_boss_text_has_required_keys(): void
    {
        DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 0]);
        app(\App\Services\SettingsService::class)->flush();

        $gemini = app(GeminiService::class);
        $text   = $gemini->generateBossText('Le Troll Philosophe');

        $this->assertArrayHasKey('description', $text);
        $this->assertArrayHasKey('mechanic', $text);
        $this->assertNotEmpty($text['description']);
        $this->assertNotEmpty($text['mechanic']);
    }

    // ─── Ambiance musicale dynamique ─────────────────────────────────────────

    public function test_music_current_returns_200(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/music/current');

        $response->assertStatus(200)
            ->assertJsonStructure(['style', 'file_path', 'context']);
    }

    public function test_music_current_returns_boss_style_when_boss_active_and_user_contributing(): void
    {
        $boss = WorldBoss::create([
            'name'       => 'Le Lich',
            'slug'       => 'le-lich-test',
            'total_hp'   => 50000,
            'current_hp' => 40000,
            'status'     => 'active',
            'spawned_at' => now(),
        ]);

        // Record contribution
        DB::table('boss_contributions')->insert([
            'boss_id'      => $boss->id,
            'user_id'      => $this->user->id,
            'damage_dealt' => 1000,
            'hits_count'   => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/music/current');

        $response->assertStatus(200);
        $this->assertEquals('boss', $response->json('style'));
    }

    public function test_music_current_returns_taverne_by_default(): void
    {
        // No boss, no quest, no combat log
        $response = $this->actingAs($this->user)
            ->getJson('/api/music/current');

        $response->assertStatus(200);
        $this->assertEquals('taverne', $response->json('style'));
    }

    public function test_music_requires_auth(): void
    {
        $this->getJson('/api/music/current')->assertStatus(401);
    }

    // ─── Événements saisonniers ───────────────────────────────────────────────

    public function test_seasonal_event_service_returns_empty_outside_event(): void
    {
        // Use a date with no event (Feb 1)
        $date   = Carbon::create(2026, 2, 1);
        $events = app(SeasonalEventService::class)->getActiveEvents($date);

        // Saint-Valentin starts Feb 13 — Feb 1 should have no events
        $slugs = array_column($events, 'slug');
        $this->assertNotContains('saint_valentin_maudite', $slugs);
    }

    public function test_seasonal_event_service_detects_active_event(): void
    {
        // Halloween = Oct 28 - Nov 2
        $date   = Carbon::create(2026, 10, 30);
        $events = app(SeasonalEventService::class)->getActiveEvents($date);

        $slugs = array_map(fn($e) => $e->slug, $events);
        $this->assertContains('halloween_raté', $slugs);
    }

    public function test_seasonal_event_service_handles_year_wrap(): void
    {
        // Noël: Dec 20 - Jan 5 (wraps year)
        $dateDec = Carbon::create(2026, 12, 25);
        $dateJan = Carbon::create(2027, 1, 3);

        $service = app(SeasonalEventService::class);

        $eventsDec = $service->getActiveEvents($dateDec);
        $eventsJan = $service->getActiveEvents($dateJan);

        $slugsDec = array_map(fn($e) => $e->slug, $eventsDec);
        $slugsJan = array_map(fn($e) => $e->slug, $eventsJan);

        $this->assertContains('noel_incompetent', $slugsDec);
        $this->assertContains('noel_incompetent', $slugsJan);
    }

    public function test_seasonal_event_xp_bonus_applies_correctly(): void
    {
        // Anniversaire du Donjon: Jul 14-21, +50% XP
        $date    = Carbon::create(2026, 7, 15);
        $service = app(SeasonalEventService::class);

        $baseXp    = 100;
        $boostedXp = $service->applyXpBonus($baseXp, $date);

        $this->assertEquals(150, $boostedXp);
    }

    public function test_seasonal_event_no_floats_in_bonus_calculation(): void
    {
        // Semaine de la Forge: Apr 1-7, +15% rare loot bonus
        $date    = Carbon::create(2026, 4, 3);
        $service = app(SeasonalEventService::class);

        $mods = $service->getActiveModifiers($date);

        $this->assertIsInt($mods['xp_bonus_pct']);
        $this->assertIsInt($mods['gold_bonus_pct']);
        $this->assertIsInt($mods['loot_bonus_pct']);
        $this->assertIsInt($mods['rare_loot_bonus_pct']);
    }

    public function test_seasonal_events_current_endpoint_returns_200(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/events/current');

        $response->assertStatus(200)
            ->assertJsonStructure(['active_events', 'modifiers', 'has_event']);
    }

    public function test_seasonal_events_index_endpoint_returns_all_events(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure(['events']);

        $this->assertGreaterThanOrEqual(5, count($response->json('events')));
    }

    public function test_seasonal_events_require_auth(): void
    {
        $this->getJson('/api/events/current')->assertStatus(401);
        $this->getJson('/api/events')->assertStatus(401);
    }
}
