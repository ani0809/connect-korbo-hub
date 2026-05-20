<section class="recently-viewed-section">
    <div class="container">
        <h2 class="section-title">Recently Viewed</h2>
        <div class="products-grid cols-4">
            @forelse(($products ?? collect()) as $item)
                <article class="product-card card">
                    <a href="{{ route('product.show', $item->slug) }}">
                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->name }}">
                        <h3>{{ $item->name }}</h3>
                        <div>{{ currency_format((float) ($item->main_price ?? 0)) }}</div>
                    </a>
                </article>
            @empty
                <p class="text-sm text-gray-500">You have not viewed any other products yet.</p>
            @endforelse
        </div>
    </div>
</section>
