<?php

namespace Tests\Unit;

use App\Models\Hero;
use App\Models\Trait_;
use App\Services\TraitService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class TraitServiceTest extends TestCase
{
    private function buildSettings(array $values): SettingsService
    {
        $mock = $this->createMock(SettingsService::class);
        $mock->method('get')->willReturnCallback(
            fn(string $key, int $default = 0) => $values[$key] ?? $default
        );
        return $mock;
    }

    private function makeService(array $settings = []): TraitService
    {
        return new TraitService($this->buildSettings($settings));
    }

    /**
     * Build an in-memory Hero with a Trait_ set via setRelation — no DB needed.
     */
    private function heroWithTrait(string $slug, int $level, array $traitData): Hero
    {
        $trait = new Trait_();
        $trait->forceFill([
            'slug'            => $slug,
            'name'            => $slug,
            'description'     => '',
            'base_chance'     => $traitData['base_chance']     ?? 15,
            'chance_level_26' => $traitData['chance_level_26'] ?? $traitData['base_chance'] ?? 15,
            'chance_level_51' => $traitData['chance_level_51'] ?? $traitData['base_chance'] ?? 15,
            'chance_level_76' => $traitData['chance_level_76'] ?? $traitData['base_chance'] ?? 15,
            'effect_data'     => $traitData['effect_data']     ?? [],
        ]);

        $hero = new Hero();
        $hero->forceFill(['level' => $level]);
        $hero->setRelation('trait_', $trait);

        return $hero;
    }

    // ─── getCurrentChance ────────────────────────────────────────────────────

    public function test_couard_chance_level_bracket_1_25(): void
    {
        $svc = $this->makeService();
        $hero = $this->heroWithTrait('couard', 1, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(15, $svc->getCurrentChance($hero));

        $hero25 = $this->heroWithTrait('couard', 25, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(15, $svc->getCurrentChance($hero25));
    }

    public function test_couard_chance_level_bracket_26_50(): void
    {
        $svc = $this->makeService();
        $hero = $this->heroWithTrait('couard', 26, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(18, $svc->getCurrentChance($hero));

        $hero50 = $this->heroWithTrait('couard', 50, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(18, $svc->getCurrentChance($hero50));
    }

    public function test_couard_chance_level_bracket_51_75(): void
    {
        $svc = $this->makeService();
        $hero = $this->heroWithTrait('couard', 51, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(20, $svc->getCurrentChance($hero));

        $hero75 = $this->heroWithTrait('couard', 75, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(20, $svc->getCurrentChance($hero75));
    }

    public function test_couard_chance_level_bracket_76_plus(): void
    {
        $svc = $this->makeService();
        $hero = $this->heroWithTrait('couard', 76, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(10, $svc->getCurrentChance($hero));

        $hero100 = $this->heroWithTrait('couard', 100, [
            'base_chance'     => 15,
            'chance_level_26' => 18,
            'chance_level_51' => 20,
            'chance_level_76' => 10,
        ]);
        $this->assertSame(10, $svc->getCurrentChance($hero100));
    }

    // ─── shouldTrigger ───────────────────────────────────────────────────────

    public function test_should_trigger_always_when_chance_100(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('couard', 1, [
            'base_chance'     => 100,
            'chance_level_26' => 100,
            'chance_level_51' => 100,
            'chance_level_76' => 100,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($svc->shouldTrigger($hero));
        }
    }

    public function test_should_trigger_never_when_chance_zero(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('couard', 1, [
            'base_chance'     => 0,
            'chance_level_26' => 0,
            'chance_level_51' => 0,
            'chance_level_76' => 0,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->assertFalse($svc->shouldTrigger($hero));
        }
    }

    public function test_should_trigger_returns_bool(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('couard', 5, ['base_chance' => 50]);
        $this->assertIsBool($svc->shouldTrigger($hero));
    }

    // ─── getOfflinePowerMultiplier ────────────────────────────────────────────

    public function test_couard_reduces_offline_power(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('couard', 10, []);
        $mult = $svc->getOfflinePowerMultiplier($hero);
        $this->assertIsInt($mult);
        $this->assertLessThan(100, $mult);
    }

    public function test_kleptomane_does_not_reduce_offline_power(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('kleptomane', 10, []);
        $mult = $svc->getOfflinePowerMultiplier($hero);
        $this->assertGreaterThanOrEqual(100, $mult);
    }

    public function test_unknown_trait_returns_100(): void
    {
        $svc  = $this->makeService();
        $hero = $this->heroWithTrait('trait_inexistant', 10, []);
        $mult = $svc->getOfflinePowerMultiplier($hero);
        $this->assertSame(100, $mult);
    }
}
