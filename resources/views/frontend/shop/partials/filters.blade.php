<form id="shop-filter-form" class="space-y-4">
  <div class="flex items-center justify-between gap-2 border-b border-[hsl(var(--border))] pb-3">
    <h3 class="font-heading font-semibold text-[hsl(var(--foreground))]">Filters</h3>
    <button type="button" id="clear-filters" class="text-sm link-primary font-medium">Clear All</button>
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))] mb-2">Categories</label>
    @foreach($categories as $cat)<label class="flex items-center gap-2 text-sm py-1"><input type="checkbox" name="category[]" value="{{ $cat->slug }}"> {{ $cat->name }} ({{ $cat->products_count }})</label>@endforeach
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))] mb-2">Price range</label>
    <div class="grid grid-cols-2 gap-2"><input type="number" name="min_price" placeholder="Min" class="form-input px-2 py-2 text-sm"><input type="number" name="max_price" placeholder="Max" class="form-input px-2 py-2 text-sm"></div>
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))] mb-2">Brands</label>
    @foreach($brands as $brand)<label class="flex items-center gap-2 text-sm py-1"><input type="checkbox" name="brand[]" value="{{ $brand->id }}"> {{ $brand->name }} ({{ $brand->products_count }})</label>@endforeach
  </div>
  <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="in_stock" value="1"> In Stock</label></div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))] mb-2">Rating</label>
    <select name="rating" class="w-full form-input px-2 py-2"><option value="">Any</option><option value="5">5</option><option value="4">4+</option><option value="3">3+</option></select>
  </div>
  @foreach($attributes as $attr)
    <div><label class="block text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))] mb-2">{{ $attr->name }}</label>@foreach($attr->values as $v)<label class="flex items-center gap-2 text-sm py-1"><input type="checkbox" name="attributes[{{ $attr->id }}][]" value="{{ $v->slug }}"> {{ $v->value }}</label>@endforeach</div>
  @endforeach
</form>
