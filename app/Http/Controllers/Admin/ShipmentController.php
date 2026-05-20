<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::query()
            ->with(['order:id,order_number,total,payment_method,shipping_name,shipping_phone'])
            ->filter($request->only(['courier', 'status', 'date_from', 'date_to', 'search']))
            ->latest()
            ->paginate(30);

        $stats = [
            'pending' => Shipment::query()->where('status', 'pending')->count(),
            'in_transit' => Shipment::query()->whereIn('status', ['created', 'picked', 'in_transit', 'out_for_delivery'])->count(),
            'delivered' => Shipment::query()->where('status', 'delivered')->count(),
            'returned' => Shipment::query()->where('status', 'returned')->count(),
        ];

        $availableCouriers = CourierManager::available();

        return view('admin.shipments.index', compact('shipments', 'stats', 'availableCouriers'));
    }

    public function create(int $orderId)
    {
        $order = Order::query()->with(['items.product', 'user'])->findOrFail($orderId);
        $couriers = CourierManager::available();
        $recommended = CourierManager::recommend($order);

        return view('admin.shipments.create', compact('order', 'couriers', 'recommended'));
    }

    public function store(Request $request, int $orderId): RedirectResponse
    {
        $order = Order::query()->with('items')->findOrFail($orderId);
        $validated = $request->validate([
            'courier' => 'required|in:pathao,steadfast,redx,manual',
            'weight' => 'required|numeric|min:0.1',
            'delivery_type' => 'nullable|string',
            'zone' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($order, $validated): void {
                $courier = CourierManager::driver($validated['courier']);
                $result = $courier->createParcel($order, [
                    'weight' => (float) $validated['weight'],
                    'delivery_type' => $validated['delivery_type'] ?? 'regular',
                    'zone' => $validated['zone'] ?? null,
                ]);

                if (! ($result['success'] ?? false)) {
                    throw new \RuntimeException($result['message'] ?? 'Courier API failed');
                }

                $shipment = Shipment::query()->create([
                    'order_id' => $order->id,
                    'courier' => $validated['courier'],
                    'tracking_code' => $result['tracking_code'] ?? null,
                    'consignment_id' => $result['consignment_id'] ?? null,
                    'parcel_id' => $result['parcel_id'] ?? null,
                    'status' => 'created',
                    'weight' => (float) $validated['weight'],
                    'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
                    'delivery_charge' => (float) ($result['delivery_fee'] ?? 0),
                    'raw_data' => $result['raw'] ?? null,
                ]);

                $order->update(['order_status' => 'processing', 'shipment_id' => $shipment->id]);

                app(NotificationService::class)->send('order.shipped', $order->user, [
                    'order_number' => $order->order_number,
                    'tracking_code' => $shipment->tracking_code,
                    'courier' => ucfirst($validated['courier']),
                    'tracking_url' => route('order.track', ['trackingCode' => $shipment->tracking_code]),
                ]);
            });

            return redirect()->route('admin.shipments.index')->with('ok', 'Shipment created successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function bulkCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1|max:50',
            'order_ids.*' => 'integer',
            'courier' => 'required|in:pathao,steadfast,redx,manual',
            'weight' => 'nullable|numeric|min:0.1',
        ]);

        $orderIds = $validated['order_ids'];
        $courierName = $validated['courier'];
        $weight = (float) ($validated['weight'] ?? 0.5);
        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->whereNull('shipment_id')
            ->where(function ($query): void {
                $query->where('payment_status', 'paid')->orWhere('payment_method', 'cod');
            })
            ->get();

        $success = 0;
        $failed = 0;
        $errors = [];

        if ($courierName === 'steadfast' && $orders->count() >= 10) {
            try {
                $service = CourierManager::driver('steadfast');
                if (method_exists($service, 'bulkCreate')) {
                    $service->bulkCreate($orders->all());
                }
            } catch (\Throwable) {
            }
        }

        foreach ($orders as $order) {
            try {
                $courierService = CourierManager::driver($courierName);
                $result = $courierService->createParcel($order, ['weight' => $weight]);
                if (! ($result['success'] ?? false)) {
                    throw new \RuntimeException($result['message'] ?? 'API failed');
                }

                $shipment = Shipment::query()->create([
                    'order_id' => $order->id,
                    'courier' => $courierName,
                    'tracking_code' => $result['tracking_code'] ?? null,
                    'consignment_id' => $result['consignment_id'] ?? null,
                    'parcel_id' => $result['parcel_id'] ?? null,
                    'status' => 'created',
                    'weight' => $weight,
                    'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
                    'delivery_charge' => (float) ($result['delivery_fee'] ?? 0),
                    'raw_data' => $result['raw'] ?? null,
                ]);
                $order->update(['order_status' => 'processing', 'shipment_id' => $shipment->id]);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = $order->order_number.': '.$e->getMessage();
            }
        }

        return response()->json(['success' => true, 'created' => $success, 'failed' => $failed, 'errors' => $errors]);
    }

    public function track(int $id): JsonResponse
    {
        $shipment = Shipment::query()->findOrFail($id);
        if (! $shipment->tracking_code) {
            return response()->json(['success' => false, 'message' => 'No tracking code']);
        }
        try {
            $courier = CourierManager::driver($shipment->courier);
            $result = $courier->trackParcel($shipment->tracking_code);
            if ($result['success'] ?? false) {
                $newStatus = $result['status'] ?? $shipment->status;
                $shipment->update([
                    'status' => $newStatus,
                    'tracking_history' => $result['events'] ?? [],
                    'last_tracked_at' => now(),
                    'delivered_at' => $newStatus === 'delivered' ? now() : null,
                ]);
                if ($newStatus === 'delivered') {
                    Order::query()->where('id', $shipment->order_id)->update(['order_status' => 'delivered', 'delivered_at' => now()]);
                }
            }

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function printLabel(int $id)
    {
        $shipment = Shipment::query()->with('order')->findOrFail($id);
        return view('admin.shipments.label', compact('shipment'));
    }

    public function cancel(int $id): RedirectResponse
    {
        $shipment = Shipment::query()->findOrFail($id);
        if ($shipment->tracking_code) {
            try {
                CourierManager::driver($shipment->courier)->cancelParcel($shipment->tracking_code);
            } catch (\Throwable) {
            }
        }
        $shipment->update(['status' => 'cancelled']);
        if ($shipment->order) {
            $shipment->order->update(['order_status' => 'cancelled']);
        }
        return back()->with('ok', 'Shipment cancelled.');
    }
}
