<div class="shop-grid">
@forelse($products as $product)
  <x-builder.product-card.renderer :product="$product" />
@empty
  <div class="col-span-full">
    <div class="empty-state">
      <div class="text-lg font-heading font-semibold text-[hsl(var(--foreground))]">No products found</div>
      <p class="text-sm mt-1 text-[hsl(var(--muted-foreground))]">Try changing filters, category, or search keyword.</p>
      <a href="{{ route('shop') }}" class="btn-primary mt-3 inline-block">Reset Filters</a>
    </div>
  </div>
@endforelse
</div>
<div class="mt-6">{{ $products->links() }}</div>
