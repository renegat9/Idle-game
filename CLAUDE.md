# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Le Donjon des Incompétents** — A humorous idle RPG web game where incompetent heroes explore dungeons, do absurd quests, and craft ridiculous items, narrated by a sarcastic AI narrator. Inspired by Kaamelott, Le Donjon de Naheulbeuk, and Munchkin.

Read ALL design documents before coding. They contain exact formulas, parameterizable constants, system interactions, and edge cases. Recommended order: `GDD.md` → `COMBAT_SYSTEM.md` → `TALENT_TREES.md` → `TRAITS_SYSTEM.md` → `LOOT_CRAFTING.md` → `BESTIARY.md` → `QUESTS_EFFECTS.md` → `ECONOMY.md` → `DATABASE.md`.

## Stack

- **Backend:** PHP 8.2+ / Laravel on cPanel (no Docker, no Redis, no WebSockets)
- **Frontend:** React + TypeScript + Vite (static build served by Apache)
- **Database:** MariaDB (34 tables)
- **Cache:** Laravel File/DB Cache
- **Auth:** Laravel Sanctum — single session (delete all tokens on login, create one new token)
- **AI:** Gemini API (text, Imagen, MusicFX) with mandatory static fallbacks
- **Real-time:** AJAX polling every 10–30s (no WebSockets)
- **Async jobs:** Laravel cron (`php artisan schedule:run` every minute)
- **Offline calc:** Computed at reconnection (not a permanent worker)

## Server Environment (cPanel Production)

- **PHP binary:** `/opt/cpanel/ea-php83/root/usr/bin/php`
- Always use the full path when running `php artisan` commands on the server.

Example:
```bash
/opt/cpanel/ea-php83/root/usr/bin/php /home/techfg/public_html/donjon/laravel/artisan optimize:clear
```

## Commands

```bash
# Setup
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed

# Development
php artisan serve
php artisan schedule:run       # Run scheduled jobs
php artisan queue:work         # Process job queue

# Individual seeders
php artisan db:seed --class=GameSettingsSeeder
php artisan db:seed --class=RaceSeeder
php artisan db:seed --class=ClassSeeder
php artisan db:seed --class=TalentSeeder
php artisan db:seed --class=MonsterSeeder

# Maintenance
php artisan logs:cleanup
php artisan shop:refresh
php artisan quests:generate
php artisan world-boss:spawn
```

## Architecture

### Backend (`laravel/`)

**Controllers** (`app/Http/Controllers/Game/`): One controller per game system — `CombatController`, `ExplorationController`, `HeroController`, `QuestController`, `CraftingController`, `InventoryController`, `ShopController`, `DungeonController`, `WorldBossController`, `TavernController`, `NarratorController`.

**Services** (`app/Services/`): Business logic lives here, not in controllers.
- `CombatService` — Core combat engine implementing exact formulas from `COMBAT_SYSTEM.md`
- `IdleService` — Offline progression using power-ratio simulation (NOT turn-by-turn)
- `LootService` — Loot generation by rarity
- `CraftingService` — Fusion, dismantling, enchantment
- `QuestService` — Quest choice resolution and stat tests
- `TraitService` — Negative trait triggering and resolution
- `NarratorService` — AI comment generation with cache
- `GeminiService` — Gemini API client (text, image, music) with fallbacks
- `SettingsService` — Reads and caches `game_settings` table; ALL gameplay constants must go through this service

**Jobs** (`app/Jobs/`): Async Gemini calls, daily quest pre-generation, shop refresh, boss spawning, log cleanup.

**Models** (`app/Models/`): `GameClass.php` (not `Class` — reserved in PHP), `Trait_.php` (not `Trait` — reserved in PHP).

### Frontend (`frontend/` or `dist/` after build)

React + TypeScript + Vite. 9 main screens: Dashboard, Équipe, Carte du monde, Donjon, Quêtes, Forge de Gérard, Taverne, Boss mondial, Profil.

Communicates with Laravel API via REST JSON + AJAX polling (no WebSockets).

## Critical Rules

### Integer-only arithmetic
**No floats anywhere.** All formulas use integer division (floor). Percentages applied as `stat × bonus / 100`. This applies to stats, damage, gold, HP, percentages everywhere.

### All constants via `game_settings`
Never hardcode gameplay values. Every constant is in the `game_settings` table (~120 entries) and accessed through `SettingsService`. Changing a DB value changes game behavior without redeployment.

### Combat formula order
Initiative → trait checks (Couard flees? Narcoleptique sleeps?) → action → talent modifiers. For traits: status effects → trait roll → Branche du Défaut talents → action. See `TRAITS_SYSTEM.md` section 3.

### Offline calculation
Use the power-ratio simplified simulation from `COMBAT_SYSTEM.md` section 6. Do NOT simulate turn-by-turn — performance critical. Cap at 12 hours.

### Gemini output validation
Always validate structure, allowed effect IDs, and max debuff durations before injecting AI output into the game. See `QUESTS_EFFECTS.md` section 7.2. Track API calls in `ai_generation_log`. Respect `AI_DAILY_BUDGET_LIMIT` from `game_settings`.

### AI fallbacks are mandatory
Every Gemini call must have a static fallback: pre-written humor templates for text, placeholder images by slot+rarity, royalty-free music library. The game must function without AI.

### Log purging
`combat_log`, `economy_log`, and `idle_event_log` must be purged regularly via `CleanupLogs` job. Without purging, the database will grow unbounded.

## Database Conventions

- All IDs: `BIGINT UNSIGNED AUTO_INCREMENT`
- Dates: `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
- No `FLOAT`/`DOUBLE` — everything in `INT`
- FK with `ON DELETE CASCADE` unless noted otherwise
- JSON columns for flexible data (effects, mechanics, compositions)
- Full schema with indexes in `DATABASE.md`
