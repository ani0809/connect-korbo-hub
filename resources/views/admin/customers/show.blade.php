@extends('admin.layouts.app')
@section('title', $customer->name)
@section('content')
@php use App\Support\StaffPermissions; @endphp
<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-3">Recent orders</h3>
      <table class="w-full text-sm">
        <thead><tr class="text-left border-b"><th>#</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($customer->orders as $o)
          <tr class="border-b">
            <td class="py-2"><a href="{{ route('admin.orders.show', $o->id) }}" class="text-blue-600">{{ $o->order_number }}</a></td>
            <td>{{ currency_format((float) $o->total) }}</td>
            <td>{{ $o->order_status }}</td>
            <td class="text-xs">@datetime($o->created_at)</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-3">Wishlist</h3>
      <div class="flex flex-wrap gap-3">
        @foreach($customer->wishlists as $w)
          @if($w->product)
            <div class="text-xs w-24">
              @if($w->product->thumbnail)
                <img src="{{ asset('storage/'.$w->product->thumbnail) }}" class="w-full rounded object-cover h-16 bg-slate-100" alt="">
              @endif
              <div class="mt-1 truncate">{{ $w->product->name }}</div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-3">Addresses</h3>
      <ul class="text-sm space-y-2">
        @forelse($customer->addresses as $a)
          <li class="border rounded p-2">{{ $a->address_line1 }} · {{ $a->city }} · {{ $a->country }}</li>
        @empty
          <li class="text-slate-500">No addresses</li>
        @endforelse
      </ul>
    </div>
  </div>
  <div class="space-y-4">
    <div class="bg-white border rounded-xl p-4 sticky top-4">
      <div class="text-lg font-semibold">{{ $customer->name }}</div>
      <div class="text-sm text-slate-600">{{ $customer->email }}</div>
      <div class="text-sm">{{ $customer->phone }}</div>
      <div class="text-xs text-slate-500 mt-2">Joined @datetime($customer->created_at)</div>
      <div class="mt-2">Status: <strong>{{ $customer->status }}</strong></div>
      @if(StaffPermissions::has(auth()->user(), 'edit_customers'))
        <form method="post" action="{{ route('admin.customers.toggle-status', $customer->id) }}" class="mt-3">@csrf
          <button type="submit" class="btn-secondary text-sm w-full">{{ $customer->status === 'active' ? 'Ban account' : 'Unban' }}</button>
        </form>
      @endif
      <div class="mt-4 text-sm space-y-1">
        <div>Orders: {{ $customer->orders_count }}</div>
        <div>Total spent: {{ currency_format((float) ($customer->total_spent ?? 0)) }}</div>
        <div>Avg order: {{ currency_format((float) $avgOrder) }}</div>
        <div>Last order: {{ $lastOrder?->created_at?->diffForHumans() ?? '—' }}</div>
      </div>
      <div class="mt-4 border-t pt-4 text-sm">
        <div class="font-medium">Wallet</div>
        <div>{{ currency_format((float) $walletBalance) }}</div>
        @if(StaffPermissions::has(auth()->user(), 'edit_customers'))
          <form method="post" action="{{ route('admin.customers.wallet', $customer->id) }}" class="mt-2 space-y-2">@csrf
            <input type="number" step="0.01" name="amount" class="w-full border rounded px-2 py-1" placeholder="Amount" required>
            <select name="type" class="w-full border rounded px-2 py-1"><option value="add">Add</option><option value="deduct">Deduct</option></select>
            <input name="reason" class="w-full border rounded px-2 py-1" placeholder="Reason">
            <button type="submit" class="btn-secondary text-sm w-full">Apply</button>
          </form>
        @endif
      </div>
      <div class="mt-4 border-t pt-4 text-sm">
        <div class="font-medium">Club points</div>
        <div>{{ number_format($pointsBalance) }} pts</div>
        @if(StaffPermissions::has(auth()->user(), 'edit_customers'))
          <form method="post" action="{{ route('admin.customers.points', $customer->id) }}" class="mt-2 space-y-2">@csrf
            <input type="number" name="points" class="w-full border rounded px-2 py-1" placeholder="Points" required>
            <select name="type" class="w-full border rounded px-2 py-1"><option value="add">Add</option><option value="deduct">Deduct</option></select>
            <input name="reason" class="w-full border rounded px-2 py-1" placeholder="Reason">
            <button type="submit" class="btn-secondary text-sm w-full">Apply</button>
          </form>
        @endif
      </div>
      @if((config('app.env') !== 'production' || config('shop.allow_impersonation')) && StaffPermissions::has(auth()->user(), 'edit_customers'))
        <form method="post" action="{{ route('admin.customers.login-as', $customer->id) }}" class="mt-4">@csrf
          <button type="submit" class="btn-secondary text-sm w-full">Login as customer</button>
        </form>
      @endif
      <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn-primary inline-flex justify-center w-full mt-3 text-center">Edit profile</a>
    </div>
  </div>
</div>
@endsection
