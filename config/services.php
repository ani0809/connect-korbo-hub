<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/google/callback',
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/facebook/callback',
    ],

    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/twitter/callback',
    ],

    'paytm-wallet' => [
        'env' => env('PAYTM_ENVIRONMENT'),
        'merchant_id' => env('PAYTM_MERCHANT_ID'),
        'merchant_key' => env('PAYTM_MERCHANT_KEY'),
        'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
        'channel' => env('PAYTM_CHANNEL'),
        'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ],

    'cibato_license' => [
        'activation_portal_url' => env('CIBATO_ACTIVATION_PORTAL_URL', 'https://licensing.cibato.com'),
        'endpoints' => [
            'check_ecommerce' => env('CIBATO_LICENSE_CHECK_ECOMMERCE', 'https://licensing.cibato.com/activation/check/eCommerce/{key}'),
            'verify_purchase_code' => env('CIBATO_LICENSE_VERIFY_PURCHASE_CODE', 'https://licensing.cibato.com/activation/verify-purchase-code/{purchase_code}'),
            'item_info' => env('CIBATO_LICENSE_ITEM_INFO', 'https://licensing.cibato.com/item_info/{purchase_code}'),
            'registered_addon_info' => env('CIBATO_LICENSE_REGISTERED_ADDON_INFO', 'https://licensing.cibato.com/registered-addon-info/{purchase_code}'),
            'registered_addon_list' => env('CIBATO_LICENSE_REGISTERED_ADDON_LIST', 'https://licensing.cibato.com/registered-addon-list/{purchase_code}'),
            'check_addon_activation' => env('CIBATO_LICENSE_CHECK_ADDON_ACTIVATION', 'https://licensing.cibato.com/check_addon_activation'),
            'check_activation' => env('CIBATO_LICENSE_CHECK_ACTIVATION', 'https://licensing.cibato.com/check_activation'),
            'check_flutter' => env('CIBATO_LICENSE_CHECK_FLUTTER', 'https://licensing.cibato.com/activation/check/flutter/{key}'),
        ],
    ],

];
