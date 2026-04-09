<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for GeminiService — all scenarios use fallbacks since AI_ENABLED=0 in tests.
 */
class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeminiService $gemini;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed game_settings so SettingsService has AI_ENABLED and AI_DAILY_BUDGET_LIMIT
        $this->seed(\Database\Seeders\GameSettingsSeeder::class);
        $this->gemini = app(GeminiService::class);
    }

    // ─── canCall ─────────────────────────────────────────────────────────────

    public function test_canCall_returns_false_when_ai_disabled(): void
    {
        // Default seeder sets AI_ENABLED = 0
        $this->assertFalse($this->gemini->canCall('narration'));
    }

    public function test_canCall_returns_false_without_api_key(): void
    {
        // Override AI_ENABLED but leave GEMINI_API_KEY empty
        \Illuminate\Support\Facades\DB::table('game_settings')
            ->where('setting_key', 'AI_ENABLED')
            ->update(['setting_value' => 1]);

        app(\App\Services\SettingsService::class)->flush();

        $gemini = app(\App\Services\GeminiService::class);
        // Rebuild gemini service with fresh settings
        $this->assertFalse($gemini->canCall('narration'));
    }

    // ─── Fallbacks (always available) ────────────────────────────────────────

    public function test_generateNarration_returns_string_when_ai_disabled(): void
    {
        $result = $this->gemini->generateNarration('combat_win', ['hero_name' => 'Grognak']);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_generateNarration_fallback_for_unknown_event(): void
    {
        $result = $this->gemini->generateNarration('unknown_event_xyz', []);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_generateLootText_returns_name_and_description(): void
    {
        $result = $this->gemini->generateLootText('epee', 'rare', 15);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertNotEmpty($result['name']);
        $this->assertNotEmpty($result['description']);
    }

    public function test_generateQuestText_returns_title_description_flavor(): void
    {
        $result = $this->gemini->generateQuestText('foret-maudite', 5);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('flavor', $result);
    }

    public function test_generateBossText_returns_description_and_mechanic(): void
    {
        $result = $this->gemini->generateBossText('La Grande Patate Maléfique');
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('mechanic', $result);
    }

    public function test_generateTavernMusic_returns_fallback(): void
    {
        $result = $this->gemini->generateTavernMusic('taverne');
        $this->assertArrayHasKey('style', $result);
        $this->assertArrayHasKey('prompt', $result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertStringContainsString('music/fallback', $result['file_path']);
    }

    // ─── Budget status ────────────────────────────────────────────────────────

    public function test_budgetStatus_returns_correct_structure(): void
    {
        $status = $this->gemini->budgetStatus();
        $this->assertArrayHasKey('used', $status);
        $this->assertArrayHasKey('limit', $status);
        $this->assertArrayHasKey('percent', $status);
        $this->assertIsInt($status['used']);
        $this->assertIsInt($status['limit']);
        $this->assertIsInt($status['percent']);
    }

    public function test_budgetStatus_used_is_zero_initially(): void
    {
        $status = $this->gemini->budgetStatus();
        $this->assertSame(0, $status['used']);
    }
}
