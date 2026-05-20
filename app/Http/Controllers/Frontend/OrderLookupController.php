<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderLookupController extends Controller
{
    public function lookup(Request $request): View
    {
        if ($request->isMethod('get')) {
            return view('frontend.orders.lookup');
        }

        $data = $request->validate([
            'email' => 'required|email',
            'order_number' => 'required|string',
        ]);

        $order = Order::query()
            ->where('order_number', $data['order_number'])
            ->where(function ($q) use ($data): void {
                $q->where('guest_email', $data['email'])
                    ->orWhereHas('user', fn ($q2) => $q2->where('email', $data['email']));
            })
            ->with(['items.product', 'statusHistory'])
            ->firstOrFail();

        return view('frontend.orders.guest-detail', compact('order'));
    }
}

