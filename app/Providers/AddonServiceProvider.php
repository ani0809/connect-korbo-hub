<?php

namespace App\Providers;

use App\Models\Addon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $addonsDir = base_path('addons');
        if (! is_dir($addonsDir)) return;

        $active = Addon::query()->where('is_active', true)->pluck('slug')->flip();
        foreach (glob($addonsDir.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $slug = basename($dir);
            if (!isset($active[$slug])) continue;
            $providerFile = $dir.'/AddonServiceProvider.php';
            if (!file_exists($providerFile)) continue;
            try {
                require_once $providerFile;
                $class = collect(get_declared_classes())->first(fn ($c) => str_ends_with($c, 'AddonServiceProvider') && str_contains((new \ReflectionClass($c))->getFileName() ?: '', $providerFile));
                if ($class) $this->app->register($class);
            } catch (\Throwable $e) {
                Log::error('Addon bootstrap failed', ['addon' => $slug, 'error' => $e->getMessage()]);
            }
        }
    }
}
