@extends('admin.layouts.app')
@section('title','Customer Report')
@section('content')
<div class="space-y-4">
  <div class="grid md:grid-cols-2 gap-3">
    <div class="card metric-card"><h3>New Customers</h3><div class="big">{{ $data['newCustomers'] }}</div></div>
    <div class="card metric-card"><h3>Returning Customers</h3><div class="big">{{ $data['returningCount'] }}</div></div>
  </div>
  <div class="bg-white border rounded-xl overflow-x-auto p-4"><h2 class="panel-title">Top Customers</h2><table class="tbl"><thead><tr><th>Customer</th><th>Orders</th><th>Spent</th></tr></thead><tbody>@foreach($data['topCustomers'] as $c)<tr><td><strong>{{ $c->user->name ?? 'N/A' }}</strong></td><td>{{ $c->orders }}</td><td>{{ currency_format((float)$c->spent) }}</td></tr>@endforeach</tbody></table></div>
</div>
@endsection
