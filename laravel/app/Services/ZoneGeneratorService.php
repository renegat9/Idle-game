<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Generates procedural zones (zone 9+) using Gemini AI.
 *
 * Per-zone output:
 *  - 1 zone row in `zones`
 *  - 6 monster rows (4 normal + 1 mini_boss + 1 boss)
 *  - 8 item template rows (4 arme/armure + 4 casque/bottes, commun+peu_commun)
 *
 * Validation rules (QUESTS_EFFECTS.md §7.2 style):
 *  - Slug must be unique and sanitized
 *  - Element must be in allowed list
 *  - Stats must be positive integers
 *  - Zone index drives level scaling
 */
class ZoneGeneratorService
{
    // Level band per zone index (zone 9 = levels 45-50, each +5)
    private const BASE_LEVEL     = 45;
    private const LEVEL_STEP     = 5;
    private const BASE_ZONE_IDX  = 9;

    public function __construct(
        private readonly GeminiService  $gemini,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Generate a new procedural zone and persist it to DB.
     * Returns the created zone id, or null on failure.
     */
    public function generate(): ?int
    {
        // Determine next zone index
        $maxOrder  = (int) DB::table('zones')->max('order_index');
        $nextIndex = max($maxOrder + 1, self::BASE_ZONE_IDX);
        $levelMin  = self::BASE_LEVEL + ($nextIndex - self::BASE_ZONE_IDX) * self::LEVEL_STEP;
        $levelMax  = $levelMin + self::LEVEL_STEP - 1;

        // Get AI zone definition
        $zoneDef = $this->gemini->generateZone($nextIndex, $levelMin, $levelMax);

        // Ensure slug uniqueness — zones.slug is varchar(30)
        $slug = $this->uniqueSlug($zoneDef['slug'], 28);

        try {
            $zoneId = DB::transaction(function () use ($zoneDef, $slug, $nextIndex, $levelMin, $levelMax) {
                // Insert zone
                $zoneId = DB::table('zones')->insertGetId([
                    'slug'              => $slug,
                    'name'              => $zoneDef['name'],
                    'description'       => $zoneDef['description'],
                    'level_min'         => $levelMin,
                    'level_max'         => $levelMax,
                    'dominant_element'  => $zoneDef['element'],
                    'is_magical'        => 1,
                    'order_index'       => $nextIndex,
                    'avg_combat_duration' => 90,
                    'ai_generated'      => 1,
                ]);

                // Insert monsters
                $this->insertMonsters($zoneId, $slug, $zoneDef['element'], $levelMin, $levelMax, $zoneDef['monster_theme']);

                // Insert item templates
                $this->insertItemTemplates($zoneId, $slug, $zoneDef['element'], $levelMin, $levelMax);

                return $zoneId;
            });

            Log::info("ZoneGeneratorService: zone {$nextIndex} created (id={$zoneId}, slug={$slug})");
            return $zoneId;

        } catch (\Throwable $e) {
            Log::error('ZoneGeneratorService::generate failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function insertMonsters(int $zoneId, string $zoneSlug, string $element, int $levelMin, int $levelMax, string $theme): void
    {
        $midLevel = intdiv($levelMin + $levelMax, 2);

        $definitions = [
            // 4 normal monsters
            ['type' => 'normal',   'suffix' => 'vagabond',    'mult' => 1,  'level' => $levelMin],
            ['type' => 'normal',   'suffix' => 'eclaireur',   'mult' => 1,  'level' => $midLevel],
            ['type' => 'normal',   'suffix' => 'champion',    'mult' => 1,  'level' => $levelMax],
            ['type' => 'normal',   'suffix' => 'sentinelle',  'mult' => 1,  'level' => $midLevel],
            // 1 mini-boss
            ['type' => 'mini_boss','suffix' => 'capitaine',   'mult' => 3,  'level' => $levelMax],
            // 1 boss
            ['type' => 'boss',     'suffix' => 'seigneur',    'mult' => 8,  'level' => $levelMax],
        ];

        foreach ($definitions as $def) {
            $lvl     = $def['level'];
            $mult    = $def['mult'];
            $baseHp  = $lvl * 10 * $mult;
            $baseAtq = max(1, intdiv($lvl * 3 * $mult, 2));
            $baseDef = max(1, intdiv($lvl * 2 * $mult, 2));
            $baseVit = max(1, intdiv($lvl * $mult, 2));
            $xp      = $lvl * 5 * $mult;
            $goldMin = $lvl * 2 * $mult;
            $goldMax = $lvl * 4 * $mult;
            $lootBonus = match ($def['type']) {
                'mini_boss' => 25,
                'boss'      => 75,
                default     => 0,
            };

            $slug = $this->uniqueSlug($zoneSlug . '_' . $def['suffix'], 45);

            DB::table('monsters')->insert([
                'zone_id'       => $zoneId,
                'name'          => ucfirst($def['suffix']) . " de {$zoneSlug} (IA)",
                'slug'          => $slug,
                'monster_type'  => $def['type'],
                'level'         => $lvl,
                'base_hp'       => $baseHp,
                'base_atq'      => $baseAtq,
                'base_def'      => $baseDef,
                'base_vit'      => $baseVit,
                'base_int'      => 0,
                'base_cha'      => 0,
                'element'       => $element,
                'xp_reward'     => $xp,
                'gold_min'      => $goldMin,
                'gold_max'      => $goldMax,
                'loot_bonus'    => $lootBonus,
                'is_active'     => 1,
            ]);
        }
    }

    private function insertItemTemplates(int $zoneId, string $zoneSlug, string $element, int $levelMin, int $levelMax): void
    {
        $midLevel = intdiv($levelMin + $levelMax, 2);

        $templates = [
            ['slot' => 'arme',      'rarity' => 'commun',     'level' => $levelMin],
            ['slot' => 'arme',      'rarity' => 'peu_commun',  'level' => $midLevel],
            ['slot' => 'armure',    'rarity' => 'commun',     'level' => $levelMin],
            ['slot' => 'armure',    'rarity' => 'peu_commun',  'level' => $midLevel],
            ['slot' => 'casque',    'rarity' => 'commun',     'level' => $levelMin],
            ['slot' => 'casque',    'rarity' => 'peu_commun',  'level' => $midLevel],
            ['slot' => 'bottes',    'rarity' => 'commun',     'level' => $levelMin],
            ['slot' => 'bottes',    'rarity' => 'peu_commun',  'level' => $midLevel],
        ];

        foreach ($templates as $t) {
            $mult = $t['rarity'] === 'peu_commun' ? 2 : 1;
            $lvl  = $t['level'];

            $baseAtq = $t['slot'] === 'arme'   ? $lvl * 3 * $mult : 0;
            $baseDef = $t['slot'] === 'armure' ? $lvl * 2 * $mult : ($t['slot'] === 'casque' ? $lvl * $mult : 0);
            $baseHp  = $t['slot'] === 'casque' ? $lvl * 5 * $mult : 0;
            $baseVit = $t['slot'] === 'bottes' ? $lvl * 2 * $mult : 0;
            $sell    = max(5, ($baseAtq + $baseDef + $baseHp + $baseVit) * 30 / 100);

            DB::table('item_templates')->insert([
                'zone_id'         => $zoneId,
                'name'            => ucfirst($t['slot']) . " de {$zoneSlug} (" . ucfirst(str_replace('_', ' ', $t['rarity'])) . ')',
                'description'     => 'Objet généré par l\'IA. Le Narrateur assure que c\'est intentionnel.',
                'rarity'          => $t['rarity'],
                'slot'            => $t['slot'],
                'element'         => $element,
                'base_atq'        => $baseAtq,
                'base_def'        => $baseDef,
                'base_hp'         => $baseHp,
                'base_vit'        => $baseVit,
                'base_cha'        => 0,
                'base_int'        => 0,
                'base_sell_value' => (int) $sell,
            ]);
        }
    }

    /**
     * Generate a unique slug ensuring no collision in zones or monsters tables.
     * Zones slug is varchar(30), monsters slug is varchar(50).
     */
    private function uniqueSlug(string $base, int $maxLen = 28): string
    {
        $slug      = substr($base, 0, $maxLen);
        $candidate = $slug;
        $counter   = 2;

        while (
            DB::table('zones')->where('slug', $candidate)->exists() ||
            DB::table('monsters')->where('slug', $candidate)->exists()
        ) {
            $suffix    = '_' . $counter;
            $candidate = substr($slug, 0, $maxLen - strlen($suffix)) . $suffix;
            $counter++;
        }

        return $candidate;
    }
}
