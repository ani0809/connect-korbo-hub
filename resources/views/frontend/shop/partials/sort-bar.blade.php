<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
  <div class="text-sm text-[hsl(var(--muted-foreground))]">
    Showing <span class="font-semibold text-[hsl(var(--foreground))]">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</span> of <span class="font-semibold text-[hsl(var(--foreground))]">{{ $products->total() }}</span> products
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <label for="shop-sort" class="text-sm font-medium text-[hsl(var(--foreground))]">Sort</label>
    <select id="shop-sort" class="form-input min-w-[180px] px-3 py-2 text-sm">
      <option value="newest">Newest</option>
      <option value="oldest">Oldest</option>
      <option value="price_low">Price Low</option>
      <option value="price_high">Price High</option>
      <option value="popular">Popular</option>
      <option value="rating">Rating</option>
    </select>
  </div>
</div>
