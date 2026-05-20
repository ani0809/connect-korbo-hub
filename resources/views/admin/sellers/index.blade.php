@extends('admin.layouts.app')
@section('title','Sellers')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-gray-100">
      <div>
        <h3>Sellers</h3>
        <p>Review seller performance, balances, and account status.</p>
      </div>
      <div class="flex gap-2">
        <a href="?status=" class="btn-secondary">All</a>
        <a href="?status=active" class="btn-secondary">Active</a>
        <a href="?status=pending" class="btn-secondary">Pending</a>
        <a href="?status=suspended" class="btn-secondary">Suspended</a>
      </div>
    </div>
  </div>
  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="border-b text-left"><th class="p-3">Shop</th><th>Seller</th><th>Products</th><th>Orders</th><th>Sales</th><th>Balance</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      @foreach($sellers as $s)
        <tr class="border-b">
          <td class="p-3"><strong>{{ $s->shop_name }}</strong></td>
          <td>{{ $s->user?->name }}</td>
          <td>{{ $s->products_count }}</td>
          <td>{{ $s->orders_count }}</td>
          <td>{{ currency_format((float)$s->total_sales) }}</td>
          <td>{{ currency_format((float)$s->balance) }}</td>
          <td>{{ number_format((float)$s->rating,1) }}</td>
          <td><span class="status-badge {{ 'status-' . $s->status }}">{{ ucfirst($s->status) }}</span></td>
          <td><a class="text-blue-600" href="{{ route('admin.sellers.show',$s->id) }}">View</a> @if($s->status==='pending')<a class="text-green-600" href="{{ route('admin.sellers.approve',$s->id) }}">Approve</a>@endif @if($s->status==='active')<a class="text-red-600" href="{{ route('admin.sellers.suspend',$s->id) }}">Suspend</a>@endif</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  {{ $sellers->links() }}
</div>
@endsection
