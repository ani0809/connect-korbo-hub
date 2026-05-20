@extends('frontend.layouts.app')
@section('title', 'Search')
@section('content')
<div class="max-w-[var(--container-width)] mx-auto px-4 py-8">
  <div class="card p-5 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-heading font-semibold">Search results for "{{ $query ?? request('q') }}"</h1>
        <p class="text-sm text-[hsl(var(--muted-foreground))] mt-1">Live ranked results with category context.</p>
      </div>
      <div class="badge status-badge">{{ ($products ?? collect())->count() }} items</div>
    </div>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @forelse(($products ?? collect()) as $product)
      <div class="card p-3 ui-hover-lift transition-all">
        <img src="{{ $product->thumbnail_url }}" class="w-full aspect-[4/3] object-cover rounded-lg">
        <a class="block mt-3 h-11 leading-5 overflow-hidden font-medium text-[hsl(var(--foreground))]" href="{{ route('product.show',$product->slug) }}">{!! $product->name !!}</a>
        <div class="text-sm text-[hsl(var(--muted-foreground))] mt-1">{{ currency_format((float)$product->main_price) }}</div>
      </div>
    @empty
      <div class="col-span-full"><div class="empty-state">No results found for your query. Try different keywords.</div></div>
    @endforelse
  </div>
  @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)<div class="mt-6">{{ $products->links() }}</div>@endif
</div>
@endsection
