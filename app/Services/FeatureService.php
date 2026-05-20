<?php

namespace App\Services;

use App\Models\Setting;

class FeatureService
{
    /** @var array<string, bool> */
    private static array $features = [];

    private static bool $loaded = false;

    public static function isEnabled(string $feature): bool
    {
        if (! static::$loaded) {
            static::load();
        }

        return static::$features[$feature] ?? false;
    }

    private static function load(): void
    {
        static::$features = [
            'multi_vendor' => (bool) setting('feature_multi_vendor', true),
            'wishlist' => (bool) setting('feature_wishlist', true),
            'compare' => (bool) setting('feature_compare', true),
            'quick_view' => (bool) setting('feature_quick_view', true),
            'reviews' => (bool) setting('feature_reviews', true),
            'qna' => (bool) setting('feature_qna', true),
            'club_points' => (bool) setting('feature_club_points', true),
            'wallet' => (bool) setting('feature_wallet', true),
            'digital_products' => (bool) setting('feature_digital', false),
            'classified_products' => (bool) setting('feature_classified', false),
            'auction' => (bool) setting('feature_auction', false),
            'social_login' => (bool) setting('feature_social_login', true),
            'guest_checkout' => (bool) setting('feature_guest_checkout', true),
            'price_tracker' => (bool) setting('feature_price_tracker', true),
            'waitlist' => (bool) setting('feature_waitlist', true),
            'abandoned_cart' => (bool) setting('feature_abandoned_cart', false),
            'blog' => (bool) setting('feature_blog', true),
            'push_notifications' => (bool) setting('feature_push_notifications', false),
            'pwa' => (bool) setting('feature_pwa', false),
            'dark_mode' => (bool) setting('feature_dark_mode', true),
            'rtl' => (bool) setting('feature_rtl', false),
            'customer_chat' => (bool) setting('feature_customer_chat', false),
            'whatsapp_order' => (bool) setting('feature_whatsapp_order', false),
        ];
        static::$loaded = true;
    }

    /** @return array<string, bool> */
    public static function all(): array
    {
        if (! static::$loaded) {
            static::load();
        }

        return static::$features;
    }

    public static function reload(): void
    {
        static::$loaded = false;
        static::$features = [];
    }
}
