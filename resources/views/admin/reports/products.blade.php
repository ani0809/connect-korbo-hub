@extends('admin.layouts.app')
@section('title','Product Report')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl overflow-x-auto p-4"><h2 class="panel-title">Top Products</h2><table class="tbl"><thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead><tbody>@foreach($data['top_products'] as $p)<tr><td><strong>{{ $p->product_name }}</strong></td><td>{{ $p->total_qty }}</td><td>{{ currency_format((float)$p->total_revenue) }}</td></tr>@endforeach</tbody></table></div>
  <div class="bg-white border rounded-xl p-4"><h2 class="panel-title">Low Stock Alerts</h2><div class="grid md:grid-cols-3 gap-3">@foreach($data['low_stock'] as $p)<div class="border rounded-xl p-3"><div class="font-medium">{{ $p->name }}</div><div class="text-xs text-gray-500">Stock running low</div></div>@endforeach</div></div>
</div>
@endsection
