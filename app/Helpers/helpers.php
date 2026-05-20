<?php

use App\Models\Addon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        if (method_exists(Setting::class, 'get')) return Setting::get($key, $default);
        if (method_exists(Setting::class, 'getValue')) return Setting::getValue($key, $default);
        return $default;
    }
}

if (! function_exists('currency_format')) {
    function currency_format(float $amount, ?string $symbol = null): string
    {
        $sym = $symbol ?? (string) setting('currency_symbol', config('shop.currency_symbol', '$'));

        return $sym.number_format($amount, 2, '.', ',');
    }
}

if (! function_exists('upload_file')) {
    function upload_file(object $file, string $folder, ?int $w = null, ?int $h = null): string
    {
        if (! $file instanceof UploadedFile) {
            throw new InvalidArgumentException('The given file must be an instance of UploadedFile.');
        }

        return $file->store($folder, 'public');
    }
}

if (! function_exists('active_theme')) {
    function active_theme(): string { return (string) setting('active_theme', 'default'); }
}

if (! function_exists('builder_config')) {
    function builder_config(string $section): array
    {
        $json = (string) setting($section.'_config_json', '{}');
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}

if (! function_exists('is_addon_active')) {
    function is_addon_active(string $slug): bool
    {
        return class_exists(Addon::class) && Addon::query()->where('slug', $slug)->where('is_active', true)->exists();
    }
}

if (! function_exists('shop_setting')) {
    function shop_setting(string $key, mixed $default = null): mixed { return setting($key, $default); }
}

if (! function_exists('generate_order_number')) {
    function generate_order_number(): string
    {
        $sequence = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        return '#ORD-'.now()->format('Y').'-'.$sequence;
    }
}

if (! function_exists('format_date')) {
    function format_date(string $date, string $format = 'd M Y'): string
    {
        return Carbon::parse($date, config('app.timezone'))->format($format);
    }
}

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10): void { app('hooks')->addAction($hook, $callback, $priority); }
}
if (! function_exists('do_action')) {
    function do_action(string $hook, mixed ...$args): void { app('hooks')->doAction($hook, ...$args); }
}
if (! function_exists('add_filter')) {
    function add_filter(string $filter, callable $callback, int $priority = 10): void { app('hooks')->addFilter($filter, $callback, $priority); }
}
if (! function_exists('apply_filters')) {
    function apply_filters(string $filter, mixed $value, mixed ...$args): mixed { return app('hooks')->applyFilters($filter, $value, ...$args); }
}

if (! function_exists('log_activity')) {
    function log_activity(
        string $action,
        ?\Illuminate\Database\Eloquent\Model $subject = null,
        array $old = [],
        array $new = [],
    ): void {
        \App\Services\ActivityLogger::log($action, $subject, $old, $new);
    }
}

if (! function_exists('feature')) {
    function feature(string $name): bool
    {
        return \App\Services\FeatureService::isEnabled($name);
    }
}

if (! function_exists('current_currency_symbol')) {
    function current_currency_symbol(): string
    {
        return (string) setting('currency_symbol', config('shop.currency_symbol', '$'));
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('avatar_url')) {
    function avatar_url(User $user): string
    {
        if ($user->avatar) {
            return asset('storage/'.$user->avatar);
        }

        return 'https://www.gravatar.com/avatar/'.md5(strtolower((string) $user->email)).'?d=mp&s=128';
    }
}

if (! function_exists('product_card_config')) {
    function product_card_config(): array
    {
        try {
            return app(\App\Services\BuilderService::class)->getActiveProductCard();
        } catch (\Throwable) {
            return [];
        }
    }
}

if (! function_exists('get_page_layout')) {
    function get_page_layout(): array
    {
        return builder_config('homepage');
    }
}

if (! function_exists('truncate_html')) {
    function truncate_html(string $html, int $limit): string
    {
        $plain = strip_tags($html);
        if (strlen($plain) <= $limit) {
            return $plain;
        }

        return substr($plain, 0, $limit).'…';
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return in_array(app()->getLocale(), config('app.rtl_locales', ['ar', 'he', 'fa']), true);
    }
}

if (! function_exists('price_range')) {
    /**
     * @return array{min: float, max: float}
     */
    function price_range(Product $product): array
    {
        $product->loadMissing('variants');
        $prices = $product->variants->map(function ($v) {
            $p = (float) $v->price;
            $s = $v->sale_price ? (float) $v->sale_price : null;

            return ($s !== null && $s > 0) ? $s : $p;
        });

        return [
            'min' => (float) ($prices->min() ?? 0),
            'max' => (float) ($prices->max() ?? 0),
        ];
    }
}

if (! function_exists('format_number')) {
    function format_number(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals, '.', ',');
    }
}

if (! function_exists('percentage_change')) {
    function percentage_change(float $old, float $new): float
    {
        if (abs($old) < 0.00001) {
            return $new > 0 ? 100.0 : 0.0;
        }

        return round((($new - $old) / $old) * 100, 2);
    }
}
