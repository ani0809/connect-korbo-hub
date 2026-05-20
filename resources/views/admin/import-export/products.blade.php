@extends('admin.layouts.app')
@section('title','Import / Export')
@section('content')
<div class="grid lg:grid-cols-2 gap-8">
  <div>
    <h2 class="font-semibold mb-2">Import products</h2>
    <p class="text-sm text-slate-600 mb-3">Required columns: <code>name</code>, <code>category</code>, <code>price</code>, <code>stock</code>. Optional: sku, brand, sale_price, unit, weight, description, short_description, image_url, published.</p>
    <a href="{{ route('admin.import-export.products.template') }}" class="btn-secondary text-sm mb-4 inline-block">Download CSV template</a>
    <div id="import-drop-zone" class="border-2 border-dashed rounded-xl p-8 text-center text-sm text-slate-600 mb-3">Drop CSV here or click to browse</div>
    <form id="product-import-form" action="{{ route('admin.import-export.products.import') }}" method="post" enctype="multipart/form-data">
      @csrf
      <input type="file" id="import-csv-input" name="csv_file" accept=".csv,.txt" class="hidden">
      <label class="flex items-center gap-2 text-sm mb-2"><input type="checkbox" name="update_existing" value="1"> Update existing (match by SKU)</label>
      <button type="submit" class="btn-primary">Import products</button>
    </form>
    <pre id="import-result" class="mt-4 text-xs bg-slate-950 text-green-100 p-3 rounded whitespace-pre-wrap min-h-[80px]"></pre>
  </div>
  <div class="space-y-4">
    <h2 class="font-semibold">Export products</h2>
    <form method="post" action="{{ route('admin.import-export.products.export') }}" class="space-y-2 text-sm">
      @csrf
      <label class="block">Category <select name="category" class="w-full border rounded px-2 py-1"><option value="">All</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
      <label class="block">Seller <select name="seller" class="w-full border rounded px-2 py-1"><option value="">All</option>@foreach($sellers as $s)<option value="{{ $s->id }}">{{ $s->shop_name }}</option>@endforeach</select></label>
      <label class="block">Status <select name="status" class="w-full border rounded px-2 py-1"><option value="">All</option><option value="published">Published</option><option value="unpublished">Unpublished</option></select></label>
      <label class="block">Type <select name="type" class="w-full border rounded px-2 py-1"><option value="">All</option><option value="simple">Simple</option><option value="variable">Variable</option><option value="digital">Digital</option></select></label>
      <div class="flex gap-2">
        <label class="flex-1">From <input type="date" name="date_from" class="w-full border rounded px-2 py-1"></label>
        <label class="flex-1">To <input type="date" name="date_to" class="w-full border rounded px-2 py-1"></label>
      </div>
      <button type="submit" class="btn-primary">Export CSV</button>
    </form>
    <h2 class="font-semibold pt-4">Export orders</h2>
    <form method="post" action="{{ route('admin.import-export.orders.export') }}" class="space-y-2 text-sm">
      @csrf
      <div class="flex gap-2">
        <label class="flex-1">From <input type="date" name="from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="w-full border rounded px-2 py-1"></label>
        <label class="flex-1">To <input type="date" name="to" value="{{ now()->format('Y-m-d') }}" class="w-full border rounded px-2 py-1"></label>
      </div>
      <label class="block">Order status <select name="status" class="w-full border rounded px-2 py-1"><option value="all">All</option><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select></label>
      <button type="submit" class="btn-secondary">Export orders CSV</button>
    </form>
    <h2 class="font-semibold pt-4">Export customers</h2>
    <form method="post" action="{{ route('admin.import-export.customers.export') }}">@csrf<button type="submit" class="btn-secondary">Export customers CSV</button></form>
  </div>
</div>
@endsection
