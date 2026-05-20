@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-2xl font-semibold mb-4">Downloads</h1>
<div class="space-y-3">@forelse($downloads as $item)<div class="border rounded-xl p-4"><div class="font-medium">{{ $item->product_name }}</div><div class="text-xs text-gray-500">Order: {{ $item->order->order_number }}</div><div class="mt-2"><a class="border rounded px-2 py-1 text-sm" href="{{ route('account.downloads.get',$item->id) }}">Download</a><span class="text-xs text-gray-500 ml-2">{{ $item->download_limit ? ($item->download_limit-$item->download_count).' remaining' : 'Unlimited' }}</span></div></div>@empty<div class="text-gray-500">No downloads yet.</div>@endforelse</div>
@endsection
