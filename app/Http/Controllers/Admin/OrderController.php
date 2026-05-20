<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['items', 'user']);

        if ($request->filled('status')) $query->where('order_status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('seller')) $query->whereHas('items', fn ($q) => $q->where('seller_id', (int) $request->seller));
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(fn ($q) => $q->where('order_number', 'like', $s)->orWhere('guest_name', 'like', $s)->orWhere('guest_email', 'like', $s)->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $s)->orWhere('email', 'like', $s)));
        }

        match ($request->string('sort', 'newest')->value()) {
            'oldest' => $query->oldest(),
            'total' => $query->orderByDesc('total'),
            default => $query->latest(),
        };

        $orders = $query->paginate(20)->withQueryString();
        $sellers = Seller::query()->orderBy('shop_name')->get();
        return view('admin.orders.index', compact('orders', 'sellers'));
    }

    public function show(int $id)
    {
        $order = Order::query()->with(['items.product', 'items.variant.attributeValues.attribute', 'items.seller', 'user', 'cashier', 'posSession', 'statusHistory.changedBy', 'transactions'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, OrderService $service)
    {
        $request->validate(['order_id' => 'required|exists:orders,id', 'status' => 'required|string', 'comment' => 'nullable|string']);
        $order = Order::query()->findOrFail((int) $request->order_id);
        $service->updateOrderStatus($order, (string) $request->status, (string) ($request->comment ?? ''));
        return back()->with('success', 'Order status updated.');
    }

    public function updateItemStatus(Request $request)
    {
        $request->validate(['item_id' => 'required|exists:order_items,id', 'status' => 'required|string']);
        $item = \App\Models\OrderItem::query()->findOrFail((int) $request->item_id);
        $item->update(['item_status' => $request->status]);
        $item->statuses()->create(['order_id' => $item->order_id, 'order_item_id' => $item->id, 'status' => $request->status, 'comment' => $request->comment, 'changed_by' => auth()->id()]);
        return back()->with('success', 'Item status updated.');
    }

    public function generateInvoice(int $id, OrderService $service)
    {
        $order = Order::query()->with(['items.product', 'items.variant.attributeValues.attribute', 'user'])->findOrFail($id);
        $pdf = $service->generateInvoice($order);
        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="invoice-'.$order->order_number.'.pdf"']);
    }

    public function bulkExport(Request $request)
    {
        $orders = Order::query()->with('items')->latest()->get();
        $csv = "order_number,customer,total,payment_status,order_status,created_at,items\n";
        foreach ($orders as $o) {
            $items = $o->items->map(fn ($i) => $i->product_name.' x'.$i->quantity)->implode(' | ');
            $customer = $o->user?->name ?: $o->guest_name;
            $csv .= implode(',', ['"'.$o->order_number.'"', '"'.str_replace('"', '""', (string) $customer).'"', $o->total, $o->payment_status, $o->order_status, $o->created_at, '"'.str_replace('"', '""', $items).'"'])."\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=orders.csv']);
    }
}
