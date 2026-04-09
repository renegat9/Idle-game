<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, function () {
            return new SettingsService();
        });
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole() || $this->app->runningUnitTests()) {
            try {
                $this->app->make(SettingsService::class)->preloadAll();
            } catch (\Exception $e) {
                // DB pas encore disponible (ex: pendant les migrations)
            }
        }
    }
}
