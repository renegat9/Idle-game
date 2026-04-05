<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Game\HeroController;
use App\Http\Controllers\Game\ExplorationController;
use App\Http\Controllers\Game\InventoryController;
use App\Http\Controllers\Game\DashboardController;
use App\Http\Controllers\Game\ZoneController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/game/dashboard', [DashboardController::class, 'index']);
    Route::get('/game/poll', [DashboardController::class, 'poll']);

    Route::get('/heroes', [HeroController::class, 'index']);
    Route::post('/heroes', [HeroController::class, 'store']);
    Route::post('/heroes/{hero}/equip', [HeroController::class, 'equip']);

    Route::get('/exploration/status', [ExplorationController::class, 'status']);
    Route::post('/exploration/start', [ExplorationController::class, 'start']);
    Route::post('/exploration/collect', [ExplorationController::class, 'collect']);

    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/sell', [InventoryController::class, 'sell']);

    Route::get('/zones', [ZoneController::class, 'index']);

    // Données de référence (races, classes, traits)
    Route::prefix('reference')->group(function () {
        Route::get('/races', fn() => response()->json(['races' => \App\Models\Race::all()]));
        Route::get('/classes', fn() => response()->json(['classes' => \App\Models\GameClass::all()]));
        Route::get('/traits', fn() => response()->json(['traits' => \App\Models\Trait_::all()]));
    });
});
