<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class SettingsApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $keys = [
            'site_name', 'site_tagline', 'currency_symbol', 'currency_code',
            'tax_inclusive', 'guest_checkout', 'products_per_page',
            'whatsapp_chat_enabled', 'whatsapp_number',
        ];

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = setting($key);
        }

        $data['app_version'] = config('shop.version', '1.0.0');

        return $this->success($data);
    }

    public function countries(): JsonResponse
    {
        $json = (string) setting('countries_json', '[]');
        $decoded = json_decode($json, true);

        return $this->success(is_array($decoded) ? $decoded : []);
    }

    public function currencies(): JsonResponse
    {
        $json = (string) setting('currencies_json', '[]');
        $decoded = json_decode($json, true);
        if (is_array($decoded) && $decoded !== []) {
            return $this->success($decoded);
        }

        return $this->success([
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
        ]);
    }

    public function languages(): JsonResponse
    {
        $locales = config('app.available_locales', ['en' => 'English']);

        return $this->success(collect($locales)->map(fn ($label, $code) => ['code' => $code, 'name' => $label])->values());
    }
}
