@extends('admin.layouts.app')
@section('title', 'Shipments')
@section('content')
<div class="space-y-4">
    <div class="grid md:grid-cols-4 gap-3">
        <div class="card p-4"><div class="text-xs text-slate-500">Pending</div><div class="text-2xl font-bold">{{ $stats['pending'] }}</div></div>
        <div class="card p-4"><div class="text-xs text-slate-500">In Transit</div><div class="text-2xl font-bold">{{ $stats['in_transit'] }}</div></div>
        <div class="card p-4"><div class="text-xs text-slate-500">Delivered</div><div class="text-2xl font-bold">{{ $stats['delivered'] }}</div></div>
        <div class="card p-4"><div class="text-xs text-slate-500">Returned</div><div class="text-2xl font-bold">{{ $stats['returned'] }}</div></div>
    </div>

    <div class="card p-4 space-y-3">
        <div class="flex flex-wrap gap-2 items-end">
            <form method="GET" class="flex flex-wrap gap-2 items-end">
                <input type="text" name="search" value="{{ request('search') }}" class="border rounded px-3 py-2" placeholder="Order / tracking / phone">
                <select name="courier" class="border rounded px-3 py-2">
                    <option value="">All couriers</option>
                    <option value="pathao" @selected(request('courier')==='pathao')>Pathao</option>
                    <option value="steadfast" @selected(request('courier')==='steadfast')>Steadfast</option>
                    <option value="redx" @selected(request('courier')==='redx')>RedX</option>
                </select>
                <select name="status" class="border rounded px-3 py-2">
                    <option value="">All status</option>
                    @foreach(['pending','created','picked','in_transit','out_for_delivery','delivered','returned','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2">
                <button class="btn-primary" type="submit">Filter</button>
            </form>
        </div>

        <form id="bulk-ship-form" class="flex gap-2 items-center">
            @csrf
            <select name="courier" class="border rounded px-3 py-2">
                <option value="steadfast">Ship selected via Steadfast</option>
                <option value="pathao">Ship selected via Pathao</option>
                <option value="redx">Ship selected via RedX</option>
            </select>
            <input type="number" step="0.1" name="weight" value="0.5" class="border rounded px-3 py-2 w-24">
            <button type="button" id="bulk-ship-btn" class="btn-primary">📦 Ship Selected Orders</button>
        </form>
    </div>

    <div class="card overflow-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-3 text-left"><input type="checkbox" id="select-all"></th>
                    <th class="p-3 text-left">Order</th>
                    <th class="p-3 text-left">Customer</th>
                    <th class="p-3 text-left">Courier</th>
                    <th class="p-3 text-left">Tracking</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">COD</th>
                    <th class="p-3 text-left">Created</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipments as $shipment)
                <tr class="border-t">
                    <td class="p-3"><input type="checkbox" class="ship-check" value="{{ $shipment->order_id }}"></td>
                    <td class="p-3">#{{ $shipment->order?->order_number }}</td>
                    <td class="p-3">{{ $shipment->order?->shipping_name }}<br><span class="text-xs text-slate-500">{{ $shipment->order?->shipping_phone }}</span></td>
                    <td class="p-3"><span class="px-2 py-1 rounded bg-slate-100">{{ ucfirst($shipment->courier) }}</span></td>
                    <td class="p-3 font-mono">{{ $shipment->tracking_code ?? '-' }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded bg-blue-50 text-blue-700">{{ str_replace('_',' ', $shipment->status) }}</span></td>
                    <td class="p-3">৳{{ number_format((float) $shipment->cod_amount, 2) }}</td>
                    <td class="p-3">@datetime($shipment->created_at)</td>
                    <td class="p-3 flex gap-2">
                        <button class="px-2 py-1 border rounded btn-track" data-id="{{ $shipment->id }}">🔍 Track</button>
                        <a href="{{ route('admin.shipments.label', $shipment->id) }}" target="_blank" class="px-2 py-1 border rounded">🖨️ Label</a>
                        <form method="POST" action="{{ route('admin.shipments.cancel', $shipment->id) }}">@csrf<button class="px-2 py-1 border rounded text-red-600">✕ Cancel</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">{{ $shipments->links() }}</div>
    </div>

    <div id="tracking-modal" style="display:none" class="fixed inset-0 bg-black/40 items-center justify-center p-4">
        <div class="bg-white rounded-xl p-5 w-full max-w-lg">
            <div class="font-semibold mb-3">Tracking: <span id="modal-tracking"></span></div>
            <div id="modal-events"></div>
            <div class="text-right mt-3"><button id="close-tracking-modal" class="btn-secondary">Close</button></div>
        </div>
    </div>
</div>
@vite('resources/js/admin/shipments.js')
@endsection
