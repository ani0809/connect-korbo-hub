@include('frontend.layouts.header')
<div class="container py-10 max-w-xl"><div class="bg-white border rounded-2xl p-8 text-center"><h1 class="text-2xl font-semibold">Payment Failed</h1><p class="text-gray-600 mt-2">Order {{ $order->order_number }} could not be paid.</p><div class="mt-4 flex justify-center gap-2"><a href="{{ route('checkout.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Retry Checkout</a><a href="{{ route('cart.index') }}" class="border px-4 py-2 rounded">Change Method</a></div></div></div>
@include('frontend.layouts.footer')
