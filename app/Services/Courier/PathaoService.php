<?php

namespace App\Services\Courier;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoService extends BaseCourier
{
    protected string $name = 'Pathao';
    private string $accessToken = '';

    protected function loadCredentials(): void
    {
        $this->apiKey = (string) setting('pathao_client_id', '');
        $this->secretKey = (string) setting('pathao_client_secret', '');
        $this->baseUrl = (bool) setting('pathao_sandbox', false)
            ? 'https://hermes-api.staging.pathao.com'
            : 'https://api-hermes.pathao.com';
    }

    private function getAccessToken(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            Cache::forget('pathao_access_token');
            $this->accessToken = '';
        }

        if ($this->accessToken !== '') {
            return $this->accessToken;
        }

        $cached = Cache::get('pathao_access_token');
        if ($cached) {
            $this->accessToken = (string) $cached;
            return $this->accessToken;
        }

        $response = Http::timeout(30)->post($this->baseUrl.'/aladdin/api/v1/issue-token', [
            'client_id' => $this->apiKey,
            'client_secret' => $this->secretKey,
            'username' => setting('pathao_username'),
            'password' => setting('pathao_password'),
            'grant_type' => 'password',
        ]);

        if (! $response->successful()) {
            Log::error('Pathao auth failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Pathao authentication failed');
        }

        $token = (string) $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);
        Cache::put('pathao_access_token', $token, now()->addSeconds(max(60, $expiresIn - 60)));
        $this->accessToken = $token;

        return $token;
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function post(string $endpoint, array $data = [], array $headers = []): array
    {
        $result = parent::post($endpoint, $data, $headers);
        if (($result['status'] ?? null) === 401) {
            try {
                $this->getAccessToken(true);
                return parent::post($endpoint, $data, $headers);
            } catch (\Throwable) {
            }
        }

        return $result;
    }

    public function createParcel(Order $order, array $options = []): array
    {
        $storeId = setting('pathao_store_id');
        if (! $storeId) {
            return ['success' => false, 'message' => 'Pathao store ID not configured'];
        }

        $weight = (float) ($options['weight'] ?? 0.5);
        $payload = [
            'store_id' => (int) $storeId,
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $order->shipping_name,
            'recipient_phone' => $order->shipping_phone,
            'recipient_address' => $order->shipping_address.', '.$order->shipping_city,
            'recipient_city' => $this->getCityId((string) $order->shipping_city),
            'recipient_zone' => $this->getZoneId((string) $order->shipping_city, $options['zone'] ?? null),
            'delivery_type' => ($options['delivery_type'] ?? 'regular') === 'express' ? 12 : 48,
            'item_type' => 2,
            'special_instruction' => (string) ($order->notes ?? ''),
            'item_quantity' => (int) $order->items()->sum('quantity'),
            'item_weight' => $weight,
            'amount_to_collect' => $order->payment_method === 'cod' ? (float) $order->total : 0,
            'item_description' => (string) $order->items()->take(3)->pluck('product_name')->join(', '),
        ];

        $result = $this->post('/aladdin/api/v1/orders', $payload);
        if (! $result['success']) {
            return $result;
        }
        $data = $result['data'];

        return [
            'success' => true,
            'tracking_code' => $data['consignment_id'] ?? null,
            'consignment_id' => $data['consignment_id'] ?? null,
            'delivery_fee' => (float) ($data['delivery_fee'] ?? 0),
            'raw' => $data,
        ];
    }

    public function trackParcel(string $trackingCode): array
    {
        $result = $this->get("/aladdin/api/v1/orders/{$trackingCode}/info");
        if (! $result['success']) {
            return $result;
        }
        $data = $result['data'];

        return [
            'success' => true,
            'tracking_code' => $trackingCode,
            'status' => $this->mapStatus((string) ($data['order_status'] ?? '')),
            'courier_status' => $data['order_status'] ?? '',
            'events' => $this->parseEvents($data),
            'raw' => $data,
        ];
    }

    public function cancelParcel(string $trackingCode): bool
    {
        $result = $this->post('/aladdin/api/v1/orders/cancel', [
            'order_id' => $trackingCode,
            'reason' => 'Cancelled by merchant',
        ]);

        return (bool) ($result['success'] ?? false);
    }

    public function getZones(): array
    {
        $result = $this->get('/aladdin/api/v1/countries/1/city-list');
        return $result['data'] ?? [];
    }

    public function calculateCharge(string $zone, float $weight = 0.5, string $type = 'regular'): float
    {
        $result = $this->post('/aladdin/api/v1/merchant/price-plan', [
            'store_id' => setting('pathao_store_id'),
            'item_weight' => $weight,
            'recipient_city' => $zone,
            'delivery_type' => $type === 'express' ? 12 : 48,
        ]);

        return (float) ($result['data']['price'] ?? 0);
    }

    private function getCityId(string $city): int
    {
        $cities = Cache::remember('pathao_cities', 86400, fn (): array => $this->getZones());
        $normalized = strtolower(trim($city));
        $match = collect($cities)->first(function ($item) use ($normalized) {
            return strtolower((string) ($item['city_name'] ?? '')) === $normalized;
        });

        return (int) ($match['city_id'] ?? $this->getDefaultCityId($city));
    }

    private function getDefaultCityId(string $city): int
    {
        return match (strtolower($city)) {
            'dhaka' => 1,
            'chittagong', 'chattogram' => 2,
            'sylhet' => 3,
            'rajshahi' => 4,
            'khulna' => 5,
            'barisal' => 6,
            'rangpur' => 7,
            'mymensingh' => 8,
            default => 1,
        };
    }

    private function getZoneId(string $city, ?string $zone): int
    {
        if ($zone) {
            $cacheKey = 'pathao_zones_'.strtolower($city);
            $zones = Cache::remember($cacheKey, 86400, function () use ($city): array {
                $result = $this->get('/aladdin/api/v1/countries/1/cities/'.$this->getCityId($city).'/zone-list');
                return $result['data'] ?? [];
            });
            $match = collect($zones)->first(function ($item) use ($zone) {
                return strtolower((string) ($item['zone_name'] ?? '')) === strtolower($zone);
            });
            if ($match) {
                return (int) $match['zone_id'];
            }
        }

        return 1;
    }

    private function mapStatus(string $courierStatus): string
    {
        return match (strtolower($courierStatus)) {
            'pending', 'order_placed' => 'pending',
            'pickup_requested', 'pickup_scheduled' => 'created',
            'picked_up' => 'picked',
            'in_review', 'at_transit_hub', 'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'returned', 'return_in_transit' => 'returned',
            default => 'in_transit',
        };
    }

    private function parseEvents(array $data): array
    {
        $events = [];
        foreach (($data['tracking_events'] ?? []) as $event) {
            $events[] = [
                'time' => $event['created_at'] ?? '',
                'status' => $event['status'] ?? '',
                'message' => $event['message'] ?? '',
                'location' => $event['location'] ?? '',
            ];
        }

        return $events;
    }
}
