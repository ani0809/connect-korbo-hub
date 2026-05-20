<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (! file_exists(storage_path('installed.lock'))) return;
        try {
            $settings = Cache::rememberForever('app_settings', fn () => Setting::query()->where('autoload', true)->pluck('value', 'key')->toArray());
            foreach ($settings as $key => $value) config(["settings.{$key}" => $value]);
            if (! empty($settings['timezone'])) { config(['app.timezone' => $settings['timezone']]); date_default_timezone_set((string) $settings['timezone']); }
            if (! empty($settings['locale'])) app()->setLocale((string) $settings['locale']);
            view()->share('dynamicCssVars', [
                '--color-primary' => (string) ($settings['primary_color'] ?? '#2563eb'),
                '--color-secondary' => (string) ($settings['secondary_color'] ?? '#1e293b'),
                '--color-accent' => (string) ($settings['accent_color'] ?? '#f59e0b'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Settings boot failed: '.$e->getMessage());
        }
    }
}
