<div class="grid lg:grid-cols-2 gap-4">
  <div class="bg-white border rounded-xl p-4 space-y-3">
    <h3 class="font-semibold">Step 1: Basic Info</h3>
    <input name="name" value="{{ old('name', $promotion->name ?? '') }}" class="border rounded px-3 py-2 w-full" placeholder="Promotion name" required>
    <select name="type" class="border rounded px-3 py-2 w-full" required>
      @foreach(['buy_x_get_y'=>'Buy X Get Y Free','quantity_discount'=>'Quantity Discount','bundle'=>'Bundle Offer','free_shipping'=>'Free Shipping','flash_sale'=>'Flash Sale','combo_discount'=>'Combo Discount'] as $k => $v)
        <option value="{{ $k }}" @selected(old('type', $promotion->type ?? '')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <textarea name="description" class="border rounded px-3 py-2 w-full" rows="3" placeholder="Description">{{ old('description', $promotion->description ?? '') }}</textarea>
    <p class="text-xs text-slate-500">Step 2 uses JSON for flexibility in this version.</p>
    <label class="text-sm font-medium">Conditions (JSON)</label>
    <textarea name="conditions" class="border rounded px-3 py-2 w-full font-mono text-xs" rows="8" placeholder='{"buy_quantity":2,"tiers":[]}'>{{ old('conditions', isset($promotion) ? json_encode($promotion->conditions ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '{}') }}</textarea>
    <label class="text-sm font-medium">Rewards (JSON)</label>
    <textarea name="rewards" class="border rounded px-3 py-2 w-full font-mono text-xs" rows="8" placeholder='{"get_quantity":1,"get_type":"cheapest"}'>{{ old('rewards', isset($promotion) ? json_encode($promotion->rewards ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '{}') }}</textarea>
  </div>

  <div class="bg-white border rounded-xl p-4 space-y-3">
    <h3 class="font-semibold">Step 3-5: Applicability, Limits, Schedule, Badge</h3>
    <label class="text-sm">Applies to</label>
    <select name="applies_to" class="border rounded px-3 py-2 w-full">
      @foreach(['all'=>'All Products','categories'=>'Specific Categories','products'=>'Specific Products','brands'=>'Specific Brands','sellers'=>'Specific Sellers'] as $k => $v)
        <option value="{{ $k }}" @selected(old('applies_to', $promotion->applies_to ?? 'all')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <input name="applies_to_ids[]" class="border rounded px-3 py-2 w-full" placeholder="Applies IDs (single entry supported)">
    <input name="exclude_ids[]" class="border rounded px-3 py-2 w-full" placeholder="Exclude Product IDs">
    <div class="grid grid-cols-2 gap-2">
      <label class="text-sm">Priority <input type="number" name="priority" value="{{ old('priority', $promotion->priority ?? 0) }}" class="border rounded px-3 py-2 w-full"></label>
      <label class="text-sm">Min Cart <input type="number" step="0.01" name="minimum_cart_amount" value="{{ old('minimum_cart_amount', $promotion->minimum_cart_amount ?? '') }}" class="border rounded px-3 py-2 w-full"></label>
      <label class="text-sm">Total uses <input type="number" name="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit ?? 0) }}" class="border rounded px-3 py-2 w-full"></label>
      <label class="text-sm">Per user <input type="number" name="usage_per_user" value="{{ old('usage_per_user', $promotion->usage_per_user ?? 0) }}" class="border rounded px-3 py-2 w-full"></label>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <label class="text-sm">Start <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($promotion->starts_at ?? null)->format('Y-m-d\TH:i')) }}" class="border rounded px-3 py-2 w-full"></label>
      <label class="text-sm">End <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($promotion->expires_at ?? null)->format('Y-m-d\TH:i')) }}" class="border rounded px-3 py-2 w-full"></label>
    </div>
    <div class="flex gap-3">
      <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->is_active ?? true))> Active</label>
      <label><input type="checkbox" name="is_stackable" value="1" @checked(old('is_stackable', $promotion->is_stackable ?? false))> Stackable</label>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <input name="badge_text" value="{{ old('badge_text', $promotion->badge_text ?? '') }}" class="border rounded px-3 py-2 w-full" placeholder="Badge text">
      <input type="color" name="badge_color" value="{{ old('badge_color', $promotion->badge_color ?? '#ef4444') }}" class="border rounded px-3 py-2 w-full">
    </div>
  </div>
</div>
