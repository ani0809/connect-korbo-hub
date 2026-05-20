@extends('frontend.layouts.app')
@section('title', 'Order Success')
@section('content')
<div class="container py-12 max-w-3xl">
    <div class="card p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-[color:color-mix(in_srgb,var(--color-success)_18%,transparent)] text-[var(--color-success)] flex items-center justify-center text-3xl">✓</div>
        <h1 class="text-3xl font-heading font-semibold mt-4">Thank You! Order Placed Successfully</h1>
        <p class="text-[hsl(var(--muted-foreground))] mt-2">Order <strong>{{ $order->order_number }}</strong> has been placed.</p>
        <div class="mt-6 grid sm:grid-cols-3 gap-2 text-sm">
            <div class="rounded-lg border border-[hsl(var(--border))] p-3 bg-[hsl(var(--background))]">
                <div class="font-medium">Order Received</div>
                <div class="text-xs text-[hsl(var(--muted-foreground))]">@datetime(now())</div>
            </div>
            <div class="rounded-lg border border-[hsl(var(--border))] p-3 bg-[hsl(var(--background))]">
                <div class="font-medium">Processing</div>
                <div class="text-xs text-[hsl(var(--muted-foreground))]">We are preparing your package</div>
            </div>
            <div class="rounded-lg border border-[hsl(var(--border))] p-3 bg-[hsl(var(--background))]">
                <div class="font-medium">Shipped</div>
                <div class="text-xs text-[hsl(var(--muted-foreground))]">Tracking updates will appear in account</div>
            </div>
        </div>
        <div class="mt-5 flex justify-center gap-3 flex-wrap">
            @auth
                <a href="{{ route('account.orders') }}" class="btn-secondary">Track Order</a>
            @endauth
            @if(\Illuminate\Support\Facades\Route::has('frontend.invoice.download'))
            <a href="{{ route('frontend.invoice.download', $order->id) }}" class="btn-secondary">Download Invoice</a>
            @endif
            <a href="{{ url('/shop') }}" class="btn-primary px-4 py-2">Continue Shopping</a>
        </div>
    </div>
</div>
@endsection
