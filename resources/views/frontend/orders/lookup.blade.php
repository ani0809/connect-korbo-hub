@extends('frontend.layouts.app')
@section('title', 'Order Lookup')
@section('content')
<div class="container py-8" style="max-width:560px">
  <div class="card p-6">
    <h2 class="mb-2 text-2xl font-heading font-semibold">Track Guest Order</h2>
    <p class="text-sm text-[hsl(var(--muted-foreground))] mb-4">Enter your email and order number.</p>
  <form method="post" action="{{ route('orders.lookup') }}" class="space-y-4">
    @csrf
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-input w-full" required value="{{ old('email') }}">
    </div>
    <div class="mb-3">
      <label class="form-label">Order Number</label>
      <input type="text" name="order_number" class="form-input w-full" required value="{{ old('order_number') }}" placeholder="#ORD-2026-00001">
    </div>
    <button class="btn-primary w-full">Lookup Order</button>
  </form>
  </div>
</div>
@endsection

