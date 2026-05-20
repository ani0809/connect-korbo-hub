<div class="pc-thumb">
    <a href="/product/{{ $product->slug }}">
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
    </a>
    @foreach($children as $child)
        @includeIf("builder.product-card.blocks.".$child["type"], ["block" => $child, "product" => $product, "children" => $child["children"] ?? []])
    @endforeach
</div>
