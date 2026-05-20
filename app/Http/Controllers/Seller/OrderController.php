<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth()->user()->seller->id;

        $orders = Order::query()->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->with(['items' => fn ($q) => $q->where('seller_id', $sellerId)->with(['product', 'variant']), 'user'])
            ->when($request->filled('status'), fn ($q) => $q->whereHas('items', fn ($iq) => $iq->where('seller_id', $sellerId)->where('item_status', $request->status)))
            ->latest()->paginate(20)->withQueryString();

        return view('seller.orders.index', compact('orders'));
    }

    public function show(string $orderNumber)
    {
        $sellerId = auth()->user()->seller->id;
        $order = Order::query()->where('order_number', $orderNumber)
            ->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->with(['items' => fn ($q) => $q->where('seller_id', $sellerId)->with(['product', 'variant']), 'user', 'statusHistory'])
            ->firstOrFail();

        return view('seller.orders.show', compact('order'));
    }

    public function updateItemStatus(Request $request): RedirectResponse
    {
        $request->validate(['item_id' => 'required|exists:order_items,id', 'status' => 'required|string']);
        $sellerId = auth()->user()->seller->id;

        $item = OrderItem::query()->where(['id' => $request->item_id, 'seller_id' => $sellerId])->firstOrFail();
        $item->update(['item_status' => $request->status]);
        $item->statuses()->create(['order_id' => $item->order_id, 'order_item_id' => $item->id, 'status' => $request->status, 'comment' => $request->comment, 'changed_by' => auth()->id()]);

        return back()->with('success', 'Item status updated.');
    }
}
