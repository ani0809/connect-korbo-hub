<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackingService
{
    public function trackFacebookEvent(string $eventName, array $eventData = [], ?Request $request = null): void
    {
        if ((string) setting('tracking_enabled', '1') !== '1') {
            return;
        }
        if ((string) setting('cookie_consent_mode', 'required') === 'required' && request()->cookie('cookie_consent') === 'rejected') {
            return;
        }

        $pixelId = (string) setting('fb_pixel_id', '');
        $accessToken = (string) setting('fb_conversion_api_token', '');
        if ($pixelId === '' || $accessToken === '') {
            return;
        }

        $request ??= request();
        try {
            $userData = [];
            if ($request->user()) {
                $user = $request->user();
                $firstName = (string) explode(' ', (string) $user->name)[0];
                $userData = [
                    'em' => [hash('sha256', strtolower((string) $user->email))],
                    'ph' => ! empty($user->phone) ? [hash('sha256', preg_replace('/[^0-9]/', '', (string) $user->phone))] : [],
                    'fn' => [hash('sha256', strtolower($firstName))],
                    'external_id' => [hash('sha256', (string) $user->id)],
                ];
            }

            $payload = [
                'data' => [[
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_source_url' => $request->fullUrl(),
                    'action_source' => 'website',
                    'user_data' => array_merge([
                        'client_ip_address' => $request->ip(),
                        'client_user_agent' => (string) $request->userAgent(),
                        'fbc' => $request->cookie('_fbc'),
                        'fbp' => $request->cookie('_fbp'),
                    ], $userData),
                    'custom_data' => $eventData,
                ]],
            ];

            if (config('app.debug')) {
                Log::info('FB CAPI test mode', $payload);
                return;
            }

            Http::timeout(5)->post("https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$accessToken}", $payload);
        } catch (\Throwable $e) {
            Log::error('FB Pixel Server API failed', ['error' => $e->getMessage()]);
        }
    }

    public function trackGA4Event(string $eventName, array $params = [], ?Request $request = null): void
    {
        if ((string) setting('tracking_enabled', '1') !== '1') {
            return;
        }
        if ((string) setting('cookie_consent_mode', 'required') === 'required' && request()->cookie('cookie_consent') === 'rejected') {
            return;
        }

        $measurementId = (string) setting('ga4_measurement_id', '');
        $apiSecret = (string) setting('ga4_api_secret', '');
        if ($measurementId === '' || $apiSecret === '') {
            return;
        }

        $request ??= request();
        try {
            $clientId = (string) ($request->cookie('_ga') ?: Str::uuid()->toString());
            if (str_starts_with($clientId, 'GA1.')) {
                $parts = explode('.', $clientId);
                if (count($parts) >= 4) {
                    $clientId = $parts[2].'.'.$parts[3];
                }
            }

            $payload = [
                'client_id' => $clientId,
                'events' => [[
                    'name' => $eventName,
                    'params' => $params,
                ]],
            ];
            if (config('app.debug')) {
                Log::info('GA4 MP test mode', $payload);
                return;
            }
            Http::timeout(5)->post(
                "https://www.google-analytics.com/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}",
                $payload
            );
        } catch (\Throwable $e) {
            Log::error('GA4 Measurement Protocol failed', ['error' => $e->getMessage()]);
        }
    }

    public function trackPurchase(Order $order, Request $request): void
    {
        dispatch(function () use ($order, $request): void {
            $order->loadMissing('items');
            $items = $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->unit_price,
            ])->toArray();

            $this->trackFacebookEvent('Purchase', [
                'currency' => setting('currency_code', 'BDT'),
                'value' => (float) $order->total,
                'order_id' => $order->order_number,
                'content_ids' => collect($items)->pluck('id')->values()->toArray(),
                'content_type' => 'product',
                'num_items' => (int) $order->items->sum('quantity'),
            ], $request);

            $this->trackGA4Event('purchase', [
                'transaction_id' => $order->order_number,
                'value' => (float) $order->total,
                'currency' => setting('currency_code', 'BDT'),
                'coupon' => (string) ($order->coupon_code ?? ''),
                'items' => array_map(fn ($i) => [
                    'item_id' => $i['id'],
                    'item_name' => $i['name'],
                    'quantity' => $i['quantity'],
                    'price' => $i['price'],
                ], $items),
            ], $request);
        })->afterResponse();
    }

    public function trackAddToCart(Product $product, int $quantity, float $price): void
    {
        dispatch(function () use ($product, $quantity, $price): void {
            $this->trackFacebookEvent('AddToCart', [
                'content_ids' => [(string) $product->id],
                'content_name' => $product->name,
                'currency' => setting('currency_code', 'BDT'),
                'value' => $price * $quantity,
            ]);
            $this->trackGA4Event('add_to_cart', [
                'currency' => setting('currency_code', 'BDT'),
                'value' => $price * $quantity,
                'items' => [[
                    'item_id' => (string) $product->id,
                    'item_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                ]],
            ]);
        })->afterResponse();
    }

    public function trackViewContent(Product $product): void
    {
        dispatch(function () use ($product): void {
            $this->trackFacebookEvent('ViewContent', [
                'content_ids' => [(string) $product->id],
                'content_name' => $product->name,
                'content_category' => $product->category?->name,
                'value' => (float) $product->main_price,
                'currency' => setting('currency_code', 'BDT'),
            ]);
            $this->trackGA4Event('view_item', [
                'currency' => setting('currency_code', 'BDT'),
                'value' => (float) $product->main_price,
                'items' => [[
                    'item_id' => (string) $product->id,
                    'item_name' => $product->name,
                ]],
            ]);
        })->afterResponse();
    }
}

