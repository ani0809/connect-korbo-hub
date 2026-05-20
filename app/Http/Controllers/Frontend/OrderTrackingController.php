<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(?string $trackingCode = null)
    {
        $track = request('track', $trackingCode);
        return view('frontend.orders.track', compact('track'));
    }

    public function track(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');
        if ($query === '') {
            return response()->json(['success' => false, 'message' => 'Enter order number or tracking code']);
        }

        $order = Order::query()->where('order_number', $query)->with(['items.product', 'shipment'])->first();
        if (! $order) {
            $shipment = Shipment::query()->where('tracking_code', $query)->with(['order.items.product', 'order.shipment'])->first();
            if ($shipment) {
                $order = $shipment->order;
            }
        }
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No order found with that number or tracking code.']);
        }

        $trackingEvents = [];
        $courierName = null;
        if ($order->shipment && $order->shipment->tracking_code) {
            $shipment = $order->shipment;
            $courierName = ucfirst((string) $shipment->courier);
            if ($shipment->last_tracked_at && $shipment->last_tracked_at->isAfter(now()->subMinutes(30)) && $shipment->tracking_history) {
                $trackingEvents = (array) $shipment->tracking_history;
            } else {
                try {
                    $courier = CourierManager::driver((string) $shipment->courier);
                    $result = $courier->trackParcel((string) $shipment->tracking_code);
                    if ($result['success'] ?? false) {
                        $trackingEvents = (array) ($result['events'] ?? []);
                        $shipment->update([
                            'tracking_history' => $trackingEvents,
                            'last_tracked_at' => now(),
                            'status' => $result['status'] ?? $shipment->status,
                            'delivered_at' => ($result['status'] ?? '') === 'delivered' ? now() : $shipment->delivered_at,
                        ]);
                        if (($result['status'] ?? '') === 'delivered') {
                            $order->update(['order_status' => 'delivered', 'delivered_at' => now()]);
                        }
                    }
                } catch (\Throwable) {
                    $trackingEvents = (array) ($shipment->tracking_history ?? []);
                }
            }
        }

        $displayStatus = $order->shipment?->status ?? $order->order_status;
        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'order_date' => $order->created_at->format('d M Y'),
            'status' => $displayStatus,
            'status_message' => $this->getStatusMessage((string) $displayStatus),
            'tracking_code' => $order->shipment?->tracking_code,
            'courier_name' => $courierName,
            'events' => $trackingEvents,
            'address' => $order->shipping_name."\n".$order->shipping_address."\n".$order->shipping_city.', '.$order->shipping_country,
            'items' => $order->items->map(static function ($item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'qty' => $item->quantity,
                    'price' => currency_format((float) $item->subtotal),
                    'image' => $item->product?->thumbnail ? asset('storage/'.$item->product->thumbnail) : asset('images/placeholder.png'),
                ];
            })->values()->toArray(),
        ]);
    }

    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'pending' => 'Your order has been received and is being reviewed.',
            'confirmed' => 'Your order has been confirmed and will be prepared soon.',
            'processing' => 'Your order is being packed and will be shipped soon.',
            'shipped', 'in_transit' => 'Your order is on its way!',
            'out_for_delivery' => 'Your order is out for delivery today!',
            'delivered' => 'Your order has been delivered. Enjoy your purchase!',
            'cancelled' => 'This order has been cancelled.',
            'returned' => 'This order has been returned.',
            default => 'Your order is being processed.',
        };
    }
}
