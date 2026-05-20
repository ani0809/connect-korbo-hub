@extends('admin.layouts.app')
@section('title','Create coupon')
@section('content')
<form method="post" action="{{ route('admin.coupons.store') }}" class="max-w-4xl space-y-6">@csrf
  <div class="grid md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm">Code</label>
      <div class="flex gap-2 mt-1">
        <input name="code" id="coupon-code" class="w-full border rounded px-3 py-2 font-mono uppercase" required pattern="[A-Za-z0-9_-]+" maxlength="50">
        <button type="button" id="btn-gen-code" class="btn-secondary whitespace-nowrap">Auto</button>
      </div>
    </div>
    <div>
      <label class="text-sm">Seller (optional)</label>
      <select name="seller_id" class="w-full border rounded px-3 py-2 mt-1"><option value="">—</option>@foreach($sellers as $s)<option value="{{ $s->id }}">{{ $s->shop_name }}</option>@endforeach</select>
    </div>
  </div>
  <div class="flex gap-6">
    <label class="flex items-center gap-2"><input type="radio" name="type" value="fixed" checked> Fixed</label>
    <label class="flex items-center gap-2"><input type="radio" name="type" value="percent"> Percent</label>
  </div>
  <div><label class="text-sm">Amount</label><input type="number" step="0.01" name="amount" class="w-full border rounded px-3 py-2 mt-1" required></div>
  <div><label class="text-sm">Max discount (percent only)</label><input type="number" step="0.01" name="maximum_discount" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Minimum order</label><input type="number" step="0.01" name="minimum_order_amount" value="0" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Usage limit (empty = unlimited)</label><input type="number" name="usage_limit" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Per customer</label><input type="number" name="usage_per_user" value="1" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Starts</label><input type="datetime-local" name="starts_at" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Expires</label><input type="datetime-local" name="expires_at" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div>
    <label class="text-sm">Applies to</label>
    <select name="applicable_to" id="applicable-to" class="w-full border rounded px-3 py-2 mt-1">
      <option value="all">All products</option>
      <option value="categories">Categories</option>
      <option value="products">Products</option>
      <option value="sellers">Sellers</option>
    </select>
  </div>
  <div id="applicable-ids-wrap" class="hidden">
    <label class="text-sm">Select IDs (hold Ctrl)</label>
    <select name="applicable_ids[]" id="applicable-ids" multiple class="w-full border rounded px-3 py-2 mt-1 h-40"></select>
  </div>
  <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active</label>
  <button type="submit" class="btn-primary">Save</button>
</form>
<script>
  const categories = @json($categories->map(fn($c) => ['id'=>$c->id,'name'=>$c->name]));
  const products = @json($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name]));
  const sellers = @json($sellers->map(fn($s) => ['id'=>$s->id,'name'=>$s->shop_name]));
  document.getElementById('applicable-to')?.addEventListener('change', function() {
    const w = document.getElementById('applicable-ids-wrap');
    const sel = document.getElementById('applicable-ids');
    sel.innerHTML = '';
    if (this.value === 'all') { w.classList.add('hidden'); return; }
    w.classList.remove('hidden');
    const src = this.value === 'categories' ? categories : (this.value === 'products' ? products : sellers);
    src.forEach(function(o) { const opt = document.createElement('option'); opt.value = o.id; opt.textContent = o.name; sel.appendChild(opt); });
  });
  document.getElementById('btn-gen-code')?.addEventListener('click', async function() {
    const r = await fetch('{{ route('admin.coupons.generate-code') }}');
    const j = await r.json();
    document.getElementById('coupon-code').value = j.code || '';
  });
</script>
@endsection
