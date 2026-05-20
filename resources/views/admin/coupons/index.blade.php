@extends('admin.layouts.app')
@section('title','Coupons')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <div class="text-sm text-slate-600">Total: {{ $stats['total'] }} | Active: {{ $stats['active'] }} | Discount given (orders): {{ currency_format($stats['discount_sum']) }}</div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.coupons.create') }}" class="btn-primary">+ Create coupon</a>
    <a href="{{ route('admin.coupons.import-template') }}" class="btn-secondary text-sm">Download CSV template</a>
  </div>
</div>

<details class="mb-4 bg-slate-50 border rounded-xl p-4">
  <summary class="cursor-pointer font-medium text-slate-800">Bulk generate & import</summary>
  <div class="mt-4 grid md:grid-cols-2 gap-6">
    <div>
      <h3 class="text-sm font-semibold mb-2">Bulk generate (CSV download)</h3>
      <form method="post" action="{{ route('admin.coupons.bulk-generate') }}" class="space-y-2 text-sm">
        @csrf
        <label class="block">Count (max 100)<input type="number" name="count" value="10" min="1" max="100" class="border rounded px-2 py-1 w-full mt-0.5"></label>
        <label class="block">Prefix (optional)<input type="text" name="prefix" maxlength="20" class="border rounded px-2 py-1 w-full mt-0.5" placeholder="SALE"></label>
        <label class="block">Type
          <select name="type" class="border rounded px-2 py-1 w-full mt-0.5"><option value="percent">Percent</option><option value="fixed">Fixed</option></select>
        </label>
        <label class="block">Amount<input type="number" step="0.01" name="amount" value="10" min="0.01" class="border rounded px-2 py-1 w-full mt-0.5"></label>
        <label class="block">Min. order (optional)<input type="number" step="0.01" name="minimum_order_amount" value="0" min="0" class="border rounded px-2 py-1 w-full mt-0.5"></label>
        <label class="block">Expires at (optional)<input type="datetime-local" name="expires_at" class="border rounded px-2 py-1 w-full mt-0.5"></label>
        <button type="submit" class="btn-primary text-sm">Generate & download CSV</button>
      </form>
    </div>
    <div>
      <h3 class="text-sm font-semibold mb-2">Import from CSV</h3>
      <form id="coupon-import-form" class="space-y-2 text-sm" enctype="multipart/form-data">
        @csrf
        <label class="block">CSV file<input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm"></label>
        <button type="submit" class="btn-secondary text-sm">Import</button>
      </form>
      <p id="coupon-import-result" class="mt-2 text-xs text-slate-600"></p>
    </div>
  </div>
</details>

@push('scripts')
<script>
document.getElementById('coupon-import-form')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const fd = new FormData(this);
  const el = document.getElementById('coupon-import-result');
  el.textContent = 'Importing…';
  try {
    const r = await fetch(@json(route('admin.coupons.import')), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    const j = await r.json();
    el.textContent = j.message || JSON.stringify(j);
    if (j.errors && j.errors.length) el.textContent += ' ' + j.errors.slice(0, 5).join(' | ');
  } catch (err) {
    el.textContent = 'Import failed.';
  }
});
</script>
@endpush

<form method="get" class="flex flex-wrap gap-2 mb-4">
  <input type="search" name="search" value="{{ request('search') }}" placeholder="Code" class="border rounded px-3 py-2 text-sm">
  <select name="type" class="border rounded px-3 py-2 text-sm"><option value="">Type</option><option value="fixed" @selected(request('type')==='fixed')>Fixed</option><option value="percent" @selected(request('type')==='percent')>Percent</option></select>
  <select name="status" class="border rounded px-3 py-2 text-sm"><option value="">Status</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option><option value="expired" @selected(request('status')==='expired')>Expired</option></select>
  <select name="seller_id" class="border rounded px-3 py-2 text-sm"><option value="">Seller</option>@foreach($sellers as $s)<option value="{{ $s->id }}" @selected((string)request('seller_id')===(string)$s->id)>{{ $s->shop_name }}</option>@endforeach</select>
  <button type="submit" class="btn-secondary text-sm">Filter</button>
</form>

<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-3">Code</th><th class="p-3">Type</th><th class="p-3">Amount</th><th class="p-3">Uses</th><th class="p-3">Until</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>
      @foreach($coupons as $c)
        <tr class="border-t">
          <td class="p-3 font-mono font-semibold">{{ $c->code }}</td>
          <td class="p-3">{{ $c->type }}</td>
          <td class="p-3">{{ $c->type==='percent' ? $c->amount.'%' : currency_format((float)$c->amount) }}</td>
          <td class="p-3">{{ $c->orders_count }} @if($c->usage_limit)/ {{ $c->usage_limit }}@endif</td>
          <td class="p-3">{{ $c->expires_at ? $c->expires_at->format('j/n/Y g:i A') : '—' }}</td>
          <td class="p-3">{{ $c->is_active ? 'Active' : 'Off' }}</td>
          <td class="p-3 flex flex-wrap gap-1">
            <a href="{{ route('admin.coupons.stats',$c->id) }}" class="text-blue-600">Stats</a>
            <a href="{{ route('admin.coupons.edit',$c->id) }}" class="text-slate-700">Edit</a>
            <form method="post" action="{{ route('admin.coupons.toggle',$c->id) }}" class="inline">@csrf<button class="text-amber-700">Toggle</button></form>
            <form method="post" action="{{ route('admin.coupons.destroy',$c->id) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $coupons->withQueryString()->links() }}
@endsection
