@if($product->type === 'variable' && !empty($variationAttributes))
<div class="product-variation-selectors">
  @foreach($variationAttributes as $attribute)
    <div class="variation-section">
      <div class="variation-label">
        <span>{{ $attribute['name'] }}</span>
        <span class="selected-value" x-text="selectedAttributeLabel({{ (int) $attribute['id'] }})"></span>
      </div>
      <div class="{{ collect($attribute['values'])->contains(fn ($value) => !empty($value['color'])) ? 'color-swatches' : 'size-swatches' }}">
        @foreach($attribute['values'] as $value)
          <button
            type="button"
            class="{{ !empty($value['color']) ? 'color-swatch' : 'size-swatch' }}"
            :class="{
              'active': isAttributeSelected({{ (int) $attribute['id'] }}, {{ (int) $value['id'] }}),
              'out-of-stock': !isAttributeValueAvailable({{ (int) $attribute['id'] }}, {{ (int) $value['id'] }})
            }"
            @click="selectAttributeValue({{ (int) $attribute['id'] }}, {{ (int) $value['id'] }})"
            @if(!empty($value['color'])) style="background-color: {{ $value['color'] }};" @endif
          >@if(empty($value['color'])){{ $value['name'] }}@endif</button>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
@endif
