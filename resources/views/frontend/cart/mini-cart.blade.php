<div class="mini-cart-wrap">
  <div class="flex justify-between items-center mb-3"><h4 class="font-semibold text-[hsl(var(--foreground))]">Your Cart ({{ $items->sum('quantity') }} items)</h4><button class="mini-cart-close">×</button></div>
  @if($items->count())
  <div class="space-y-2 max-h-80 overflow-auto">
    @foreach($items as $item)
      <div class="flex gap-2 items-start"><img src="{{ $item->product->thumbnail_url }}" class="w-10 h-10 rounded"><div class="text-sm flex-1"><div class="text-[hsl(var(--foreground))]">{{ $item->product->name }}</div><div class="text-[hsl(var(--muted-foreground))]">{{ $item->quantity }} × {{ currency_format((float)$item->unit_price) }}</div></div><button class="remove-item-btn text-[var(--color-danger)]" data-item-id="{{ $item->id }}">×</button></div>
    @endforeach
  </div>
  <div class="mt-3 text-sm flex justify-between"><span>Subtotal</span><strong>{{ currency_format((float)$cart->items->sum(fn($i)=>$i->unit_price*$i->quantity)) }}</strong></div>
  <div class="mt-3 grid grid-cols-2 gap-2"><a href="/cart" class="btn-secondary py-2 text-center">View Cart</a><a href="/checkout" class="btn-primary py-2 text-center">Checkout</a></div>
  @else
  <div class="text-center py-8 text-[hsl(var(--muted-foreground))]">Your cart is empty.<div><a href="/shop" class="text-[hsl(var(--primary))]">Start Shopping</a></div></div>
  @endif
</div>
