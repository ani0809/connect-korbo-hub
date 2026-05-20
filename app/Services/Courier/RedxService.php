<?php

namespace App\Services\Courier;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class RedxService extends BaseCourier
{
    protected string $name = 'RedX';

    protected function loadCredentials(): void
    {
        $this->apiKey = (string) setting('redx_api_key', '');
        $this->baseUrl = (bool) setting('redx_sandbox', false)
            ? 'https://opensandbox.redx.com.bd'
            : 'https://openapi.redx.com.bd';
    }

    protected function getHeaders(): array
    {
        return [
            'API-ACCESS-TOKEN' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createParcel(Order $order, array $options = []): array
    {
        $payload = [
            'name' => $order->shipping_name,
            'phone' => $order->shipping_phone,
            'email' => $order->guest_email ?? $order->user?->email ?? '',
            'address' => $order->shipping_address,
            'area' => $order->shipping_city,
            'area_id' => $this->getAreaId((string) $order->shipping_city),
            'products' => [[
                'name' => (string) $order->items()->take(3)->pluck('product_name')->join(', '),
                'category' => 'Parcel',
                'value' => (float) $order->subtotal,
            ]],
            'parcel_details_instruction' => (string) ($order->notes ?? ''),
            'pickup_store_id' => setting('redx_pickup_store_id'),
            'invoice_id' => $order->order_number,
            'cash_collection_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
            'weight' => (int) round(((float) ($options['weight'] ?? 0.5)) * 1000),
        ];

        $result = $this->post('/v1.0.0/parcel', $payload);
        if (! $result['success']) {
            return $result;
        }
        $data = $result['data'];

        return [
            'success' => true,
            'tracking_code' => $data['tracking_id'] ?? null,
            'parcel_id' => $data['tracking_id'] ?? null,
            'raw' => $data,
        ];
    }

    public function trackParcel(string $trackingCode): array
    {
        $result = $this->get("/v1.0.0/parcel/track/{$trackingCode}");
        if (! $result['success']) {
            return $result;
        }
        $data = $result['data'];
        $parcel = $data['parcel'] ?? [];

        return [
            'success' => true,
            'tracking_code' => $trackingCode,
            'status' => $this->mapStatus((string) ($parcel['status'] ?? '')),
            'courier_status' => $parcel['status'] ?? '',
            'events' => array_map(static function ($event): array {
                return [
                    'time' => $event['created_at'] ?? '',
                    'status' => $event['status'] ?? '',
                    'message' => $event['message'] ?? '',
                    'location' => $event['location'] ?? '',
                ];
            }, $data['tracking'] ?? []),
            'raw' => $data,
        ];
    }

    public function cancelParcel(string $trackingCode): bool
    {
        $result = $this->post("/v1.0.0/parcel/{$trackingCode}/cancel");
        return (bool) ($result['success'] ?? false);
    }

    public function getZones(): array
    {
        $result = $this->get('/v1.0.0/area');
        return $result['data']['areas'] ?? [];
    }

    public function calculateCharge(string $zone, float $weight = 0.5, string $type = 'regular'): float
    {
        $insideDhaka = in_array(strtolower($zone), ['dhaka', 'dhaka city'], true);
        $base = $insideDhaka ? 60 : 120;
        if ($weight > 0.5) {
            $base += ceil($weight - 0.5) * 40;
        }
        return (float) $base;
    }

    private function getAreaId(string $city): int
    {
        $areas = Cache::remember('redx_areas', 86400, function (): array {
            return collect($this->getZones())
                ->pluck('id', 'name')
                ->toArray();
        });

        return (int) ($areas[ucfirst(strtolower($city))] ?? 1);
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'created', 'new' => 'created',
            'picked_up' => 'picked',
            'in_transit', 'at_hub' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'returned_to_merchant' => 'returned',
            default => 'in_transit',
        };
    }
}
