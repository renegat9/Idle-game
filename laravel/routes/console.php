<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Cron Schedule ────────────────────────────────────────────────────────────
// Run via: php artisan schedule:run (every minute via server cron)
//
// Server crontab entry:
//   * * * * * cd /path/to/laravel && php artisan schedule:run >> /dev/null 2>&1

// Daily at midnight: purge old logs
Schedule::command('logs:cleanup')->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Daily at 00:05: generate daily quest pool via Gemini
Schedule::command('quests:generate')->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();

// Every 6 hours: purge expired shop items
Schedule::command('shop:refresh')->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();

// Every 3 days at 12:00: spawn a world boss if none is active
Schedule::command('world-boss:spawn')->cron('0 12 */3 * *')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly on Monday at 02:00: generate a new procedural zone (zone 9+)
Schedule::command('zones:generate')->weekly()->mondays()->at('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Every 2 hours: NPCs auto-attack the world boss (simulates activity)
Schedule::command('world-boss:auto-attack')->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground();

// Every 5 minutes: process queued jobs (GenerateLootImage, GenerateHeroImage, etc.)
Schedule::command('queue:work --stop-when-empty --tries=2 --timeout=90')->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
