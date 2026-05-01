<?php

namespace App\Services\Quest;

use App\Models\Hero;
use App\Models\HeroBuff;
use App\Models\User;
use App\Services\LootService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;

/**
 * Handles all quest effect application: B01-B15, D01-D10, EQ01-EQ08, M01-M10.
 * QUESTS_EFFECTS.md §4.
 */
class QuestEffectService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $loot,
    ) {}

    /**
     * Dispatch an effect from a quest branch.
     */
    public function apply(User $user, array $effect): ?array
    {
        return match ($effect['type'] ?? '') {
            'buff'         => $this->applyBuff($user, $effect['id'], $effect['target'] ?? 'leader'),
            'debuff'       => $this->applyDebuff($user, $effect['id'], $effect['target'] ?? 'leader'),
            'gold'         => $this->applyGold($user, $effect['amount'] ?? 0),
            'reputation'   => $this->applyReputation($user, $effect['zone'] ?? null, (int) ($effect['amount'] ?? 0)),
            'loot'         => $this->applyLoot($user, $effect['rarity_min'] ?? 'commun'),
            'team_effect'  => $this->applyTeamEffect($user, $effect),
            'world_effect' => $this->applyWorldEffect($user, $effect),
            default        => null,
        };
    }

    // ── Buffs B01-B15 ────────────────────────────────────────────────────────

    public function applyBuff(User $user, string $buffId, string $target): array
    {
        $components = $this->buffComponents($buffId);
        $heroes     = $this->selectHeroes($user, $target);

        foreach ($heroes as $hero) {
            foreach ($components as $c) {
                HeroBuff::create([
                    'hero_id'           => $hero->id,
                    'buff_key'          => $buffId,
                    'name'              => $c['name'],
                    'source'            => 'quest_buff_' . $buffId,
                    'stat_affected'     => $c['stat'],
                    'modifier_percent'  => $c['percent'],
                    'remaining_combats' => $c['duration'],
                    'is_buff'           => ($c['percent'] >= 0),
                    'is_debuff'         => false,
                ]);
            }
        }

        return ['type' => 'buff', 'id' => $buffId, 'name' => $components[0]['name'] ?? $buffId];
    }

    // ── Debuffs D01-D10 ───────────────────────────────────────────────────────

    public function applyDebuff(User $user, string $debuffId, string $target): array
    {
        $components = $this->debuffComponents($debuffId);
        $heroes     = $this->selectHeroes($user, $target);
        $maxDur     = (int) $this->settings->get('QUEST_DEBUFF_DURATION_MAX', 20);

        foreach ($heroes as $hero) {
            foreach ($components as $c) {
                HeroBuff::create([
                    'hero_id'           => $hero->id,
                    'buff_key'          => $debuffId,
                    'name'              => $c['name'],
                    'source'            => 'quest_debuff_' . $debuffId,
                    'stat_affected'     => $c['stat'],
                    'modifier_percent'  => -abs($c['percent']),
                    'remaining_combats' => min($c['duration'], $maxDur),
                    'is_buff'           => false,
                    'is_debuff'         => true,
                ]);
            }
        }

        return ['type' => 'debuff', 'id' => $debuffId, 'name' => $components[0]['name'] ?? $debuffId];
    }

    // ── Team effects EQ01-EQ08 ────────────────────────────────────────────────

    public function applyTeamEffect(User $user, array $effect): ?array
    {
        $id     = $effect['id'] ?? '';
        $heroes = $user->activeHeroes()->get();

        switch ($id) {
            case 'EQ01': // Héros Perdu — absent pour QUEST_HERO_ABSENCE_MAX minutes
                if ($heroes->isEmpty()) return null;
                $target         = $heroes->random();
                $absenceMinutes = (int) $this->settings->get('QUEST_HERO_ABSENCE_MAX', 60);
                HeroBuff::create([
                    'hero_id'           => $target->id,
                    'buff_key'          => 'EQ01',
                    'name'              => 'Héros Perdu',
                    'source'            => 'quest_team_effect',
                    'stat_affected'     => 'absent',
                    'modifier_percent'  => 0,
                    'remaining_combats' => 0,
                    'is_buff'           => false,
                    'is_debuff'         => true,
                    'expires_at'        => now()->addMinutes($absenceMinutes),
                ]);
                return ['type' => 'team_effect', 'id' => $id, 'hero' => $target->name, 'minutes' => $absenceMinutes];

            case 'EQ02': // Héros Blessé — 1 PV, pas de potion 5 combats
                $target = $heroes->sortByDesc(fn($h) => $h->current_hp)->first();
                if (!$target) return null;
                $target->current_hp = 1;
                $target->save();
                HeroBuff::create([
                    'hero_id'           => $target->id,
                    'buff_key'          => 'EQ02',
                    'name'              => 'Héros Blessé',
                    'source'            => 'quest_team_effect',
                    'stat_affected'     => 'injured',
                    'modifier_percent'  => 0,
                    'remaining_combats' => 5,
                    'is_buff'           => false,
                    'is_debuff'         => true,
                ]);
                return ['type' => 'team_effect', 'id' => $id, 'hero' => $target->name];

            case 'EQ03': // Renfort Temporaire — PNJ rejoint pour la quête (stub narratif)
                return ['type' => 'team_effect', 'id' => $id, 'npc_name' => $effect['npc_name'] ?? 'Allié PNJ'];

            case 'EQ04': // Recrue Surprise — héros recruitable 24h à la Taverne
                DB::table('tavern_recruits')->insert([
                    'user_id'    => $user->id,
                    'name'       => 'Recrue Mystérieuse',
                    'level'      => max(1, (int) ($user->level ?? 1) - 2),
                    'rarity'     => 'rare',
                    'expires_at' => now()->addHours(24),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return ['type' => 'team_effect', 'id' => $id, 'expires_in' => '24h'];

            case 'EQ05': // Échange de Position — initiative swappée 15 combats
                if ($heroes->count() < 2) return null;
                [$h1, $h2] = $heroes->take(2)->values()->all();
                foreach ([$h1, $h2] as $h) {
                    HeroBuff::create([
                        'hero_id'           => $h->id,
                        'buff_key'          => 'EQ05',
                        'name'              => 'Échange de Position',
                        'source'            => 'quest_team_effect',
                        'stat_affected'     => 'position_swapped',
                        'modifier_percent'  => 0,
                        'remaining_combats' => 15,
                        'is_buff'           => false,
                        'is_debuff'         => false,
                    ]);
                }
                return ['type' => 'team_effect', 'id' => $id, 'heroes' => [$h1->name, $h2->name]];

            case 'EQ06': // Lien Fraternel — +10% all quand ensemble, 50 combats
                if ($heroes->count() < 2) return null;
                [$h1, $h2] = $heroes->take(2)->values()->all();
                foreach ([$h1, $h2] as $h) {
                    HeroBuff::create([
                        'hero_id'           => $h->id,
                        'buff_key'          => 'EQ06',
                        'name'              => 'Lien Fraternel',
                        'source'            => 'quest_team_effect',
                        'stat_affected'     => 'all',
                        'modifier_percent'  => 10,
                        'remaining_combats' => 50,
                        'is_buff'           => true,
                        'is_debuff'         => false,
                    ]);
                }
                return ['type' => 'team_effect', 'id' => $id, 'heroes' => [$h1->name, $h2->name]];

            case 'EQ07': // Rivalité — +15% ATQ -10% DEF, 30 combats
                if ($heroes->count() < 2) return null;
                [$h1, $h2] = $heroes->take(2)->values()->all();
                foreach ([$h1, $h2] as $h) {
                    foreach ([
                        ['buff_key' => 'EQ07', 'name' => 'Rivalité (ATQ)', 'stat_affected' => 'atq', 'modifier_percent' => 15,  'is_buff' => true,  'is_debuff' => false],
                        ['buff_key' => 'EQ07', 'name' => 'Rivalité (DEF)', 'stat_affected' => 'def', 'modifier_percent' => -10, 'is_buff' => false, 'is_debuff' => true],
                    ] as $row) {
                        HeroBuff::create(array_merge($row, [
                            'hero_id'           => $h->id,
                            'source'            => 'quest_team_effect',
                            'remaining_combats' => 30,
                        ]));
                    }
                }
                return ['type' => 'team_effect', 'id' => $id, 'heroes' => [$h1->name, $h2->name]];

            case 'EQ08': // Mentor — +20% XP pour le héros bas niveau, 50 combats
                $mentor = $heroes->sortByDesc('level')->first();
                $junior = $heroes->sortBy('level')->first();
                if (!$mentor || !$junior || $mentor->id === $junior->id) return null;
                HeroBuff::create([
                    'hero_id'           => $junior->id,
                    'buff_key'          => 'EQ08',
                    'name'              => 'Mentor',
                    'source'            => 'quest_team_effect',
                    'stat_affected'     => 'xp_gain',
                    'modifier_percent'  => 20,
                    'remaining_combats' => 50,
                    'is_buff'           => true,
                    'is_debuff'         => false,
                ]);
                return ['type' => 'team_effect', 'id' => $id, 'mentor' => $mentor->name, 'junior' => $junior->name];
        }

        return null;
    }

    // ── World effects M01-M10 ─────────────────────────────────────────────────

    public function applyWorldEffect(User $user, array $effect): ?array
    {
        $id     = $effect['id'] ?? '';
        $zoneId = $user->current_zone_id;
        if (!$zoneId) return null;

        static $names = [
            'M01' => 'Chemin Débloqué',       'M02' => 'Zone Secrète',
            'M03' => 'Point de Repos',         'M04' => 'Marchand Ambulant',
            'M05' => 'Malédiction de Zone',    'M06' => 'Bénédiction de Zone',
            'M07' => 'Modification du Terrain','M08' => 'Camp de Base',
            'M09' => 'Portail Instable',        'M10' => 'Donjon Caché',
        ];

        $expiresAt = match ($id) {
            'M04'   => now()->addHours(24),
            'M05'   => now()->addHours(2),
            'M06'   => now()->addHours(4),
            default => null,
        };
        $permanent = ($expiresAt === null);

        DB::table('zone_world_effects')->updateOrInsert(
            ['zone_id' => $zoneId, 'user_id' => $user->id, 'effect_id' => $id],
            [
                'name'         => $names[$id] ?? $id,
                'data'         => json_encode($effect['data'] ?? []),
                'is_permanent' => $permanent,
                'expires_at'   => $expiresAt,
                'created_at'   => now(),
            ]
        );

        return ['type' => 'world_effect', 'id' => $id, 'name' => $names[$id] ?? $id, 'permanent' => $permanent];
    }

    // ── Reputation helper (public so SurpriseEventService can call it) ────────

    public function applyReputation(User $user, ?string $zoneSlug, int $amount): ?array
    {
        $zoneId = $zoneSlug
            ? \App\Models\Zone::where('slug', $zoneSlug)->value('id')
            : $user->current_zone_id;

        if (!$zoneId || $amount === 0) return null;

        $max = (int) $this->settings->get('REPUTATION_MAX', 200);
        DB::table('zone_reputation')->updateOrInsert(
            ['user_id' => $user->id, 'zone_id' => $zoneId],
            [
                'reputation' => DB::raw("LEAST(COALESCE(reputation,0) + {$amount}, {$max})"),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ['type' => 'reputation', 'amount' => $amount];
    }

    // ── Hero selector ─────────────────────────────────────────────────────────

    public function selectHeroes(User $user, string $target): \Illuminate\Support\Collection
    {
        $heroes = $user->activeHeroes()->get();
        return match ($target) {
            'party'    => $heroes,
            'leader'   => $heroes->take(1),
            'attacker' => $heroes->sortByDesc(fn($h) => $h->computedStats()['atq'] ?? 0)->take(1),
            default    => $heroes->take(1),
        };
    }

    // ── Buff / debuff definition tables ───────────────────────────────────────

    /**
     * Returns all HeroBuff components to create for a given buff ID.
     * Composite buffs (B03, B06, B10, B11) produce multiple rows.
     * Each entry: [name, stat, percent, duration].
     * QUESTS_EFFECTS.md §4.1.
     */
    public function buffComponents(string $id): array
    {
        $short  = (int) $this->settings->get('QUEST_BUFF_DURATION_SHORT', 10);
        $medium = (int) $this->settings->get('QUEST_BUFF_DURATION_MEDIUM', 30);
        $long   = (int) $this->settings->get('QUEST_BUFF_DURATION_LONG', 100);

        $map = [
            'B01' => [['name' => 'Bénédiction du Village',      'stat' => 'all',              'percent' => 10,  'duration' => $medium]],
            'B02' => [['name' => 'Rage de la Victoire',          'stat' => 'atq',              'percent' => 15,  'duration' => $short]],
            'B03' => [
                      ['name' => 'Sagesse Ancienne',             'stat' => 'int',              'percent' => 15,  'duration' => $medium],
                      ['name' => 'Sagesse Ancienne (XP)',        'stat' => 'xp_gain',          'percent' => 10,  'duration' => $medium],
            ],
            'B04' => [['name' => 'Pieds Légers',                 'stat' => 'vit',              'percent' => 20,  'duration' => $short]],
            'B05' => [['name' => 'Peau de Dragon',               'stat' => 'def',              'percent' => 20,  'duration' => $short]],
            'B06' => [
                      ['name' => 'Baraka',                       'stat' => 'cha',              'percent' => 10,  'duration' => $medium],
                      ['name' => 'Baraka (Loot)',                'stat' => 'loot_chance',      'percent' => 5,   'duration' => $medium],
            ],
            'B07' => [['name' => 'Inspiration Épique',           'stat' => 'all',              'percent' => 10,  'duration' => $long]],
            'B08' => [['name' => 'Festin',                       'stat' => 'hp',               'percent' => 15,  'duration' => $medium]],
            'B09' => [['name' => 'Protection Divine',            'stat' => 'damage_reduction', 'percent' => 15,  'duration' => $short]],
            'B10' => [
                      ['name' => 'Soif de Sang',                 'stat' => 'atq',              'percent' => 20,  'duration' => $medium],
                      ['name' => 'Soif de Sang (DEF)',           'stat' => 'def',              'percent' => -10, 'duration' => $medium],
            ],
            'B11' => [
                      ['name' => 'Concentration Totale',         'stat' => 'crit_chance',      'percent' => 10,  'duration' => $short],
                      ['name' => 'Concentration Totale (ESQ)',   'stat' => 'dodge',            'percent' => 10,  'duration' => $short],
            ],
            'B12' => [['name' => 'Aura du Héros',                'stat' => 'all',              'percent' => 5,   'duration' => $medium]],
            'B13' => [['name' => 'Chance du Survivant',          'stat' => 'dodge',            'percent' => 15,  'duration' => $short]],
            'B14' => [['name' => 'Brise Magique',                'stat' => 'magic_resist',     'percent' => 15,  'duration' => $medium]],
            'B15' => [['name' => 'Trésor Caché',                 'stat' => 'gold_found',       'percent' => 20,  'duration' => $long]],
        ];

        return $map[$id] ?? [['name' => $id, 'stat' => 'all', 'percent' => 10, 'duration' => $short]];
    }

    /**
     * Returns all HeroBuff components for a given debuff ID.
     * percent is stored positive here; applyDebuff negates it.
     * QUESTS_EFFECTS.md §4.2.
     */
    public function debuffComponents(string $id): array
    {
        $map = [
            'D01' => [['name' => 'Malédiction Mineure',          'stat' => 'all',              'percent' => 10, 'duration' => 20]],
            'D02' => [
                      ['name' => 'Gueule de Bois (VIT)',         'stat' => 'vit',              'percent' => 15, 'duration' => 10],
                      ['name' => 'Gueule de Bois (INT)',         'stat' => 'int',              'percent' => 10, 'duration' => 10],
            ],
            'D03' => [
                      ['name' => 'Peur Résiduelle (ATQ)',        'stat' => 'atq',              'percent' => 10, 'duration' => 15],
                      ['name' => 'Peur Résiduelle (Fuite)',      'stat' => 'flee_chance',      'percent' => 5,  'duration' => 15],
            ],
            'D04' => [['name' => 'Empoisonnement Léger',         'stat' => 'hp',               'percent' => 5,  'duration' => 10]],
            'D05' => [['name' => 'Honte Publique',               'stat' => 'cha',              'percent' => 15, 'duration' => 20]],
            'D06' => [['name' => 'Rhume du Donjon',              'stat' => 'all',              'percent' => 10, 'duration' => 10]],
            'D07' => [
                      ['name' => 'Distraction Mentale (INT)',    'stat' => 'int',              'percent' => 10, 'duration' => 15],
                      ['name' => 'Distraction Mentale (VIT)',    'stat' => 'vit',              'percent' => 5,  'duration' => 15],
            ],
            'D08' => [['name' => 'Encombrement',                 'stat' => 'vit',              'percent' => 15, 'duration' => 10]],
            'D09' => [
                      ['name' => 'Mauvais Karma (CHA)',          'stat' => 'cha',              'percent' => 10, 'duration' => 15],
                      ['name' => 'Mauvais Karma (Loot)',         'stat' => 'loot_chance',      'percent' => 5,  'duration' => 15],
            ],
            'D10' => [['name' => 'Fatigue de Quête',             'stat' => 'all',              'percent' => 5,  'duration' => 10]],
        ];

        return $map[$id] ?? [['name' => $id, 'stat' => 'all', 'percent' => 5, 'duration' => 10]];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyGold(User $user, int $amount): array
    {
        if ($amount >= 0) {
            $user->increment('gold', $amount);
        } else {
            $user->decrement('gold', abs($amount));
        }
        $user->gold = max(0, $user->fresh()->gold);
        $user->save();
        return ['type' => 'gold', 'amount' => $amount];
    }

    private function applyLoot(User $user, string $rarityMin): ?array
    {
        $item = $this->loot->rollQuestLoot($user, $rarityMin);
        return $item ? ['type' => 'loot', 'item_name' => $item->name, 'rarity' => $item->rarity] : null;
    }
}
