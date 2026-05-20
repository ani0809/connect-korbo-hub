<?php

return [
    'name' => env('SHOP_NAME', 'Cibato Commerce'),
    'version' => '1.0.0',
    'license_server' => env('LICENSE_SERVER_URL', 'https://license.yourdomain.com'),
    'license_api_secret' => env('LICENSE_API_SECRET', ''),
    'product_slug' => env('LICENSE_PRODUCT_SLUG', 'cibato-commerce'),
    'currency' => env('DEFAULT_CURRENCY', 'USD'),
    'currency_symbol' => env('DEFAULT_CURRENCY_SYMBOL', '$'),
    'timezone' => env('DEFAULT_TIMEZONE', 'UTC'),
    'per_page' => (int) env('PRODUCTS_PER_PAGE', 20),
    'thumbnail_size' => [300, 300],
    'gallery_size' => [800, 800],
    'demo_mode' => (bool) env('DEMO_MODE', false),
    'allow_impersonation' => (bool) env('ALLOW_ADMIN_IMPERSONATION', false),
];
