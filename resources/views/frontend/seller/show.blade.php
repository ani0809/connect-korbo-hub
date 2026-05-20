@include('frontend.layouts.header')
<div class="w-full h-52 bg-[hsl(var(--muted))]" style="background-image:url({{ $seller->shop_banner ? asset('storage/'.$seller->shop_banner) : '' }});background-size:cover;background-position:center">
  <div class="container h-full flex items-end pb-4">
    <div class="bg-black/45 text-white rounded-xl p-4 flex items-center gap-4 flex-wrap border border-white/20 shadow-lg">
      <img src="{{ $seller->shop_logo ? asset('storage/'.$seller->shop_logo) : '' }}" alt="" class="w-16 h-16 rounded-full bg-white object-cover border border-white/40">
      <div>
        <div class="text-xl font-heading font-semibold tracking-tight">{{ $seller->shop_name }}</div>
        <div class="text-sm text-white/90">
          {{ $seller->followers_count }} followers · {{ $seller->products_count }} products
          · {{ number_format((float) $seller->rating, 1) }} ({{ (int) $seller->total_reviews }} {{ \Illuminate\Support\Str::plural('review', (int) $seller->total_reviews) }})
        </div>
      </div>
      <button id="follow-btn" type="button" data-seller-id="{{ $seller->id }}" class="ml-auto btn-secondary text-xs px-3.5 py-2 {{ $isFollowing ? 'seller-following' : 'seller-follow-idle' }}">
        {{ $isFollowing ? 'Following' : 'Follow' }}
      </button>
    </div>
  </div>
</div>

<div class="container py-6 space-y-10" id="seller-products">
  <div>
    <h2 class="text-lg font-heading font-semibold text-[hsl(var(--foreground))] mb-4">Products</h2>
    <div class="shop-grid">
      @foreach($products as $product)
        <x-builder.product-card.renderer :product="$product" />
      @endforeach
    </div>
    {{ $products->links() }}
  </div>

  <div id="seller-reviews" class="border-t border-[hsl(var(--border))] pt-8">
    <h2 class="text-lg font-heading font-semibold text-[hsl(var(--foreground))] mb-4">Shop reviews</h2>
    @if($sellerReviews->isEmpty())
      <p class="text-[hsl(var(--muted-foreground))]">No shop reviews yet.</p>
    @else
      <div class="space-y-4">
        @foreach($sellerReviews as $sr)
          <div class="card p-4">
            <div class="flex justify-between gap-4 flex-wrap">
              <div class="text-[hsl(var(--warning))]">{{ str_repeat('★', (int) $sr->rating) }}{{ str_repeat('☆', 5 - (int) $sr->rating) }}</div>
              <span class="text-xs text-[hsl(var(--muted-foreground))]">@datetime($sr->created_at)</span>
            </div>
            <div class="text-sm font-medium mt-2 text-[hsl(var(--foreground))]">{{ $sr->user?->name ?? 'Customer' }}
              <span class="text-xs font-normal text-[hsl(var(--success))] ml-2">Verified purchase</span>
            </div>
            @if($sr->comment)
              <p class="text-[hsl(var(--muted-foreground))] mt-2">{{ $sr->comment }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>

<script>
document.getElementById('follow-btn')?.addEventListener('click', async (e) => {
  if (!window.isLoggedIn) { alert('Please login first'); return; }
  const btn = e.currentTarget;
  const r = await fetch('{{ route('seller.follow') }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: JSON.stringify({ seller_id: btn.dataset.sellerId }),
  });
  const d = await r.json();
  if (d.success) {
    btn.textContent = d.action === 'followed' ? 'Following' : 'Follow';
    btn.classList.toggle('seller-following', d.action === 'followed');
    btn.classList.toggle('seller-follow-idle', d.action !== 'followed');
  }
});
</script>
@include('frontend.layouts.footer')
