<div class="pc-info">
    @foreach($children as $child)
        @includeIf("builder.product-card.blocks.".$child["type"], ["block" => $child, "product" => $product, "children" => $child["children"] ?? []])
    @endforeach
</div>
