<?php

namespace Tests\Unit;

use App\Services\IdleService;
use App\Services\SettingsService;
use App\Services\LootService;
use App\Services\NarratorService;
use App\Services\ReputationService;
use PHPUnit\Framework\TestCase;

class IdleServiceTest extends TestCase
{
    private IdleService $idle;

    protected function setUp(): void
    {
        parent::setUp();
        $settings = $this->buildSettings([
            'OFFLINE_EFFICIENCY'  => 75,
            'OFFLINE_MAX_HOURS'   => 12,
            'HEAL_BETWEEN_FIGHTS' => 30,
        ]);
        $loot = $this->createMock(LootService::class);
        $narrator = $this->createMock(NarratorService::class);
        $narrator->method('getComment')->willReturn('...');
        $reputation = $this->createMock(ReputationService::class);
        $reputation->method('addReputation')->willReturn(['reputation' => 0, 'tier' => 'etranger', 'tier_up' => false, 'old_tier' => 'etranger', 'capped' => false]);

        $this->idle = new IdleService($settings, $loot, $narrator, $reputation);
    }

    // ─── calculatePower ───────────────────────────────────────────────────────

    public function test_power_is_integer(): void
    {
        $stats = ['atq' => 10, 'int' => 5, 'def' => 20, 'max_hp' => 100];
        $power = $this->idle->calculatePower($stats);
        $this->assertIsInt($power);
    }

    public function test_power_scales_with_atq(): void
    {
        $low  = $this->idle->calculatePower(['atq' => 10, 'int' => 0, 'def' => 0, 'max_hp' => 100]);
        $high = $this->idle->calculatePower(['atq' => 20, 'int' => 0, 'def' => 0, 'max_hp' => 100]);
        $this->assertGreaterThan($low, $high);
    }

    public function test_power_scales_with_hp(): void
    {
        $low  = $this->idle->calculatePower(['atq' => 10, 'int' => 0, 'def' => 0, 'max_hp' => 50]);
        $high = $this->idle->calculatePower(['atq' => 10, 'int' => 0, 'def' => 0, 'max_hp' => 200]);
        $this->assertGreaterThan($low, $high);
    }

    public function test_power_is_never_negative(): void
    {
        $power = $this->idle->calculatePower(['atq' => 0, 'int' => 0, 'def' => 0, 'max_hp' => 0]);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    // ─── getWinChance ────────────────────────────────────────────────────────

    public function test_win_chance_dominant_hero(): void
    {
        $this->assertSame(95, $this->idle->getWinChance(150));
        $this->assertSame(95, $this->idle->getWinChance(200));
    }

    public function test_win_chance_equal(): void
    {
        $this->assertSame(75, $this->idle->getWinChance(100));
    }

    public function test_win_chance_slight_disadvantage(): void
    {
        $this->assertSame(50, $this->idle->getWinChance(80));
    }

    public function test_win_chance_big_disadvantage(): void
    {
        $this->assertSame(25, $this->idle->getWinChance(60));
    }

    public function test_win_chance_overwhelmed(): void
    {
        $this->assertSame(5, $this->idle->getWinChance(10));
    }

    public function test_win_chance_boundaries(): void
    {
        // Exact boundary values
        $this->assertSame(95, $this->idle->getWinChance(150));
        $this->assertSame(75, $this->idle->getWinChance(100));
        $this->assertSame(50, $this->idle->getWinChance(70));
        $this->assertSame(25, $this->idle->getWinChance(50));
        $this->assertSame(5,  $this->idle->getWinChance(49));
    }

    public function test_win_chance_always_between_5_and_95(): void
    {
        foreach ([1, 25, 50, 75, 100, 150, 300] as $ratio) {
            $chance = $this->idle->getWinChance($ratio);
            $this->assertGreaterThanOrEqual(5, $chance);
            $this->assertLessThanOrEqual(95, $chance);
        }
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function buildSettings(array $values): SettingsService
    {
        $mock = $this->createMock(SettingsService::class);
        $mock->method('get')->willReturnCallback(
            fn(string $key, int $default = 0) => $values[$key] ?? $default
        );
        return $mock;
    }
}
