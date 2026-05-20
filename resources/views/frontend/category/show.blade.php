@extends('frontend.layouts.app')
@section('title', $category->name)
@section('content')
<div class="max-w-[var(--container-width)] mx-auto px-4 py-6">
  <x-breadcrumb :items="[['name'=>$category->name]]" />
  <div class="mt-3 mb-4 p-4 border rounded-xl {{ $category->banner ? '' : 'bg-gray-50' }}">
    <h1 class="text-2xl font-semibold">{{ $category->name }}</h1>
    @if($category->description)<p class="text-sm text-gray-600 mt-2">{{ $category->description }}</p>@endif
  </div>
  <div class="grid md:grid-cols-[260px_1fr] gap-5">
    <aside class="border rounded-xl p-4"><h3 class="font-semibold mb-2">Brands</h3>@foreach($brands as $brand)<a class="block text-sm py-1" href="{{ route('shop.brand',$brand->slug) }}">{{ $brand->name }}</a>@endforeach</aside>
    <section>
      <div class="grid md:grid-cols-4 gap-4">@foreach($products as $product)<div class="border rounded-xl p-3"><img src="{{ $product->thumbnail_url }}" class="w-full h-40 object-cover rounded"><a class="block mt-2 font-medium" href="{{ route('product.show',$product->slug) }}">{{ $product->name }}</a><div class="text-sm text-gray-500">{{ currency_format((float)$product->main_price) }}</div></div>@endforeach</div>
      <div class="mt-4">{{ $products->links() }}</div>
    </section>
  </div>
</div>
@endsection
