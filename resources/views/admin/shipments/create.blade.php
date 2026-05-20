@extends('admin.layouts.app')
@section('title','Create Shipment')
@section('content')
<div class="max-w-3xl space-y-4">
    <div class="card p-4">
        <h2 class="font-semibold text-lg mb-2">Ship Order #{{ $order->order_number }}</h2>
        <p class="text-sm text-slate-500">{{ $order->shipping_name }} — {{ $order->shipping_phone }} — {{ $order->shipping_city }}</p>
    </div>

    <form method="POST" class="card p-4 space-y-3">
        @csrf
        <div>
            <label class="block text-sm mb-1">Courier</label>
            <select name="courier" class="border rounded px-3 py-2 w-full">
                @foreach($couriers as $name => $driver)
                    <option value="{{ $name }}" @selected($recommended===$name)>{{ ucfirst($name) }}</option>
                @endforeach
                <option value="manual">Manual</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm mb-1">Weight (kg)</label>
                <input type="number" step="0.1" min="0.1" name="weight" value="{{ old('weight', setting('courier_default_weight', 0.5)) }}" class="border rounded px-3 py-2 w-full">
            </div>
            <div>
                <label class="block text-sm mb-1">Delivery Type</label>
                <select name="delivery_type" class="border rounded px-3 py-2 w-full">
                    <option value="regular">Regular</option>
                    <option value="express">Express</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm mb-1">Zone (optional)</label>
            <input type="text" name="zone" class="border rounded px-3 py-2 w-full" placeholder="Dhaka / Mirpur">
        </div>
        <button class="btn-primary">Create Shipment</button>
    </form>
</div>
@endsection
