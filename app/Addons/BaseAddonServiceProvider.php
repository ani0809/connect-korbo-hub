<?php

namespace App\Addons;

use App\Models\Addon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class BaseAddonServiceProvider extends ServiceProvider
{
    protected string $addonId;
    protected string $addonPath;

    public function register(): void
    {
        $this->addonPath = base_path("addons/{$this->addonId}");
        if (file_exists("{$this->addonPath}/config.php")) {
            $this->mergeConfigFrom("{$this->addonPath}/config.php", $this->addonId);
        }
    }

    public function boot(): void
    {
        if (! $this->isActive()) return;
        $this->loadViews();
        $this->loadTranslations();
        $this->registerRoutes();
        $this->registerAssets();
        $this->registerMenuItems();
        $this->registerHooks();
        $this->bootAddon();
    }

    abstract protected function bootAddon(): void;

    protected function isActive(): bool
    {
        return Cache::remember("addon_active_{$this->addonId}", 3600, fn () => Addon::query()->where('slug', $this->addonId)->where('is_active', true)->exists());
    }

    protected function loadViews(): void
    {
        $viewPath = "{$this->addonPath}/resources/views";
        if (is_dir($viewPath)) $this->loadViewsFrom($viewPath, $this->addonId);
    }

    protected function loadTranslations(): void
    {
        $langPath = "{$this->addonPath}/resources/lang";
        if (is_dir($langPath)) $this->loadTranslationsFrom($langPath, $this->addonId);
    }

    protected function registerRoutes(): void
    {
        $routeFile = "{$this->addonPath}/routes/web.php";
        if (file_exists($routeFile)) {
            Route::middleware(['web', 'installed'])->group($routeFile);
        }
        $apiFile = "{$this->addonPath}/routes/api.php";
        if (file_exists($apiFile)) {
            Route::middleware(['api'])->prefix('api')->group($apiFile);
        }
    }

    protected function registerAssets(): void
    {
        $publicPath = "{$this->addonPath}/resources/public";
        if (is_dir($publicPath)) {
            $this->publishes([$publicPath => public_path("addons/{$this->addonId}")], 'addon-assets');
        }
    }

    protected function registerMenuItems(): void
    {
        $addonJson = $this->getAddonJson();
        if (!isset($addonJson['admin_menu'])) return;
        if (app()->bound('addon.menu')) {
            app('addon.menu')->register($this->addonId, $addonJson['admin_menu']);
        }
    }

    protected function registerHooks(): void {}

    protected function getAddonJson(): array
    {
        static $cache = [];
        if (!isset($cache[$this->addonId])) {
            $cache[$this->addonId] = json_decode((string) @file_get_contents("{$this->addonPath}/addon.json"), true) ?? [];
        }
        return $cache[$this->addonId];
    }
}
