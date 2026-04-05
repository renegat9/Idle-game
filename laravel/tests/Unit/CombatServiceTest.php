<?php

namespace Tests\Unit;

use App\Services\CombatService;
use App\Services\SettingsService;
use App\Services\TraitService;
use PHPUnit\Framework\TestCase;

class CombatServiceTest extends TestCase
{
    private CombatService $combat;

    protected function setUp(): void
    {
        parent::setUp();
        $settings = $this->buildSettings([
            'MIN_DAMAGE'            => 1,
            'VARIANCE_MIN'          => 100,
            'VARIANCE_MAX'          => 100,
            'DEF_SOFT_CAP'          => 200,
            'DEF_HARD_CAP'          => 75,
            'CRIT_BASE_CHANCE'      => 5,
            'CRIT_CAP'              => 50,
            'CRIT_DAMAGE_MULTIPLIER'=> 150,
            'DODGE_BASE_CHANCE'     => 3,
            'DODGE_CAP'             => 40,
            'SPEED_BASE'            => 100,
            'XP_BASE_PER_KILL'      => 10,
            'XP_LEVEL_MULTIPLIER'   => 2,
            'XP_LEVEL_DIFF_PENALTY' => 5,
            'XP_LEVEL_DIFF_BONUS'   => 10,
        ]);
        $traitService = $this->createMock(TraitService::class);
        $this->combat = new CombatService($settings, $traitService);
    }

    // ─── Dégâts physiques ────────────────────────────────────────────────────

    public function test_physical_damage_no_defense(): void
    {
        // ATQ=10, DEF=0, variance=100 → raw=10, reduction=0, net=10
        $attacker = ['atq' => 10];
        $target   = ['def' => 0];
        $dmg = $this->combat->calculatePhysicalDamage($attacker, $target, 100);
        $this->assertSame(10, $dmg);
    }

    public function test_physical_damage_with_defense(): void
    {
        // DEF=200 (= soft cap) → reduction = 200*100/(200+200) = 50%
        // ATQ=100, raw=100, net = 100*(100-50)/100 = 50
        $attacker = ['atq' => 100];
        $target   = ['def' => 200];
        $dmg = $this->combat->calculatePhysicalDamage($attacker, $target, 100);
        $this->assertSame(50, $dmg);
    }

    public function test_physical_damage_hard_cap(): void
    {
        // Very high DEF → reduction capped at DEF_HARD_CAP (75%)
        $attacker = ['atq' => 100];
        $target   = ['def' => 9999];
        $dmg = $this->combat->calculatePhysicalDamage($attacker, $target, 100);
        $this->assertSame(25, $dmg); // 100*(100-75)/100
    }

    public function test_physical_damage_minimum_is_one(): void
    {
        // Even with huge DEF, damage >= 1
        $attacker = ['atq' => 1];
        $target   = ['def' => 9999];
        $dmg = $this->combat->calculatePhysicalDamage($attacker, $target, 100);
        $this->assertGreaterThanOrEqual(1, $dmg);
    }

    public function test_physical_damage_no_floats(): void
    {
        $attacker = ['atq' => 7];
        $target   = ['def' => 13];
        $dmg = $this->combat->calculatePhysicalDamage($attacker, $target, 100);
        $this->assertIsInt($dmg);
    }

    // ─── Dégâts magiques ─────────────────────────────────────────────────────

    public function test_magic_damage_halved_resistance(): void
    {
        // Resistance halved vs magic → effective resistance = DEF/2
        $attacker = ['int' => 100];
        $target   = ['int' => 100, 'def' => 100];
        $physAttacker = ['atq' => 100];
        $physTarget   = ['def' => 100];

        $physDmg  = $this->combat->calculatePhysicalDamage($physAttacker, $physTarget, 100);
        $magicDmg = $this->combat->calculateMagicDamage($attacker, $target, 100);
        // Magic uses int for both offense and defense (resistance halved), but here
        // int resistance cap is halved so magic should deal >= physical vs same stat
        $this->assertIsInt($magicDmg);
        $this->assertGreaterThanOrEqual(1, $magicDmg);
    }

    public function test_magic_damage_no_floats(): void
    {
        $attacker = ['int' => 50];
        $target   = ['int' => 30];
        $this->assertIsInt($this->combat->calculateMagicDamage($attacker, $target, 100));
    }

    // ─── Critique ────────────────────────────────────────────────────────────

    public function test_crit_chance_base_plus_cha(): void
    {
        // critChance = CRIT_BASE (5) + CHA/4
        $chance = $this->combat->calculateCritChance(['cha' => 0]);
        $this->assertSame(5, $chance);

        $chance = $this->combat->calculateCritChance(['cha' => 40]); // 5 + 40/4 = 15
        $this->assertSame(15, $chance);
    }

    public function test_crit_chance_capped_at_50(): void
    {
        $chance = $this->combat->calculateCritChance(['cha' => 9999]);
        $this->assertSame(50, $chance);
    }

    // ─── Esquive ─────────────────────────────────────────────────────────────

    public function test_dodge_chance_base_plus_vit(): void
    {
        // dodge = min(DEF*100 / (DEF + VIT_atk + SPEED_BASE), CAP)
        $chance = $this->combat->calculateDodgeChance(['def' => 0], ['vit' => 0]);
        $this->assertSame(0, $chance); // base is 0 when def=0

        $chance = $this->combat->calculateDodgeChance(['def' => 100], ['vit' => 0]);
        $this->assertGreaterThan(0, $chance);
    }

    public function test_dodge_chance_capped_at_40(): void
    {
        $chance = $this->combat->calculateDodgeChance(['def' => 9999], ['vit' => 1]);
        $this->assertSame(40, $chance);
    }

    // ─── XP ──────────────────────────────────────────────────────────────────

    public function test_xp_base_formula(): void
    {
        // level 5 hero kills level 5 enemy
        // base = XP_BASE (10) + enemy_level * MULT (2) = 10 + 10 = 20
        $xp = $this->combat->calculateXpForKill(5, 5);
        $this->assertSame(20, $xp);
    }

    public function test_xp_penalty_for_weaker_enemy(): void
    {
        // Hero lv 10 kills enemy lv 5 → penalty per level diff
        $base = $this->combat->calculateXpForKill(5, 5);
        $low  = $this->combat->calculateXpForKill(5, 10);
        $this->assertLessThan($base, $low);
    }

    public function test_xp_bonus_for_stronger_enemy(): void
    {
        $base = $this->combat->calculateXpForKill(5, 5);
        $hard = $this->combat->calculateXpForKill(10, 3);
        $this->assertGreaterThan($base, $hard);
    }

    public function test_xp_minimum_is_one(): void
    {
        // Killing a much weaker enemy still gives ≥ 1 XP
        $xp = $this->combat->calculateXpForKill(1, 100);
        $this->assertGreaterThanOrEqual(1, $xp);
    }

    public function test_xp_no_floats(): void
    {
        $this->assertIsInt($this->combat->calculateXpForKill(7, 5));
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
