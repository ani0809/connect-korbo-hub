@extends('seller.layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Create coupon</h1>
<form method="post" action="{{ route('seller.coupons.store') }}" class="max-w-xl space-y-4 bg-white p-6 rounded-xl border">@csrf
  <div><label class="text-sm">Code</label><input name="code" class="w-full border rounded px-3 py-2 mt-1 font-mono uppercase" required></div>
  <div class="flex gap-4"><label><input type="radio" name="type" value="fixed" checked> Fixed</label><label><input type="radio" name="type" value="percent"> %</label></div>
  <div><label class="text-sm">Amount</label><input type="number" step="0.01" name="amount" class="w-full border rounded px-3 py-2 mt-1" required></div>
  <div><label class="text-sm">Minimum order</label><input type="number" step="0.01" name="minimum_order_amount" value="0" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Applies to</label>
    <select name="applicable_to" class="w-full border rounded px-3 py-2 mt-1">
      <option value="all">All my products</option>
      <option value="categories">Categories</option>
      <option value="products">Products</option>
    </select>
  </div>
  <div><label class="text-sm">IDs (comma-separated, when not “all”)</label><input name="applicable_ids_helper" class="w-full border rounded px-3 py-2 mt-1 text-xs" placeholder="e.g. 1,2,3"></div>
  <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active</label>
  <button class="bg-teal-600 text-white px-4 py-2 rounded-lg">Save</button>
</form>
@endsection
