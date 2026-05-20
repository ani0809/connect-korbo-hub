@extends('admin.layouts.app')
@section('title','Edit coupon')
@section('content')
@php $selIds = $coupon->applicable_ids ?? []; @endphp
<form method="post" action="{{ route('admin.coupons.update',$coupon->id) }}" class="max-w-4xl space-y-6">@csrf @method('PUT')
  <div><label class="text-sm">Code</label><input name="code" value="{{ $coupon->code }}" class="w-full border rounded px-3 py-2 mt-1 font-mono" required></div>
  <div><label class="text-sm">Seller</label><select name="seller_id" class="w-full border rounded px-3 py-2 mt-1"><option value="">—</option>@foreach($sellers as $s)<option value="{{ $s->id }}" @selected($coupon->seller_id==$s->id)>{{ $s->shop_name }}</option>@endforeach</select></div>
  <div class="flex gap-6">
    <label class="flex items-center gap-2"><input type="radio" name="type" value="fixed" @checked($coupon->type==='fixed')> Fixed</label>
    <label class="flex items-center gap-2"><input type="radio" name="type" value="percent" @checked($coupon->type==='percent')> Percent</label>
  </div>
  <div><label class="text-sm">Amount</label><input type="number" step="0.01" name="amount" value="{{ $coupon->amount }}" class="w-full border rounded px-3 py-2 mt-1" required></div>
  <div><label class="text-sm">Max discount</label><input type="number" step="0.01" name="maximum_discount" value="{{ $coupon->maximum_discount }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Minimum order</label><input type="number" step="0.01" name="minimum_order_amount" value="{{ $coupon->minimum_order_amount }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Usage limit</label><input type="number" name="usage_limit" value="{{ $coupon->usage_limit }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Per customer</label><input type="number" name="usage_per_user" value="{{ $coupon->usage_per_user }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Starts</label><input type="datetime-local" name="starts_at" value="{{ $coupon->starts_at?->format('Y-m-d\TH:i') }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Expires</label><input type="datetime-local" name="expires_at" value="{{ $coupon->expires_at?->format('Y-m-d\TH:i') }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div>
    <label class="text-sm">Applies to</label>
    <select name="applicable_to" id="applicable-to" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['all'=>'All products','categories'=>'Categories','products'=>'Products','sellers'=>'Sellers'] as $k=>$label)
        <option value="{{ $k }}" @selected($coupon->applicable_to===$k)>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div id="applicable-ids-wrap" class="{{ $coupon->applicable_to==='all' ? 'hidden' : '' }}">
    <label class="text-sm">Selection</label>
    <select name="applicable_ids[]" id="applicable-ids" multiple class="w-full border rounded px-3 py-2 mt-1 h-40"></select>
  </div>
  <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Active</label>
  <button type="submit" class="btn-primary">Update</button>
</form>
<script>
  const categories = @json($categories->map(fn($c) => ['id'=>$c->id,'name'=>$c->name]));
  const products = @json($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name]));
  const sellers = @json($sellers->map(fn($s) => ['id'=>$s->id,'name'=>$s->shop_name]));
  const selected = @json($selIds);
  function fillOptions(mode) {
    const sel = document.getElementById('applicable-ids');
    const w = document.getElementById('applicable-ids-wrap');
    sel.innerHTML = '';
    if (mode === 'all') { w.classList.add('hidden'); return; }
    w.classList.remove('hidden');
    const src = mode === 'categories' ? categories : (mode === 'products' ? products : sellers);
    src.forEach(function(o) {
      const opt = document.createElement('option');
      opt.value = o.id; opt.textContent = o.name;
      if (selected.map(Number).includes(Number(o.id))) opt.selected = true;
      sel.appendChild(opt);
    });
  }
  document.getElementById('applicable-to')?.addEventListener('change', function() { fillOptions(this.value); });
  fillOptions(document.getElementById('applicable-to').value);
</script>
@endsection
