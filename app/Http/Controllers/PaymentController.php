<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function stripeIntent(Request $request, PaymentService $payments): JsonResponse
    {
        $order = Order::query()->findOrFail((int) $request->integer('order_id'));
        $res = $payments->getGateway('stripe')->createPayment($order);
        return response()->json(['success' => $res->success, 'message' => $res->message, ...$res->data]);
    }

    public function stripeWebhook(Request $request, PaymentService $payments): JsonResponse
    {
        $res = $payments->getGateway('stripe')->handleWebhook($request);
        return response()->json(['success' => $res->success, 'message' => $res->message]);
    }

    public function paypalRedirect(Order $order, PaymentService $payments)
    {
        $res = $payments->getGateway('paypal')->createPayment($order);
        return $res->success ? redirect()->away((string) $res->redirectUrl) : redirect()->route('payment.failed', $order->id)->with('error', $res->message);
    }

    public function paypalSuccess(Request $request, PaymentService $payments)
    {
        $res = $payments->getGateway('paypal')->handleCallback($request);
        return $res->success && $res->order ? redirect()->route('checkout.success', $res->order->order_number) : redirect()->route('home')->with('error', $res->message ?: 'PayPal payment failed.');
    }

    public function paypalCancel()
    {
        return redirect()->route('cart.index')->with('error', 'PayPal payment canceled.');
    }

    public function bkashRedirect(Order $order, PaymentService $payments)
    {
        $res = $payments->getGateway('bkash')->createPayment($order);
        return $res->success ? redirect()->away((string) $res->redirectUrl) : redirect()->route('payment.failed', $order->id)->with('error', $res->message);
    }

    public function bkashCallback(Request $request, PaymentService $payments)
    {
        $res = $payments->getGateway('bkash')->handleCallback($request);
        return $res->success && $res->order ? redirect()->route('checkout.success', $res->order->order_number) : redirect()->route('home')->with('error', $res->message ?: 'Bkash payment failed.');
    }

    public function nagadRedirect(Order $order, PaymentService $payments)
    {
        $res = $payments->getGateway('nagad')->createPayment($order);
        return $res->success ? redirect()->away((string) $res->redirectUrl) : redirect()->route('payment.failed', $order->id)->with('error', $res->message);
    }

    public function nagadCallback(Request $request, PaymentService $payments)
    {
        $res = $payments->getGateway('nagad')->handleCallback($request);
        return $res->success && $res->order ? redirect()->route('checkout.success', $res->order->order_number) : redirect()->route('home')->with('error', $res->message ?: 'Nagad payment failed.');
    }

    public function sslcommerzRedirect(Order $order, PaymentService $payments)
    {
        $res = $payments->getGateway('sslcommerz')->createPayment($order);
        return $res->success ? redirect()->away((string) $res->redirectUrl) : redirect()->route('payment.failed', $order->id)->with('error', $res->message);
    }

    public function sslcommerzSuccess(Request $request, PaymentService $payments)
    {
        $res = $payments->getGateway('sslcommerz')->handleCallback($request);
        return $res->success && $res->order ? redirect()->route('checkout.success', $res->order->order_number) : redirect()->route('home')->with('error', $res->message ?: 'SSLCommerz payment failed.');
    }

    public function sslcommerzFail(Request $request)
    {
        $order = Order::query()->where('order_number', (string) $request->get('tran_id'))->first();
        return redirect()->route('payment.failed', $order?->id ?? 0)->with('error', 'SSLCommerz payment failed.');
    }

    public function sslcommerzCancel(Request $request)
    {
        $order = Order::query()->where('order_number', (string) $request->get('tran_id'))->first();
        return redirect()->route('payment.failed', $order?->id ?? 0)->with('error', 'SSLCommerz payment canceled.');
    }

    public function sslcommerzIpn(Request $request, PaymentService $payments): JsonResponse
    {
        $res = $payments->getGateway('sslcommerz')->handleWebhook($request);
        return response()->json(['success' => $res->success, 'message' => $res->message]);
    }

    public function genericCallback(string $gateway, Request $request, PaymentService $payments)
    {
        $res = $payments->getGateway($gateway)->handleCallback($request);
        return $res->success && $res->order ? redirect()->route('checkout.success', $res->order->order_number) : redirect()->route('home')->with('error', $res->message ?: 'Payment callback failed.');
    }

    public function failed(Order $order)
    {
        return view('frontend.checkout.failed', compact('order'));
    }
}
