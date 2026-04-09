<?php

namespace App\Services;

use App\Models\GameSetting;

class SettingsService
{
    private array $cache = [];

    public function preloadAll(): void
    {
        GameSetting::all()->each(function ($setting) {
            $this->cache[$setting->setting_key] = (int) $setting->setting_value;
        });
    }

    public function get(string $key, int $default = 0): int
    {
        if (!isset($this->cache[$key])) {
            $value = GameSetting::where('setting_key', $key)->value('setting_value');
            $this->cache[$key] = $value !== null ? (int) $value : $default;
        }

        return $this->cache[$key];
    }

    public function set(string $key, int $value): void
    {
        GameSetting::updateOrInsert(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
        $this->cache[$key] = $value;
    }

    public function flush(): void
    {
        $this->cache = [];
    }
}
