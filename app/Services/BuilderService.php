<?php

namespace App\Services;

use App\Models\Setting;

class BuilderService
{
    public function getHeaderConfig(): array
    {
        $raw = Setting::get('builder__header_config_json', null) ?? Setting::get('header_config_json', '{}');
        $config = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return $this->validateConfig('header', $config) ? $config : $this->getPreset('header', 'preset-1');
    }

    public function getFooterConfig(): array
    {
        $raw = Setting::get('builder__footer_config_json', null) ?? Setting::get('footer_config_json', '{}');
        $config = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return $this->validateConfig('footer', $config) ? $config : $this->getPreset('footer', 'preset-1');
    }

    public function getHomepageSections(): array
    {
        $raw = Setting::get('builder__homepage_sections_json', null) ?? Setting::get('homepage_sections_json', '[]');
        $decoded = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return $decoded['sections'] ?? $decoded;
    }

    public function getHomepageConfig(): array
    {
        $sections = $this->getHomepageSections();

        return [
            'version' => '1.0',
            'sections' => is_array($sections) ? $sections : [],
        ];
    }

    public function getProductCards(): array
    {
        $raw = Setting::get('builder__product_card_configs_json', null) ?? Setting::get('product_card_configs_json', '[]');

        return is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
    }

    public function getActiveProductCard(): array
    {
        $cards = $this->getProductCards();
        $active = collect($cards)->first(fn (array $card): bool => (bool) ($card['is_active'] ?? false));

        if (is_array($active)) {
            return $active;
        }

        return $this->getPreset('product-card', 'card-1');
    }

    public function getProductPageLayouts(): array
    {
        $raw = Setting::get('builder__product_page_layouts_json', null) ?? Setting::get('product_page_layouts_json', '[]');

        return is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
    }

    public function getProductPageLayout(?object $product = null): array
    {
        $layouts = $this->getProductPageLayouts();
        $default = collect($layouts)->first(fn (array $layout): bool => (bool) ($layout['is_default'] ?? false));

        if (is_array($default)) {
            return $default;
        }

        return $this->getPreset('product-page', 'layout-a');
    }

    public function getShopBuilderConfig(): array
    {
        $raw = Setting::get('builder__shop_layout_config_json', null) ?? Setting::get('shop_layout_config_json', '{}');
        $config = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return $config ?: [
            'header' => ['enabled' => true, 'title' => 'Shop'],
            'filter' => ['position' => 'left', 'sidebar_width' => '280px', 'sticky' => true],
            'sort' => ['show_count' => true, 'show_sort' => true, 'show_view_switcher' => true, 'default_view' => 'grid'],
            'grid' => ['columns' => 4, 'per_page' => 24, 'pagination' => 'numbers', 'gap' => '16px'],
            'description' => ['enabled' => true, 'position' => 'below', 'max_chars' => 220],
        ];
    }

    public function saveConfig(string $type, array $config): bool
    {
        if (! $this->validateConfig($type, $config)) {
            return false;
        }

        $key = match ($type) {
            'header' => 'builder__header_config_json',
            'footer' => 'builder__footer_config_json',
            'homepage' => 'builder__homepage_sections_json',
            'product-card' => 'builder__product_card_configs_json',
            'product-page' => 'builder__product_page_layouts_json',
            'shop' => 'builder__shop_layout_config_json',
            default => 'builder__'.$type,
        };

        Setting::query()->updateOrCreate(['key' => $key], [
            'value' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'type' => 'json',
            'group' => 'builder',
            'autoload' => true,
        ]);

        return true;
    }

    public function validateConfig(string $type, array $config): bool
    {
        if ($type === 'header') {
            return isset($config['rows']) && is_array($config['rows']);
        }

        if ($type === 'footer') {
            return isset($config['rows']) && is_array($config['rows']) && isset($config['settings']);
        }
        if ($type === 'homepage') {
            return isset($config['sections']) && is_array($config['sections']);
        }
        if ($type === 'product-card') {
            return is_array($config);
        }
        if ($type === 'product-page') {
            return is_array($config);
        }
        if ($type === 'shop') {
            return is_array($config);
        }

        return is_array($config);
    }

    public function generateElementId(): string
    {
        return 'el_'.substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
    }

    public function getPreset(string $type, string $preset): array
    {
        $path = config_path('builder/presets/'.$type.'/'.$preset.'.json');

        if (! file_exists($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?: [];
    }

    public function getAvailableElements(string $type): array
    {
        return match ($type) {
            'header' => config('builder.header.available_elements', []),
            'footer' => config('builder.footer.available_elements', []),
            'homepage' => config('builder.homepage_sections', []),
            default => [],
        };
    }

    public function getElementDefaults(string $elementType): array
    {
        return match ($elementType) {
            'logo', 'footer-logo' => ['max_width' => '160px', 'link' => '/', 'text_logo' => ''],
            'main-menu' => ['style' => 'horizontal', 'spacing' => '24px', 'font_size' => '14px', 'font_weight' => '500'],
            'search' => ['style' => 'icon', 'placeholder' => 'Search products...'],
            'cart' => ['show_count' => true, 'mini_cart' => 'dropdown'],
            'wishlist' => ['show_count' => true],
            'account' => ['show_name' => false, 'dropdown' => true],
            'topbar-text' => ['text' => 'Free shipping on orders over $50', 'scrolling' => false],
            'social-icons', 'footer-social' => ['size' => 'sm', 'show' => ['facebook', 'instagram', 'twitter']],
            'language-switcher' => ['style' => 'dropdown', 'show_flag' => true],
            'currency-switcher' => ['style' => 'dropdown', 'show_symbol' => true],
            'cta-button' => ['text' => 'Shop Now', 'url' => '/shop'],
            'phone' => ['number' => '+1 000 000 0000', 'label' => 'Call Us'],
            'divider' => ['type' => 'line', 'height' => '30px', 'width' => '1px'],
            'custom-html' => ['html' => '<strong>Custom HTML</strong>'],
            'footer-links' => ['title' => 'Quick Links', 'links' => [['label' => 'Home', 'url' => '/']]],
            'footer-newsletter' => ['title' => 'Newsletter', 'button_text' => 'Subscribe'],
            'footer-contact' => ['title' => 'Contact', 'phone' => '+1 000 000 0000'],
            'footer-payment-icons' => ['show' => ['visa', 'mastercard', 'paypal']],
            default => [],
        };
    }
}
