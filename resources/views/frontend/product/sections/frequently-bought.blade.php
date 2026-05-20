<div class="fbt-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px">
    <article class="fbt-item card" style="padding:14px">
        <a href="{{ route('product.show', $product->slug) }}" class="block">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1 / 1;object-fit:cover;border-radius:12px;margin-bottom:12px">
            <h4>{{ $product->name }}</h4>
            <div>{{ currency_format((float) ($product->main_price ?? 0)) }}</div>
        </a>
    </article>
    @foreach($frequentlyBought as $item)
        <article class="fbt-item card" style="padding:14px">
            <a href="{{ route('product.show', $item->slug) }}" class="block">
                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->name }}" style="width:100%;aspect-ratio:1 / 1;object-fit:cover;border-radius:12px;margin-bottom:12px">
                <h4>{{ $item->name }}</h4>
                <div>{{ currency_format((float) ($item->main_price ?? 0)) }}</div>
            </a>
        </article>
    @endforeach
</div>
