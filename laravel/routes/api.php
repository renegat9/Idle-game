<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Game\HeroController;
use App\Http\Controllers\Game\ExplorationController;
use App\Http\Controllers\Game\InventoryController;
use App\Http\Controllers\Game\DashboardController;
use App\Http\Controllers\Game\ZoneController;
use App\Http\Controllers\Game\QuestController;
use App\Http\Controllers\Game\CraftingController;
use App\Http\Controllers\Game\TavernController;
use App\Http\Controllers\Game\ShopController;
use App\Http\Controllers\Game\WorldBossController;
use App\Http\Controllers\Game\DungeonController;
use App\Http\Controllers\Game\TalentController;
use App\Http\Controllers\Game\ProfileController;
use App\Http\Controllers\Game\ReputationController;
use App\Http\Controllers\Game\MusicController;
use App\Http\Controllers\Game\SeasonalEventController;
use App\Http\Controllers\Game\ConsumableController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard & polling
    Route::get('/game/dashboard', [DashboardController::class, 'index']);
    Route::get('/game/poll', [DashboardController::class, 'poll']);

    // Heroes
    Route::get('/heroes', [HeroController::class, 'index']);
    Route::get('/heroes/synergies', [HeroController::class, 'synergies']);
    Route::post('/heroes', [HeroController::class, 'store']);
    Route::post('/heroes/{hero}/equip', [HeroController::class, 'equip']);
    Route::delete('/heroes/{hero}', [HeroController::class, 'dismiss']);

    // Talents
    Route::get('/heroes/{heroId}/talents', [TalentController::class, 'index']);
    Route::post('/heroes/{heroId}/talents/{talentId}/allocate', [TalentController::class, 'allocate']);
    Route::post('/heroes/{heroId}/talents/reset', [TalentController::class, 'reset']);

    // Exploration
    Route::get('/exploration/status', [ExplorationController::class, 'status']);
    Route::post('/exploration/start', [ExplorationController::class, 'start']);
    Route::post('/exploration/collect', [ExplorationController::class, 'collect']);

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/sell', [InventoryController::class, 'sell']);

    // Zones
    Route::get('/zones', [ZoneController::class, 'index']);

    // Quests
    Route::get('/quests', [QuestController::class, 'index']);
    Route::get('/quests/daily', [QuestController::class, 'daily']);
    Route::post('/quests/{questId}/start', [QuestController::class, 'start']);
    Route::post('/user-quests/{userQuestId}/choose', [QuestController::class, 'choose']);

    // Crafting — Forge de Gérard
    Route::get('/crafting', [CraftingController::class, 'index']);
    Route::post('/crafting/fuse', [CraftingController::class, 'fuse']);
    Route::post('/crafting/dismantle', [CraftingController::class, 'dismantle']);
    Route::post('/crafting/craft', [CraftingController::class, 'craft']);
    Route::get('/crafting/enchantments', [CraftingController::class, 'enchantments']);
    Route::post('/crafting/enchant', [CraftingController::class, 'enchant']);

    // Taverne
    Route::get('/tavern', [TavernController::class, 'index']);
    Route::get('/tavern/music', [TavernController::class, 'music']);
    Route::post('/tavern/hire/{recruitId}', [TavernController::class, 'hire']);
    Route::post('/tavern/remove-debuff', [TavernController::class, 'removeDebuff']);

    // Boutique
    Route::get('/shop', [ShopController::class, 'index']);
    Route::post('/shop/buy', [ShopController::class, 'buy']);

    // Donjon
    Route::get('/dungeon', [DungeonController::class, 'status']);
    Route::post('/dungeon/start', [DungeonController::class, 'start']);
    Route::post('/dungeon/{dungeonId}/enter', [DungeonController::class, 'enter']);
    Route::post('/dungeon/{dungeonId}/abandon', [DungeonController::class, 'abandon']);

    // Boss mondial
    Route::get('/world-boss', [WorldBossController::class, 'status']);
    Route::post('/world-boss/attack', [WorldBossController::class, 'attack']);
    Route::get('/world-boss/leaderboard', [WorldBossController::class, 'leaderboard']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    // Consommables
    Route::get('/consumables', [ConsumableController::class, 'index']);
    Route::get('/consumables/catalog', [ConsumableController::class, 'catalog']);
    Route::post('/consumables/{slug}/use', [ConsumableController::class, 'use']);

    // Réputation par zone
    Route::get('/reputation', [ReputationController::class, 'index']);
    Route::get('/reputation/{zoneId}', [ReputationController::class, 'show']);

    // Ambiance musicale dynamique
    Route::get('/music/current', [MusicController::class, 'current']);

    // Événements saisonniers
    Route::get('/events/current', [SeasonalEventController::class, 'current']);
    Route::get('/events', [SeasonalEventController::class, 'index']);

    // Reference data
    Route::prefix('reference')->group(function () {
        Route::get('/races', fn() => response()->json(['races' => \App\Models\Race::all()]));
        Route::get('/classes', fn() => response()->json(['classes' => \App\Models\GameClass::all()]));
        Route::get('/traits', fn() => response()->json(['traits' => \App\Models\Trait_::all()]));
    });
});
