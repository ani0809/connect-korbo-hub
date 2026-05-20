<?php

namespace App\Services\Courier;

use App\Models\Order;

class SteadfastService extends BaseCourier
{
    protected string $name = 'Steadfast';

    protected function loadCredentials(): void
    {
        $this->apiKey = (string) setting('steadfast_api_key', '');
        $this->secretKey = (string) setting('steadfast_secret_key', '');
        $this->baseUrl = 'https://portal.steadfast.com.bd/api/v1';
    }

    protected function getHeaders(): array
    {
        return [
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createParcel(Order $order, array $options = []): array
    {
        $payload = [
            'invoice' => $order->order_number,
            'recipient_name' => $order->shipping_name,
            'recipient_phone' => $order->shipping_phone,
            'recipient_address' => $order->shipping_address.', '.$order->shipping_city.', '.$order->shipping_country,
            'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
            'note' => (string) ($order->notes ?? ''),
            'weight' => (float) ($options['weight'] ?? 0.5),
        ];

        $result = $this->post('/create_order', $payload);
        if (! $result['success']) {
            return $result;
        }
        $data = $result['data'];

        if (($data['status'] ?? 0) !== 200 || ! isset($data['consignment'])) {
            return ['success' => false, 'message' => $data['message'] ?? 'Failed to create parcel'];
        }
        $consignment = $data['consignment'];

        return [
            'success' => true,
            'tracking_code' => $consignment['tracking_code'] ?? null,
            'consignment_id' => $consignment['id'] ?? null,
            'delivery_fee' => (float) ($consignment['delivery_fee'] ?? 0),
            'cod_fee' => (float) ($consignment['cod_fee'] ?? 0),
            'raw' => $consignment,
        ];
    }

    public function bulkCreate(array $orders): array
    {
        $payload = array_map(static function (Order $order): array {
            return [
                'invoice' => $order->order_number,
                'recipient_name' => $order->shipping_name,
                'recipient_phone' => $order->shipping_phone,
                'recipient_address' => $order->shipping_address.', '.$order->shipping_city,
                'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
                'note' => (string) ($order->notes ?? ''),
            ];
        }, $orders);

        return $this->post('/create_order/bulk-order', $payload);
    }

    public function trackParcel(string $trackingCode): array
    {
        $result = $this->get("/status_by_trackingcode/{$trackingCode}");
        if (! $result['success']) {
            return ['success' => false, 'message' => 'Tracking info not found'];
        }
        $data = $result['data'];
        if (($data['status'] ?? 0) !== 200) {
            return ['success' => false, 'message' => 'Tracking info not found'];
        }
        $delivery = $data['delivery_status'] ?? [];

        return [
            'success' => true,
            'tracking_code' => $trackingCode,
            'status' => $this->mapStatus((string) ($delivery['current_status'] ?? '')),
            'courier_status' => $delivery['current_status'] ?? '',
            'events' => $this->parseEvents($data),
            'recipient_name' => $delivery['recipient_name'] ?? '',
            'recipient_phone' => $delivery['recipient_phone'] ?? '',
            'raw' => $data,
        ];
    }

    public function trackByInvoice(string $invoiceId): array
    {
        return $this->get("/status_by_invoice/{$invoiceId}");
    }

    public function cancelParcel(string $trackingCode): bool
    {
        return false;
    }

    public function getZones(): array
    {
        return [
            'inside_dhaka' => ['name' => 'Inside Dhaka', 'charge' => 60, 'cod_charge' => 30],
            'outside_dhaka' => ['name' => 'Outside Dhaka', 'charge' => 110, 'cod_charge' => 30],
            'sub_district' => ['name' => 'Sub-District', 'charge' => 150, 'cod_charge' => 30],
        ];
    }

    public function calculateCharge(string $zone, float $weight = 0.5, string $type = 'regular'): float
    {
        $zones = $this->getZones();
        $baseCharge = (float) ($zones[$zone]['charge'] ?? 110);
        if ($weight > 0.5) {
            $baseCharge += ceil($weight - 0.5) * 40;
        }

        return $baseCharge;
    }

    public function getBalance(): array
    {
        $result = $this->get('/get_balance');
        if (! $result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'balance' => (float) ($result['data']['current_balance'] ?? 0),
            'withdrawable' => (float) ($result['data']['withdraw_amount'] ?? 0),
        ];
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'in_review', 'waiting_for_courier_pickup' => 'created',
            'courier_accepted', 'courier_picked_up' => 'picked',
            'in_transit', 'at_sortation' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered', 'partial_delivered' => 'delivered',
            'cancelled', 'unknown' => 'cancelled',
            'hold' => 'in_transit',
            'in_return', 'return_to_merchant' => 'returned',
            default => 'in_transit',
        };
    }

    private function parseEvents(array $data): array
    {
        $events = [];
        $status = $data['delivery_status'] ?? [];
        if (! empty($status['current_status'])) {
            $events[] = [
                'time' => $status['updated_at'] ?? '',
                'status' => $status['current_status'],
                'message' => $status['note'] ?? '',
                'location' => '',
            ];
        }

        return $events;
    }
}
