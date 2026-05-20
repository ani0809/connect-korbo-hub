@extends('seller.layouts.app')
@section('content')
<div class="space-y-4"><div class="flex items-center justify-between"><h1 class="text-xl font-semibold">My Products</h1></div><div class="bg-white border rounded-xl overflow-auto"><table class="w-full text-sm"><thead><tr class="border-b text-left"><th class="p-3">Product</th><th>Type</th><th>Category</th><th>Stock</th><th>Status</th><th>Approved</th></tr></thead><tbody>@foreach($products as $p)<tr class="border-b"><td class="p-3">{{ $p->name }}</td><td>{{ $p->type }}</td><td>{{ $p->category?->name }}</td><td>{{ $p->variants->sum('stock') }}</td><td>{{ $p->is_published ? 'Published' : 'Draft' }}</td><td>{{ $p->is_approved ? 'Yes' : 'Pending' }}</td></tr>@endforeach</tbody></table></div>{{ $products->links() }}</div>
@endsection
