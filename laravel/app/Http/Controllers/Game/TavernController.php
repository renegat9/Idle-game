<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateHeroImage;
use App\Models\Hero;
use App\Models\HeroBuff;
use App\Models\Race;
use App\Models\GameClass;
use App\Models\Trait_;
use App\Models\TavernRecruit;
use App\Services\GeminiService;
use App\Services\SettingsService;
use App\Services\NarratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TavernController extends Controller
{
    // French hero name parts for generation
    private const FIRST_NAMES = [
        'Gruntak', 'Fizzle', 'Borak', 'Elwyn', 'Mira', 'Thorek',
        'Zara', 'Dunk', 'Pip', 'Korgar', 'Lyria', 'Snarg',
        'Bertrand', 'Guillemette', 'Raoul', 'Hildegarde', 'Eustache', 'Pélagie',
        'Gontran', 'Ygraine', 'Mouloud', 'Sigrid', 'Adalbert', 'Cunégonde',
    ];

    private const LAST_NAMES = [
        'le Vaillant', 'des Bas-Fonds', 'l\'Improbable', 'du Marais', 'à la Retraite',
        'le Mal Coiffé', 'Trois-Genoux', 'l\'Incompris', 'Casse-Tout', 'le Pas Doué',
        'de la Prairie', 'au Grand Cœur (Petit Budget)', 'le Présomptueux',
    ];

    // Probabilité qu'un recrutement soit légendaire (10%)
    private const LEGENDARY_CHANCE = 10;

    public function __construct(
        private readonly SettingsService $settings,
        private readonly NarratorService $narrator,
        private readonly GeminiService $gemini,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Purge expired recruits
        TavernRecruit::where('user_id', $user->id)
            ->where('is_hired', false)
            ->where('expires_at', '<', now())
            ->delete();

        // Generate recruits if fewer than max
        $maxOffers    = $this->settings->get('TAVERN_MAX_OFFERS', 3);
        $currentCount = TavernRecruit::where('user_id', $user->id)
            ->where('is_hired', false)
            ->count();

        if ($currentCount < $maxOffers) {
            $this->generateRecruits($user->id, $maxOffers - $currentCount);
        }

        $recruits = TavernRecruit::with(['race', 'gameClass', 'trait_'])
            ->where('user_id', $user->id)
            ->where('is_hired', false)
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn($r) => $this->recruitResponse($r));

        // Active debuffs on heroes (can be removed here)
        $heroDebuffs = [];
        foreach ($user->activeHeroes()->with('buffs')->get() as $hero) {
            $debuffs = $hero->buffs->where('is_debuff', true)->where('remaining_combats', '>', 0);
            if ($debuffs->isNotEmpty()) {
                $removalCost = $this->settings->get('QUEST_DEBUFF_REMOVE_COST', 100) * $hero->level;
                $heroDebuffs[] = [
                    'hero_id'      => $hero->id,
                    'hero_name'    => $hero->name,
                    'debuffs'      => $debuffs->map(fn($b) => [
                        'id'               => $b->id,
                        'source'           => $b->source,
                        'stat_affected'    => $b->stat_affected,
                        'modifier_percent' => $b->modifier_percent,
                        'remaining_combats'=> $b->remaining_combats,
                    ])->values(),
                    'removal_cost' => $removalCost,
                ];
            }
        }

        return response()->json([
            'recruits'    => $recruits->values(),
            'hero_debuffs'=> $heroDebuffs,
            'narrator_comment' => $this->narrator->getComment('tavern_visited', []),
        ]);
    }

    /**
     * Returns the current tavern music track.
     * Style is driven by query param or defaults to 'taverne'.
     * MusicFX is not publicly available — always returns static fallback.
     *
     * GET /api/tavern/music?style=victoire_epique
     */
    public function music(Request $request): JsonResponse
    {
        $validStyles = ['taverne', 'victoire_epique', 'defaite', 'exploration', 'boss', 'repos'];
        $style = $request->query('style', 'taverne');

        if (!in_array($style, $validStyles)) {
            $style = 'taverne';
        }

        $track = $this->gemini->generateTavernMusic($style);

        return response()->json([
            'style'     => $track['style'],
            'file_path' => $track['file_path'],
            'prompt'    => $track['prompt'],
        ]);
    }

    public function hire(Request $request, int $recruitId): JsonResponse
    {
        $user    = $request->user();
        $recruit = TavernRecruit::where('id', $recruitId)
            ->where('user_id', $user->id)
            ->where('is_hired', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$recruit) {
            return response()->json(['message' => 'Ce recrutement n\'est plus disponible.'], 404);
        }

        $maxSlots = $this->settings->get('HERO_MAX_SLOTS', 5);
        if ($user->heroes()->count() >= $maxSlots) {
            return response()->json([
                'message' => 'Équipe complète. Le Narrateur vous suggère de renvoyer quelqu\'un. Méchamment.',
            ], 422);
        }

        if ($user->gold < $recruit->hire_cost) {
            return response()->json([
                'message' => "Or insuffisant. Coût de recrutement : {$recruit->hire_cost} or.",
            ], 422);
        }

        $race  = $recruit->race;
        $class = $recruit->gameClass;
        $baseHp = max(1, $race->base_hp + $class->mod_hp);
        $slotIndex = $user->heroes()->max('slot_index') + 1;

        $hero = DB::transaction(function () use ($user, $recruit, $race, $class, $baseHp, $slotIndex) {
            $user->decrement('gold', $recruit->hire_cost);
            $recruit->update(['is_hired' => true]);

            return Hero::create([
                'user_id'          => $user->id,
                'race_id'          => $recruit->race_id,
                'class_id'         => $recruit->class_id,
                'trait_id'         => $recruit->trait_id,
                'name'             => $recruit->name,
                'level'            => 1,
                'xp'               => 0,
                'xp_to_next_level' => 100,
                'current_hp'       => $baseHp,
                'max_hp'           => $baseHp,
                'talent_points'    => 0,
                'slot_index'       => $slotIndex,
                'is_active'        => true,
                'image_path'       => $recruit->image_path,
            ]);
        });

        $hero->load(['race', 'gameClass', 'trait_']);

        return response()->json([
            'message'          => "{$hero->name} rejoint l\'équipe. Le Narrateur prend ses paris.",
            'hero_id'          => $hero->id,
            'gold_spent'       => $recruit->hire_cost,
            'narrator_comment' => $this->narrator->getComment('hero_created', ['hero_name' => $hero->name]),
        ], 201);
    }

    public function removeDebuff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hero_id'  => 'required|integer',
            'buff_id'  => 'required|integer',
        ]);

        $user = $request->user();
        $hero = Hero::where('id', $validated['hero_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$hero) {
            return response()->json(['message' => 'Héros introuvable.'], 404);
        }

        $buff = HeroBuff::where('id', $validated['buff_id'])
            ->where('hero_id', $hero->id)
            ->where('is_debuff', true)
            ->first();

        if (!$buff) {
            return response()->json(['message' => 'Debuff introuvable.'], 404);
        }

        $cost = $this->settings->get('QUEST_DEBUFF_REMOVE_COST', 100) * $hero->level;
        if ($user->gold < $cost) {
            return response()->json(['message' => "Or insuffisant. Coût de purification : {$cost} or."], 422);
        }

        DB::transaction(function () use ($user, $buff, $cost) {
            $user->decrement('gold', $cost);
            $buff->delete();
        });

        return response()->json([
            'message'    => 'Debuff retiré. Gérard a fait brûler des herbes. Ça sentait mauvais.',
            'gold_spent' => $cost,
        ]);
    }

    // ─── Internals ───────────────────────────────────────────────────────────

    private function generateRecruits(int $userId, int $count): void
    {
        $races   = Race::all();
        $classes = GameClass::all();
        $traits  = Trait_::all();
        $expires = now()->addHours(24);

        for ($i = 0; $i < $count; $i++) {
            $race  = $races->random();
            $class = $classes->random();
            $trait = $traits->random();
            $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
            $lastName  = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            $name      = "{$firstName} {$lastName}";

            $baseSlots  = DB::table('heroes')->where('user_id', $userId)->count();
            $isLegendary = rand(1, 100) <= self::LEGENDARY_CHANCE;
            $hireCost   = $isLegendary
                ? 500 + ($baseSlots * 500) + rand(0, 200)
                : 100 + ($baseSlots * 200) + rand(0, 50);

            $legendaryEpithet   = null;
            $legendaryBackstory = null;

            if ($isLegendary) {
                $legendary = $this->gemini->generateLegendaryHero($firstName, $class->name, $trait->name);
                $legendaryEpithet   = $legendary['epithet'];
                $legendaryBackstory = $legendary['backstory'];
            }

            $recruit = TavernRecruit::create([
                'user_id'             => $userId,
                'race_id'             => $race->id,
                'class_id'            => $class->id,
                'trait_id'            => $trait->id,
                'name'                => $name,
                'hire_cost'           => $hireCost,
                'is_hired'            => false,
                'expires_at'          => $expires,
                'is_legendary'        => $isLegendary,
                'legendary_epithet'   => $legendaryEpithet,
                'legendary_backstory' => $legendaryBackstory,
            ]);

            GenerateHeroImage::dispatch(
                $recruit->id,
                $race->name,
                $class->slug,
                $trait->slug,
                'tavern_recruits',
            );
        }
    }

    private function recruitResponse(TavernRecruit $r): array
    {
        return [
            'id'                  => $r->id,
            'name'                => $r->name,
            'hire_cost'           => $r->hire_cost,
            'expires_at'          => $r->expires_at,
            'is_legendary'        => (bool) $r->is_legendary,
            'legendary_epithet'   => $r->legendary_epithet,
            'legendary_backstory' => $r->legendary_backstory,
            'image_path'          => $r->image_path,
            'race'                => ['id' => $r->race->id, 'name' => $r->race->name, 'slug' => $r->race->slug],
            'class'               => ['id' => $r->gameClass->id, 'name' => $r->gameClass->name, 'role' => $r->gameClass->role, 'slug' => $r->gameClass->slug],
            'trait'               => ['id' => $r->trait_->id, 'name' => $r->trait_->name, 'description' => $r->trait_->description],
        ];
    }
}
