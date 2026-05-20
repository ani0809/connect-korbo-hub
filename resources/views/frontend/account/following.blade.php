@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-2xl font-semibold mb-4">Following</h1>
<div class="grid md:grid-cols-3 gap-4">@forelse($following as $follow)<div class="border rounded-xl overflow-hidden"><div class="h-20 bg-gray-100"></div><div class="p-3"><div class="font-medium">{{ $follow->seller->shop_name ?? 'Seller' }}</div><div class="text-xs text-gray-500">{{ $follow->seller->products_count ?? 0 }} products</div><div class="mt-2 flex gap-2"><a class="border rounded px-2 py-1 text-sm" href="{{ route('seller.shop.show',$follow->seller->shop_slug) }}">Visit Store</a><button class="border rounded px-2 py-1 text-sm wishlist-btn" data-seller-id="{{ $follow->seller_id }}">Unfollow</button></div></div></div>@empty<div class="text-gray-500">You're not following any sellers.</div>@endforelse</div>
<div class="mt-4">{{ $following->links() }}</div>
@endsection
