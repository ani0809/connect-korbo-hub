<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\OrderResource;
use App\Models\PaymentGateway;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\OrderPlacementService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutApiController extends BaseApiController
{
    public function shippingMethods(Request $request, CartService $cart): JsonResponse
    {
        $methods = ShippingMethod::query()->where('is_active', true)->orderBy('cost')->get()->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'cost' => (float) $m->cost,
            'cost_formatted' => currency_format((float) $m->cost),
            'estimated_days' => $m->estimated_days ?? null,
        ]);

        return $this->success(['methods' => $methods, 'cart_subtotal' => currency_format($cart->getSubtotal()), 'cart_subtotal_raw' => $cart->getSubtotal()]);
    }

    public function placeOrder(Request $request, OrderPlacementService $placement): JsonResponse
    {
        $gatewaySlugs = PaymentGateway::query()->where('is_active', true)->pluck('slug')->toArray();
        $allowedGateways = array_merge($gatewaySlugs, ['wallet', 'cod']);

        $request->validate([
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:50',
            'shipping_email' => 'nullable|email|max:120',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:120',
            'shipping_state' => 'nullable|string|max:120',
            'shipping_country' => 'required|string|max:120',
            'shipping_postal_code' => 'nullable|string|max:40',
            'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            'payment_method' => 'required|in:'.implode(',', $allowedGateways),
            'use_wallet' => 'sometimes|boolean',
            'wallet_amount' => 'nullable|numeric|min:0',
            'use_points' => 'sometimes|boolean',
            'points_to_redeem' => 'nullable|integer|min:0',
            'save_address' => 'sometimes|boolean',
            'order_notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $placement->placeFromAuthenticatedRequest($request);
            $order = $result['order'];

            return $this->success([
                'order' => new OrderResource($order),
                'payment_redirect_url' => $result['payment_redirect'],
                'success_url' => url('/checkout/success/'.$order->order_number),
            ], 'Order placed', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function paymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        $order = \App\Models\Order::query()
            ->where('order_number', $request->order_number)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return $this->error('Order already paid', 422);
        }

        try {
            $gateway = $paymentService->getGateway($request->payment_method);
            $payment = $gateway->createPayment($order);
            if (! $payment->success) {
                return $this->error($payment->message ?: 'Payment init failed', 422);
            }

            return $this->success([
                'redirect_url' => $payment->redirectUrl,
            ]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
