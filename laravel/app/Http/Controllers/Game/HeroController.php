<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\GameClass;
use App\Models\Hero;
use App\Models\Item;
use App\Models\Race;
use App\Models\Trait_;
use App\Services\NarratorService;
use App\Services\SettingsService;
use App\Services\TraitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeroController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly NarratorService $narrator,
        private readonly TraitService $traitService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $heroes = $request->user()
            ->heroes()
            ->with(['race', 'gameClass', 'trait_', 'equippedItems.effects', 'buffs'])
            ->orderBy('slot_index')
            ->get()
            ->map(fn($hero) => $this->heroResponse($hero));

        return response()->json(['heroes' => $heroes]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $maxSlots = $this->settings->get('HERO_MAX_SLOTS', 5);

        if ($user->heroes()->count() >= $maxSlots) {
            return response()->json([
                'message' => 'Équipe complète. Le Narrateur vous suggère de renvoyer quelqu\'un. Méchamment.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'race_id' => 'required|integer|exists:races,id',
            'class_id' => 'required|integer|exists:classes,id',
            'trait_id' => 'required|integer|exists:traits,id',
        ], [
            'name.required' => 'Le nom du héros est obligatoire.',
            'race_id.exists' => 'Cette race n\'existe pas.',
            'class_id.exists' => 'Cette classe n\'existe pas.',
            'trait_id.exists' => 'Ce trait n\'existe pas.',
        ]);

        $race = Race::findOrFail($validated['race_id']);
        $class = GameClass::findOrFail($validated['class_id']);

        // Calculer HP initial
        $baseHp = max(1, $race->base_hp + $class->mod_hp);

        $slotIndex = $user->heroes()->max('slot_index') + 1;

        $hero = DB::transaction(function () use ($user, $validated, $race, $class, $baseHp, $slotIndex) {
            return Hero::create([
                'user_id' => $user->id,
                'race_id' => $validated['race_id'],
                'class_id' => $validated['class_id'],
                'trait_id' => $validated['trait_id'],
                'name' => $validated['name'],
                'level' => 1,
                'xp' => 0,
                'xp_to_next_level' => 100,
                'current_hp' => $baseHp,
                'max_hp' => $baseHp,
                'talent_points' => 0,
                'slot_index' => $slotIndex,
                'is_active' => true,
            ]);
        });

        $hero->load(['race', 'gameClass', 'trait_']);
        $narratorComment = $this->narrator->getComment('hero_created', ['hero_name' => $hero->name]);

        return response()->json([
            'message' => 'Héros créé. ' . $narratorComment,
            'hero' => $this->heroResponse($hero),
            'narrator_comment' => $narratorComment,
        ], 201);
    }

    public function synergies(Request $request): JsonResponse
    {
        $heroes = $request->user()
            ->heroes()
            ->with(['gameClass', 'trait_'])
            ->where('is_active', true)
            ->get();

        $mods = $this->traitService->getTeamSynergyModifiers($heroes);

        return response()->json([
            'active_synergies' => $mods['active_synergies'],
            'team_bonuses' => [
                'loot_bonus_pct'  => $mods['loot_bonus_pct'],
                'atq_bonus_pct'   => $mods['atq_bonus_pct'],
                'def_bonus_pct'   => $mods['def_bonus_pct'],
            ],
        ]);
    }

    public function dismiss(Request $request, Hero $hero): JsonResponse
    {
        $user = $request->user();

        if ($hero->user_id !== $user->id) {
            return response()->json(['message' => 'Ce héros ne vous appartient pas.'], 403);
        }

        if ($user->heroes()->count() <= 1) {
            return response()->json([
                'message' => 'Vous ne pouvez pas renvoyer votre dernier héros. Qui ferait le travail ?',
            ], 422);
        }

        $heroName = $hero->name;

        DB::transaction(function () use ($hero) {
            // Déséquiper tous ses objets
            Item::where('equipped_by_hero_id', $hero->id)
                ->update(['equipped_by_hero_id' => null]);
            // Supprimer le héros
            $hero->delete();
        });

        $narratorComment = $this->narrator->getComment('hero_dismissed', ['hero_name' => $heroName]);

        return response()->json([
            'message' => $heroName . ' a été renvoyé. ' . $narratorComment,
            'narrator_comment' => $narratorComment,
        ]);
    }

    public function equip(Request $request, Hero $hero): JsonResponse
    {
        $user = $request->user();

        if ($hero->user_id !== $user->id) {
            return response()->json(['message' => 'Ce héros ne vous appartient pas.'], 403);
        }

        $validated = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
        ]);

        $item = Item::where('id', $validated['item_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Objet introuvable dans votre inventaire.'], 404);
        }

        // Vérifier restriction de classe
        $class = $hero->gameClass;
        if ($item->slot === 'arme' && !empty($class->weapon_types)) {
            // On laisse passer pour l'instant — restriction à affiner en Phase 2
        }
        if ($item->slot === 'armure' && !empty($class->armor_types)) {
            // Idem
        }

        DB::transaction(function () use ($hero, $item) {
            // Déséquiper l'objet actuel dans ce slot
            Item::where('equipped_by_hero_id', $hero->id)
                ->where('slot', $item->slot)
                ->update(['equipped_by_hero_id' => null]);

            // Équiper le nouvel objet
            $item->equipped_by_hero_id = $hero->id;
            $item->save();
        });

        $hero->load(['race', 'gameClass', 'trait_', 'equippedItems']);

        return response()->json([
            'message' => $item->name . ' équipé sur ' . $hero->name . '.',
            'hero' => $this->heroResponse($hero),
        ]);
    }

    private function heroResponse(Hero $hero): array
    {
        $stats = $hero->computedStats();
        $trait = $hero->trait_;

        return [
            'id' => $hero->id,
            'name' => $hero->name,
            'level' => $hero->level,
            'xp' => $hero->xp,
            'xp_to_next_level' => $hero->xp_to_next_level,
            'slot_index' => $hero->slot_index,
            'is_active' => $hero->is_active,
            'deaths' => $hero->deaths,
            'talent_points' => $hero->talent_points,
            'race' => [
                'id' => $hero->race->id,
                'name' => $hero->race->name,
                'slug' => $hero->race->slug,
                'passive_bonus_description' => $hero->race->passive_bonus_description,
            ],
            'class' => [
                'id' => $hero->gameClass->id,
                'name' => $hero->gameClass->name,
                'slug' => $hero->gameClass->slug,
                'role' => $hero->gameClass->role,
                'key_skill_name' => $hero->gameClass->key_skill_name,
            ],
            'trait' => $trait ? [
                'id' => $trait->id,
                'name' => $trait->name,
                'slug' => $trait->slug,
                'description' => $trait->description,
                'flavor_text' => $trait->flavor_text,
            ] : null,
            'computed_stats' => $stats,
            'equipped_items' => $hero->equippedItems->map(fn($item) => $this->itemResponse($item))->values(),
        ];
    }

    private function itemResponse(Item $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'rarity' => $item->rarity,
            'slot' => $item->slot,
            'element' => $item->element,
            'item_level' => $item->item_level,
            'atq' => $item->atq, 'def' => $item->def, 'hp' => $item->hp,
            'vit' => $item->vit, 'cha' => $item->cha, 'int' => $item->int,
            'sell_value' => $item->sell_value,
        ];
    }
}
