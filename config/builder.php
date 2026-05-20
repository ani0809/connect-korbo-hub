<?php

return [
    'header' => [
        'rows' => ['topbar', 'main', 'bottom'],
        'max_elements_per_column' => 10,
        'available_elements' => [
            'logo', 'main-menu', 'search', 'cart',
            'wishlist', 'account', 'topbar-text',
            'social-icons', 'language-switcher',
            'currency-switcher', 'phone', 'custom-html',
            'cta-button', 'divider',
        ],
        'presets' => 5,
    ],
    'footer' => [
        'column_options' => [1, 2, 3, 4],
        'available_elements' => [
            'footer-logo', 'footer-links', 'footer-newsletter',
            'footer-social', 'footer-payment-icons',
            'footer-contact', 'custom-html', 'image-block', 'text-block',
        ],
    ],
    'product_card' => [
        'styles' => ['card-1', 'card-2', 'card-3', 'card-4', 'card-5', 'card-6'],
        'default' => 'card-1',
    ],
    'homepage_sections' => [
        'hero-slider', 'announcement-bar', 'category-grid',
        'banner-row', 'flash-deal', 'todays-deal',
        'featured-products', 'brand-logos', 'testimonials',
        'blog-posts', 'newsletter', 'custom-html',
        'video-banner', 'stats-counter',
    ],
    'product_page_layouts' => ['layout-a', 'layout-b', 'layout-c', 'layout-d'],
];
