<div class="quick-view-overlay" data-qv-overlay>
  <div class="quick-view-modal" data-qv-modal>
    <button class="close" data-qv-close>&times;</button>
    <div class="quick-view-grid">
      <div class="images"><img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">@foreach($product->images->take(2) as $img)<img src="{{ asset('storage/'.$img->image) }}" alt="img">@endforeach</div>
      <div class="content"><h3>{{ $product->name }}</h3><div class="price">${{ number_format($product->main_price, 2) }}</div><div class="vars"><span class="swatch" style="background:#111"></span><span class="swatch" style="background:#ef4444"></span></div><button>Add to Cart</button><p><a href="{{ route('product.show', $product->slug) }}">View Full Details</a></p></div>
    </div>
  </div>
</div>
<script>(function(){const ov=document.querySelector('[data-qv-overlay]');const cls=document.querySelector('[data-qv-close]');function close(){ov?.remove();document.removeEventListener('keydown',onKey);}function onKey(e){if(e.key==='Escape')close();}cls?.addEventListener('click',close);ov?.addEventListener('click',(e)=>{if(e.target===ov)close();});document.addEventListener('keydown',onKey);}());</script>
