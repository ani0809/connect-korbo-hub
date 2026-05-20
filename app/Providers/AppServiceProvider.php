<?php

namespace App\Providers;

use App\Addons\AddonMenuManager;
use App\Addons\HookManager;
use App\Models\Addon;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Services\BuilderService;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\CartService;
use App\Services\ClubPointsService;
use App\Services\WalletService;
use App\Services\CommissionService;
use App\Services\ImageService;
use App\Services\LicenseService;
use App\Services\NotificationService;
use App\Services\OrderPlacementService;
use App\Services\PushNotificationService;
use App\Services\ReportService;
use App\Services\SeoService;
use App\Services\ShippingService;
use App\Services\SmsService;
use App\View\Composers\GlobalComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use DateTimeInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartService::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(ClubPointsService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(BuilderService::class);
        $this->app->singleton(LicenseService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(PushNotificationService::class);
        $this->app->singleton(SeoService::class);
        $this->app->singleton(CommissionService::class);
        $this->app->singleton(OrderPlacementService::class);
        $this->app->singleton(ShippingService::class);
        $this->app->singleton(ImageService::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton('hooks', fn () => new HookManager());
        $this->app->singleton('addon.menu', fn () => new AddonMenuManager());
    }

    public function boot(): void
    {
        if (config('database.default') === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            if (is_string($path) && $path !== '' && ! is_file($path)) {
                $dir = dirname($path);
                if (! is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                if (is_dir($dir) && ! is_file($path)) {
                    @touch($path);
                }
            }
        }

        RateLimiter::for('api', fn ($request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('login', fn ($request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('checkout', fn ($request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('search', fn ($request) => Limit::perMinute(30)->by($request->ip()));

        View::composer('*', GlobalComposer::class);
        Order::observe(OrderObserver::class);
        $this->loadAddons();
        $this->registerBladeDirectives();
        $this->registerMacros();
        $this->registerDateFormatting();
        URL::forceScheme(config('app.env') === 'production' ? 'https' : 'http');
    }

    private function loadAddons(): void
    {
        if (! file_exists(storage_path('installed.lock'))) return;
        try {
            $activeAddons = Cache::remember('active_addon_slugs', 3600, fn () => Addon::query()->where('is_active', true)->pluck('slug'));
            foreach ($activeAddons as $slug) {
                $providerFile = base_path("addons/{$slug}/AddonServiceProvider.php");
                if (! file_exists($providerFile)) continue;
                require_once $providerFile;
                $providerClass = "Addons\\{$slug}\\AddonServiceProvider";
                if (class_exists($providerClass)) $this->app->register($providerClass);
            }
        } catch (\Throwable $e) {
            Log::error('Addon load failed: '.$e->getMessage());
        }
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('setting', fn ($key) => "<?php echo setting({$key}); ?>");
        Blade::directive('currency', fn ($amount) => "<?php echo currency_format({$amount}); ?>");
        Blade::directive('addon', fn ($slug) => "<?php if(is_addon_active({$slug})): ?>");
        Blade::directive('endaddon', fn () => '<?php endif; ?>');

        Blade::if('feature', fn (string $feature): bool => \App\Services\FeatureService::isEnabled($feature));
    }

    private function registerMacros(): void
    {
        Str::macro('money', fn ($amount, $currency = null) => currency_format($amount));
    }

    private function registerDateFormatting(): void
    {
        $formatter = static fn (DateTimeInterface $date): string => Carbon::instance(
            $date instanceof Carbon ? $date : Carbon::parse($date->format(DateTimeInterface::ATOM))
        )->format('j/n/Y g:i A');

        Blade::stringable(
            fn (CarbonInterface $date): string => $formatter($date)
        );

        Blade::stringable(
            fn (DateTimeInterface $date): string => $formatter($date)
        );

        Blade::directive('datetime', fn ($value) => "<?php echo ({$value}) instanceof \\DateTimeInterface ? \\Carbon\\Carbon::parse({$value})->format('j/n/Y g:i A') : {$value}; ?>");

        Carbon::serializeUsing(
            static fn (CarbonInterface $date): string => $date->format('j/n/Y g:i A')
        );
    }
}
