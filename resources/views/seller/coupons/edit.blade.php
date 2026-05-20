@extends('seller.layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Edit coupon</h1>
<form method="post" action="{{ route('seller.coupons.update',$coupon->id) }}" class="max-w-xl space-y-4 bg-white p-6 rounded-xl border">@csrf @method('PUT')
  <div><label class="text-sm">Code</label><input name="code" value="{{ $coupon->code }}" class="w-full border rounded px-3 py-2 mt-1 font-mono" required></div>
  <div class="flex gap-4"><label><input type="radio" name="type" value="fixed" @checked($coupon->type==='fixed')> Fixed</label><label><input type="radio" name="type" value="percent" @checked($coupon->type==='percent')> %</label></div>
  <div><label class="text-sm">Amount</label><input type="number" step="0.01" name="amount" value="{{ $coupon->amount }}" class="w-full border rounded px-3 py-2 mt-1" required></div>
  <div><label class="text-sm">Minimum order</label><input type="number" step="0.01" name="minimum_order_amount" value="{{ $coupon->minimum_order_amount }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Applies to</label>
    <select name="applicable_to" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['all'=>'All my products','categories'=>'Categories','products'=>'Products'] as $k=>$label)
        <option value="{{ $k }}" @selected($coupon->applicable_to===$k)>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div><label class="text-sm">IDs (comma-separated)</label><input name="applicable_ids_helper" value="{{ implode(',', $coupon->applicable_ids ?? []) }}" class="w-full border rounded px-3 py-2 mt-1 text-xs"></div>
  <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Active</label>
  <button class="bg-teal-600 text-white px-4 py-2 rounded-lg">Update</button>
</form>
@endsection
