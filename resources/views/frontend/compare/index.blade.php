@include('frontend.layouts.header')
<div class="container py-6">
  <div class="flex items-center justify-between mb-3"><h1 class="text-2xl font-semibold">Compare Products</h1><button id="compare-clear" class="btn-secondary">Clear</button></div>
  @if($products->count())
  <div class="overflow-auto bg-white border border-gray-200 rounded-xl shadow-sm"><table class="w-full min-w-[900px]"><thead><tr><th class="p-3 text-left">Attribute</th>@foreach($products as $p)<th class="p-3 text-left"><img src="{{ $p->thumbnail_url }}" class="w-14 h-14 rounded mb-1"><div>{{ $p->name }}</div></th>@endforeach</tr></thead><tbody>
    <tr><td class="p-3 border-t">Price</td>@foreach($products as $p)<td class="p-3 border-t">{{ currency_format($p->main_price) }}</td>@endforeach</tr>
    <tr><td class="p-3 border-t">Rating</td>@foreach($products as $p)<td class="p-3 border-t">{{ number_format((float)$p->rating,1) }}</td>@endforeach</tr>
    <tr><td class="p-3 border-t">Category</td>@foreach($products as $p)<td class="p-3 border-t">{{ $p->category?->name ?? '—' }}</td>@endforeach</tr>
    <tr><td class="p-3 border-t">Brand</td>@foreach($products as $p)<td class="p-3 border-t">{{ $p->brand?->name ?? '—' }}</td>@endforeach</tr>
    <tr><td class="p-3 border-t">SKU</td>@foreach($products as $p)<td class="p-3 border-t">{{ $p->sku ?? '—' }}</td>@endforeach</tr>
    @foreach($allAttributes as $attr)
      <tr><td class="p-3 border-t">{{ $attr->name }}</td>@foreach($products as $p)<td class="p-3 border-t">{{ $p->variants->flatMap->attributeValues->where('attribute_id',$attr->id)->pluck('value')->unique()->implode(', ') ?: '—' }}</td>@endforeach</tr>
    @endforeach
    <tr><td class="p-3 border-t"></td>@foreach($products as $p)<td class="p-3 border-t"><button class="add-to-cart-btn btn-primary" data-product-id="{{ $p->id }}">Add Cart</button> <button class="compare-btn active btn-secondary" data-product-id="{{ $p->id }}">Remove</button></td>@endforeach</tr>
  </tbody></table></div>
  @else
  <div class="text-gray-500">No products in compare list.</div>
  @endif
</div>
@include('frontend.layouts.footer')
@vite(['resources/js/frontend/compare.js','resources/js/frontend/cart.js'])
