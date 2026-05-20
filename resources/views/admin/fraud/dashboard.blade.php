@extends('admin.layouts.app')
@section('title','Fraud Dashboard')
@section('content')
<div class="space-y-4">
    <div class="grid md:grid-cols-4 gap-3">
        <div class="card p-4">🚨 Flagged Today: <strong>{{ $flaggedToday }}</strong></div>
        <div class="card p-4">🚫 Blocked Today: <strong>{{ $blockedToday }}</strong></div>
        <div class="card p-4">📋 Total Flagged: <strong>{{ $totalFlagged }}</strong></div>
        <div class="card p-4">📋 Blacklisted: <strong>{{ $blacklist->flatten(1)->count() }}</strong></div>
    </div>
    @if($flaggedOrders->total() > 0)
        <div class="rounded border border-amber-300 bg-amber-50 p-3">⚠️ {{ $flaggedOrders->total() }} orders need review!</div>
    @endif

    <div class="card p-4 overflow-auto">
        <h3 class="font-semibold mb-2">Flagged Orders</h3>
        <table class="w-full text-sm">
            <thead><tr><th class="text-left p-2">Order</th><th class="text-left p-2">Customer</th><th class="text-left p-2">Amount</th><th class="text-left p-2">Risk</th><th class="text-left p-2">Flags</th><th class="text-left p-2">Actions</th></tr></thead>
            <tbody>
            @foreach($flaggedOrders as $order)
                @php($score = min(100, (int) $order->fraud_score))
                <tr class="border-t">
                    <td class="p-2">{{ $order->order_number }}</td>
                    <td class="p-2">{{ $order->shipping_name }}<br><span class="text-xs text-slate-500">{{ $order->shipping_phone }}</span></td>
                    <td class="p-2">৳{{ number_format((float) $order->total,2) }}</td>
                    <td class="p-2">
                        <div class="w-28 h-2 bg-slate-200 rounded overflow-hidden"><div class="h-full {{ $score>=80?'bg-red-500':($score>=60?'bg-orange-500':($score>=40?'bg-yellow-500':'bg-green-500')) }}" style="width: {{ $score }}%"></div></div>
                        <span class="text-xs">{{ $score }}</span>
                    </td>
                    <td class="p-2 text-xs">{{ collect($order->fraud_flags ?? [])->pluck('type')->join(', ') }}</td>
                    <td class="p-2 flex gap-2">
                        <form method="POST" action="{{ route('admin.fraud.clear', $order->id) }}">@csrf<button class="px-2 py-1 border rounded">✅ Clear</button></form>
                        <form method="POST" action="{{ route('admin.fraud.block', $order->id) }}">@csrf<button class="px-2 py-1 border rounded text-red-600">🚫 Block</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $flaggedOrders->links() }}</div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="card p-4">
            <h3 class="font-semibold mb-2">Blacklist Management</h3>
            <form method="POST" action="{{ route('admin.fraud.blacklist.add') }}" class="space-y-2">
                @csrf
                <select name="type" class="border rounded px-3 py-2 w-full">
                    @foreach(['phone_blacklist','email_blacklist','ip_blacklist','address_blacklist','area_blacklist'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                </select>
                <input type="text" name="value" class="border rounded px-3 py-2 w-full" placeholder="Value">
                <input type="text" name="notes" class="border rounded px-3 py-2 w-full" placeholder="Notes">
                <button class="btn-primary">+ Add to Blacklist</button>
            </form>
            <div class="mt-3 space-y-1 text-xs max-h-64 overflow-auto">
                @foreach($blacklist as $type => $items)
                    <div class="font-semibold mt-2">{{ $type }}</div>
                    @foreach($items as $item)
                        <div class="flex justify-between border rounded px-2 py-1">
                            <span>{{ $item->value }}</span>
                            <form method="POST" action="{{ route('admin.fraud.blacklist.remove') }}">@csrf
                                <input type="hidden" name="type" value="{{ $item->rule_type }}">
                                <input type="hidden" name="value" value="{{ $item->value }}">
                                <button class="text-red-600">Remove</button>
                            </form>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="card p-4">
            <h3 class="font-semibold mb-2">Fraud Settings</h3>
            <form method="POST" action="{{ route('admin.fraud.settings.save') }}" class="space-y-2">
                @csrf
                <label class="flex gap-2"><input type="checkbox" name="fraud_auto_block" value="1" @checked(setting('fraud_auto_block', true))> Auto block enabled</label>
                <div><label class="text-sm">Auto-block score threshold</label><input type="range" name="fraud_auto_block_score" min="0" max="100" value="{{ setting('fraud_auto_block_score', 80) }}" class="w-full"></div>
                <input type="number" name="fraud_cod_limit" class="border rounded px-3 py-2 w-full" value="{{ setting('fraud_cod_limit', 5000) }}" placeholder="COD limit">
                <input type="number" name="fraud_max_orders_per_hour" class="border rounded px-3 py-2 w-full" value="{{ setting('fraud_max_orders_per_hour', 3) }}" placeholder="Max orders per hour">
                <input type="number" name="fraud_new_account_threshold" class="border rounded px-3 py-2 w-full" value="{{ setting('fraud_new_account_threshold', 3000) }}" placeholder="New account threshold">
                <button class="btn-primary">Save Fraud Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
