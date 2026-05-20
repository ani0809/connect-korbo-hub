@extends('admin.layouts.app')
@section('title', isset($product) ? 'Edit product' : 'Add new product')
@section('breadcrumb','')
@push('styles')
<style>
  .admin-breadcrumb,
  .admin-page-header {
    display: none !important;
  }
  .admin-content {
    padding-top: 14px !important;
  }
</style>
@endpush
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
@endpush
@section('content')
<section class="zpf">
  @php
    $isEditing = isset($product);
    $editorState = $isEditing && is_array($product->custom_tabs['editor'] ?? null) ? $product->custom_tabs['editor'] : [];
    $publishStatusDefault = old('publish_status', $editorState['publish_status'] ?? (($product->is_published ?? false) ? 'publish' : 'draft'));
    $frontendProductBase = url('/product');
    $primaryVariant = $isEditing ? $product->variants->sortBy('sort_order')->first() : null;
    $dimensions = is_array($editorState['dimensions'] ?? null) ? $editorState['dimensions'] : [];
    $selectedAttributeValues = collect();
    if ($isEditing) {
        $selectedAttributeValues = $product->variants
            ->flatMap(fn ($variant) => $variant->attributeValues)
            ->pluck('id')
            ->unique()
            ->values();
        $selectedAttributeValues = $selectedAttributeValues
            ->merge(collect($editorState['attribute_values'] ?? [])->flatten())
            ->unique()
            ->values();
    }
    $existingVariants = $isEditing
        ? $product->variants->sortBy('sort_order')->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'sale_price' => $variant->sale_price,
                'sale_starts_at' => optional($variant->sale_starts_at)->format('Y-m-d\TH:i'),
                'sale_ends_at' => optional($variant->sale_ends_at)->format('Y-m-d\TH:i'),
                'stock' => $variant->stock,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'attribute_value_ids' => $variant->attributeValues->pluck('id')->values()->all(),
            ];
        })->values()
        : collect();
    $errorsList = $errors->all();
    $selectedLinkGroups = [
        'upsell' => collect($productOptions ?? [])->whereIn('id', old('upsell_products', $editorState['upsell_products'] ?? []))->values(),
        'cross' => collect($productOptions ?? [])->whereIn('id', old('cross_sell_products', $editorState['cross_sell_products'] ?? []))->values(),
        'linked' => collect($productOptions ?? [])->whereIn('id', old('linked_products', $editorState['linked_products'] ?? []))->values(),
        'bundle' => collect($productOptions ?? [])->whereIn('id', old('bundle_products', $editorState['bundle_products'] ?? []))->values(),
    ];
  @endphp
  <form id="product-form" method="POST" enctype="multipart/form-data" action="{{ isset($product) ? route('admin.products.update',$product->id) : route('admin.products.store') }}" class="zpf-grid zpf-grid-wp">
    @csrf @if(isset($product)) @method('PUT') @endif
    <input type="hidden" name="submit_action" id="submit_action" value="{{ old('submit_action', 'publish') }}">
    @if(session('success'))
      <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger mb-3">
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-2 pl-3">
          @foreach($errorsList as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="zpf-main" id="zpfMainMetaboxes">
      <header class="zpf-page-head">
        <h2>{{ $isEditing ? 'Edit product' : 'Add new product' }}</h2>
        <div class="zpf-page-tools">
          <button type="button" class="zpf-btn zpf-btn-tab" id="zpfScreenOptionsBtn" aria-expanded="false">Screen Options</button>
          <div class="zpf-screen-options" id="zpfScreenOptions" hidden>
            <strong>Screen elements</strong>
            <label><input type="checkbox" data-box-toggle="title-box" checked> Product title</label>
            <label><input type="checkbox" data-box-toggle="description-box" checked> Product description</label>
            <label><input type="checkbox" data-box-toggle="data-box" checked> Product data</label>
            <label><input type="checkbox" data-box-toggle="short-desc-box" checked> Product short description</label>
          </div>
        </div>
      </header>

      <article class="zpf-card zpf-title-card" data-box="title-box">
        <div class="zpf-card-body">
          <input name="name" id="name" value="{{ old('name',$product->name ?? '') }}" class="zpf-input zpf-title-input" placeholder="Product name" required>
          <input type="hidden" name="slug" id="slug" value="{{ old('slug',$product->slug ?? '') }}">
          <div
            id="productPermalinkRow"
            class="zpf-permalink-row"
            data-base-url="{{ $frontendProductBase }}"
            @if(!$isEditing && !old('name') && !old('slug')) hidden @endif
          >
            <span class="zpf-note">Permalink:</span>
            <a id="productPermalinkLink" href="{{ $isEditing ? $frontendProductBase.'/'.$product->slug : '#' }}" class="zpf-link" target="_blank" rel="noopener">
              {{ $isEditing ? $frontendProductBase.'/'.$product->slug : $frontendProductBase.'/' }}
            </a>
            <button type="button" id="productPermalinkEdit" class="zpf-btn-lite">Edit</button>
            <span id="productPermalinkEditor" class="zpf-permalink-editor" hidden>
              <span class="zpf-note">{{ rtrim($frontendProductBase, '/') }}/</span>
              <input type="text" id="productPermalinkInput" class="zpf-input" value="{{ old('slug',$product->slug ?? '') }}" placeholder="product-slug">
              <button type="button" id="productPermalinkSave" class="zpf-btn-lite">OK</button>
              <button type="button" id="productPermalinkCancel" class="zpf-btn-lite">Cancel</button>
            </span>
          </div>
        </div>
      </article>

      <article class="zpf-card" data-box="description-box" data-main-metabox-id="description-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product description</h3>
          <span class="zpf-head-tools">
            <button type="button" id="btn-ai-generate" class="zpf-btn">AI Generate</button>
          </span>
        </header>
        <div class="zpf-card-content">
        <div class="zpf-card-body">
          <textarea name="description" id="description" rows="12" class="zpf-textarea zpf-editor shopadmin-text-editor" data-min-height="320">{{ old('description',$product->description ?? '') }}</textarea>
        </div>
        </div>
      </article>

      <article class="zpf-card zpf-data" data-box="data-box" data-main-metabox-id="data-box" draggable="true">
        <header class="zpf-card-head zpf-data-head">
          <h3>Product data</h3>
          <div class="zpf-head-tools">
            <div class="zpf-data-controls">
            <select name="type" id="product-type" class="zpf-input">
              <option value="simple" @selected(old('type',$product->type ?? 'simple') === 'simple')>Simple product</option>
              <option value="variable" @selected(old('type',$product->type ?? 'simple') === 'variable')>Variable product</option>
              <option value="digital" @selected(old('type',$product->type ?? 'simple') === 'digital')>Digital product</option>
              <option value="classified" @selected(old('type',$product->type ?? 'simple') === 'classified')>Classified</option>
            </select>
            </div>
          </div>
        </header>
        <div class="zpf-card-content">
        <div class="zpf-data-wrap">
          <nav class="zpf-data-tabs" id="productDataTabs">
            <button type="button" class="is-active" data-tab="general">General</button>
            <button type="button" data-tab="inventory">Inventory</button>
            <button type="button" data-tab="shipping">Shipping</button>
            <button type="button" data-tab="linked-products">Linked Products</button>
            <button type="button" data-tab="attributes">Attributes</button>
            <button type="button" data-tab="variations">Variations</button>
            <button type="button" data-tab="advanced">Advanced</button>
            <button type="button" data-tab="frequently-bought-together">Frequently Bought Together</button>
            <button type="button" data-tab="more-options">Get more options</button>
            <button type="button" data-tab="facebook">Facebook</button>
          </nav>
          <div class="zpf-data-panels">
            <section class="zpf-data-panel is-active" data-panel="general">
              <div class="zpf-panel-block">
                <div class="zpf-row zpf-row-2">
                  <input type="number" step="0.01" name="price" value="{{ old('price', $primaryVariant?->price) }}" placeholder="Regular price" class="zpf-input">
                  <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $primaryVariant?->sale_price) }}" placeholder="Sale price" class="zpf-input">
                </div>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="inventory">
              <div class="zpf-panel-block">
                <div class="zpf-inv-row">
                  <label class="zpf-inv-label">SKU</label>
                  <div class="zpf-inv-control zpf-sku">
                    <input name="sku" id="sku" value="{{ old('sku', $product->sku ?? '') }}" placeholder="SKU" class="zpf-input">
                    <button type="button" id="btn-generate-sku" class="zpf-btn">Generate</button>
                  </div>
                </div>
                <div class="zpf-inv-row">
                  <label class="zpf-inv-label">GTIN, UPC, EAN, or ISBN</label>
                  <div class="zpf-inv-control">
                    <input name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="GTIN, UPC, EAN, or ISBN" class="zpf-input">
                  </div>
                </div>
                <div class="zpf-inv-row">
                  <label class="zpf-inv-label">Stock management</label>
                  <div class="zpf-inv-control">
                    <label class="zpf-check"><input type="checkbox" id="manage_stock" name="manage_stock" value="1" @checked(old('manage_stock', $editorState['manage_stock'] ?? true))> Track stock quantity for this product</label>
                  </div>
                </div>
                <div class="zpf-inv-row" data-inventory-role="managed-stock-fields">
                  <label class="zpf-inv-label">Quantity</label>
                  <div class="zpf-inv-control">
                    <input type="number" name="stock" value="{{ old('stock', $primaryVariant?->stock) }}" placeholder="Quantity" class="zpf-input">
                  </div>
                </div>
                <div class="zpf-inv-row" data-inventory-role="stock-status">
                  <label class="zpf-inv-label">Stock status</label>
                  <div class="zpf-inv-control zpf-radio-list">
                    <label class="zpf-check"><input type="radio" name="stock_status" value="instock" @checked(old('stock_status', $editorState['stock_status'] ?? 'instock') === 'instock')> In stock</label>
                    <label class="zpf-check"><input type="radio" name="stock_status" value="outofstock" @checked(old('stock_status', $editorState['stock_status'] ?? 'instock') === 'outofstock')> Out of stock</label>
                    <label class="zpf-check"><input type="radio" name="stock_status" value="onbackorder" @checked(old('stock_status', $editorState['stock_status'] ?? 'instock') === 'onbackorder')> On backorder</label>
                  </div>
                </div>
                <div class="zpf-inv-row" data-inventory-role="managed-stock-fields">
                  <label class="zpf-inv-label">Allow backorders?</label>
                  <div class="zpf-inv-control zpf-radio-list">
                    <label class="zpf-check"><input type="radio" name="backorder_mode" value="no" @checked(old('backorder_mode', $editorState['backorder_mode'] ?? 'no') === 'no')> Do not allow</label>
                    <label class="zpf-check"><input type="radio" name="backorder_mode" value="notify" @checked(old('backorder_mode', $editorState['backorder_mode'] ?? 'no') === 'notify')> Allow, but notify customer</label>
                    <label class="zpf-check"><input type="radio" name="backorder_mode" value="yes" @checked(old('backorder_mode', $editorState['backorder_mode'] ?? 'no') === 'yes')> Allow</label>
                  </div>
                </div>
                <div class="zpf-inv-row" data-inventory-role="managed-stock-fields">
                  <label class="zpf-inv-label">Low stock threshold</label>
                  <div class="zpf-inv-control">
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $primaryVariant?->low_stock_threshold ?? 5) }}" placeholder="Store-wide threshold (2)" class="zpf-input">
                  </div>
                </div>
                <div class="zpf-inv-row">
                  <label class="zpf-inv-label">Sold individually</label>
                  <div class="zpf-inv-control">
                    <label class="zpf-check"><input type="checkbox" id="sold_individually" name="sold_individually" value="1" @checked(old('sold_individually', $editorState['sold_individually'] ?? false))> Limit purchases to 1 item per order</label>
                  </div>
                </div>
                <div class="zpf-row zpf-row-2">
                  <input name="unit" value="{{ old('unit', $product->unit ?? '') }}" placeholder="Unit of measurement" class="zpf-input">
                  <input type="number" name="club_point" value="{{ old('club_point', $product->club_point ?? 0) }}" placeholder="Club points" class="zpf-input">
                </div>
                <div class="zpf-row zpf-row-2">
                  <input type="number" name="min_purchase_qty" value="{{ old('min_purchase_qty', $product->min_purchase_qty ?? 1) }}" placeholder="Min purchase qty" class="zpf-input">
                  <input type="number" name="max_purchase_qty" value="{{ old('max_purchase_qty', $product->max_purchase_qty ?? '') }}" placeholder="Max purchase qty" class="zpf-input">
                </div>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="shipping">
              <div class="zpf-panel-block">
                <div class="zpf-row zpf-row-2">
                  <input type="number" step="0.001" name="weight" value="{{ old('weight', $product->weight ?? '') }}" placeholder="Weight (kg)" class="zpf-input">
                  <input name="shipping_class" value="{{ old('shipping_class', $editorState['shipping_class'] ?? '') }}" placeholder="Shipping class" class="zpf-input">
                </div>
                <div class="zpf-row zpf-row-3">
                  <input type="number" step="0.01" name="length" value="{{ old('length', $dimensions['length'] ?? '') }}" placeholder="Length" class="zpf-input">
                  <input type="number" step="0.01" name="width" value="{{ old('width', $dimensions['width'] ?? '') }}" placeholder="Width" class="zpf-input">
                  <input type="number" step="0.01" name="height" value="{{ old('height', $dimensions['height'] ?? '') }}" placeholder="Height" class="zpf-input">
                </div>
                <div class="zpf-row zpf-row-2">
                  <select name="shipping_type" class="zpf-input">
                    <option value="free" @selected(old('shipping_type', $product->shipping_type ?? 'free') === 'free')>Free shipping</option>
                    <option value="flat" @selected(old('shipping_type', $product->shipping_type ?? 'free') === 'flat')>Flat rate</option>
                    <option value="product_wise" @selected(old('shipping_type', $product->shipping_type ?? 'free') === 'product_wise')>Product-wise</option>
                  </select>
                  <input type="number" step="0.01" name="shipping_cost" value="{{ old('shipping_cost', $product->shipping_cost ?? 0) }}" placeholder="Shipping cost" class="zpf-input">
                </div>
                <input name="estimated_delivery" value="{{ old('estimated_delivery', $product->estimated_delivery ?? '') }}" placeholder="Estimated delivery text" class="zpf-input">
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="linked-products">
              <div class="zpf-panel-block">
                <div class="zpf-related-field" data-related-field data-target-input="upsell_products">
                  <input type="hidden" name="upsell_products" value="{{ old('upsell_products', implode(',', $editorState['upsell_products'] ?? [])) }}">
                  <label class="zpf-note">Upsells</label>
                  <div class="zpf-related-selected" data-related-selected>
                    @foreach($selectedLinkGroups['upsell'] as $linkedProduct)
                      <button type="button" class="zpf-related-chip" data-related-id="{{ $linkedProduct->id }}">{{ $linkedProduct->name }}@if($linkedProduct->sku) <span>#{{ $linkedProduct->sku }}</span>@endif</button>
                    @endforeach
                  </div>
                  <input class="zpf-input" data-related-search list="product-link-options" placeholder="Search for an upsell product...">
                </div>
                <div class="zpf-related-field" data-related-field data-target-input="cross_sell_products">
                  <input type="hidden" name="cross_sell_products" value="{{ old('cross_sell_products', implode(',', $editorState['cross_sell_products'] ?? [])) }}">
                  <label class="zpf-note">Cross-sells</label>
                  <div class="zpf-related-selected" data-related-selected>
                    @foreach($selectedLinkGroups['cross'] as $linkedProduct)
                      <button type="button" class="zpf-related-chip" data-related-id="{{ $linkedProduct->id }}">{{ $linkedProduct->name }}@if($linkedProduct->sku) <span>#{{ $linkedProduct->sku }}</span>@endif</button>
                    @endforeach
                  </div>
                  <input class="zpf-input" data-related-search list="product-link-options" placeholder="Search for a cross-sell product...">
                </div>
                <div class="zpf-related-field" data-related-field data-target-input="linked_products">
                  <input type="hidden" name="linked_products" value="{{ old('linked_products', implode(',', $editorState['linked_products'] ?? [])) }}">
                  <label class="zpf-note">Grouped / related products</label>
                  <div class="zpf-related-selected" data-related-selected>
                    @foreach($selectedLinkGroups['linked'] as $linkedProduct)
                      <button type="button" class="zpf-related-chip" data-related-id="{{ $linkedProduct->id }}">{{ $linkedProduct->name }}@if($linkedProduct->sku) <span>#{{ $linkedProduct->sku }}</span>@endif</button>
                    @endforeach
                  </div>
                  <input class="zpf-input" data-related-search list="product-link-options" placeholder="Search for grouped / related products...">
                </div>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="attributes">
              <div class="zpf-panel-block">
                <p class="zpf-note">Add descriptive information customers can use to search for this product, such as material or size.</p>
                <div class="zpf-attribute-toolbar">
                  <button type="button" class="zpf-btn" id="addAllAttributesBtn">Show all attributes</button>
                  <select class="zpf-input" id="attributePicker">
                    <option value="">Add existing</option>
                    @foreach($attributes as $attribute)
                      <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="zpf-panel-block zpf-attribute-groups">
                @foreach($attributes as $attribute)
                  @php
                    $selectedValues = collect(old("attribute_values.{$attribute->id}", $selectedAttributeValues->intersect($attribute->values->pluck('id'))->values()->all()));
                    $attributeConfig = old("attribute_config.{$attribute->id}", $editorState['attribute_config'][$attribute->id] ?? []);
                    $attributeActive = $selectedValues->isNotEmpty() || !empty($attributeConfig);
                  @endphp
                  <div class="zpf-attribute-group @if(!$attributeActive) is-collapsed-empty @endif" data-attribute-group data-attribute-id="{{ $attribute->id }}">
                    <div class="zpf-attribute-header">
                      <strong>{{ $attribute->name }}</strong>
                      <div class="zpf-attribute-flags">
                        <label class="zpf-check"><input type="checkbox" name="attribute_config[{{ $attribute->id }}][visible]" value="1" @checked(data_get($attributeConfig, 'visible', true))> Visible on the product page</label>
                        <label class="zpf-check"><input type="checkbox" name="attribute_config[{{ $attribute->id }}][variation]" value="1" @checked(data_get($attributeConfig, 'variation', false))> Used for variations</label>
                        <button type="button" class="zpf-link-btn" data-attribute-remove>Remove</button>
                      </div>
                    </div>
                    <div class="zpf-attribute-values">
                      @foreach($attribute->values as $value)
                        <label class="zpf-check">
                          <input type="checkbox" name="attribute_values[{{ $attribute->id }}][]" value="{{ $value->id }}" @checked($selectedValues->contains($value->id))>
                          {{ $value->value }}
                        </label>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
              <div class="zpf-attribute-actions">
                <button type="button" class="zpf-btn" id="saveAttributesBtn">Save attributes</button>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="variations">
              <div id="variable-section" class="zpf-panel-block hidden">
                <div class="zpf-variation-toolbar">
                  <button type="button" class="zpf-btn" id="generateVariationsBtn">Generate variations</button>
                  <span class="zpf-note">Select attribute values first, then generate combinations.</span>
                </div>
                <div id="variationRows" class="zpf-variation-rows"></div>
                <textarea name="variants_payload" id="variants-payload" class="hidden"></textarea>
              </div>
              <p id="variationsSimpleHint" class="zpf-note">Switch product type to Variable to configure variations.</p>
            </section>

            <section class="zpf-data-panel" data-panel="advanced">
              <div class="zpf-panel-block">
                <textarea name="purchase_note" rows="3" class="zpf-textarea" placeholder="Purchase note">{{ old('purchase_note', $editorState['purchase_note'] ?? '') }}</textarea>
                <div class="zpf-row zpf-row-2">
                  <input type="number" name="menu_order" value="{{ old('menu_order', $editorState['menu_order'] ?? 0) }}" class="zpf-input" placeholder="Menu order">
                  <label class="zpf-check"><input type="checkbox" name="enable_reviews" value="1" @checked(old('enable_reviews', $editorState['enable_reviews'] ?? true))> Enable reviews</label>
                </div>
                <div class="zpf-row zpf-row-2">
                  <label class="zpf-check"><input type="checkbox" name="pos_available" value="1" @checked(old('pos_available', $editorState['pos_available'] ?? true))> Available for POS</label>
                  <label class="zpf-check"><input type="checkbox" name="facebook_sync" value="1" @checked(old('facebook_sync', $editorState['facebook_sync'] ?? false))> Sync to Facebook catalog</label>
                </div>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="frequently-bought-together">
              <div class="zpf-panel-block">
                <div class="zpf-empty-state" data-bundle-empty>
                  <p class="zpf-note">No bundles found.</p>
                </div>
                <div class="zpf-related-field" data-related-field data-target-input="bundle_products">
                  <input type="hidden" name="bundle_products" value="{{ old('bundle_products', implode(',', $editorState['bundle_products'] ?? [])) }}">
                  <div class="zpf-related-selected" data-related-selected>
                    @foreach($selectedLinkGroups['bundle'] as $linkedProduct)
                      <button type="button" class="zpf-related-chip" data-related-id="{{ $linkedProduct->id }}">{{ $linkedProduct->name }}@if($linkedProduct->sku) <span>#{{ $linkedProduct->sku }}</span>@endif</button>
                    @endforeach
                  </div>
                  <input class="zpf-input" data-related-search list="product-link-options" placeholder="Search products to bundle together...">
                </div>
                <button type="button" class="zpf-link-btn" id="openBundlesManagerBtn">Open bundles manager</button>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="more-options">
              <div class="zpf-panel-block">
                <input name="meta_title" id="meta_title" value="{{ old('meta_title',$product->meta_title ?? '') }}" class="zpf-input" placeholder="Meta title">
                <textarea name="meta_description" id="meta_description" class="zpf-textarea" rows="3" placeholder="Meta description">{{ old('meta_description',$product->meta_description ?? '') }}</textarea>
                <div class="zpf-row zpf-row-2">
                  <input name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords',$product->meta_keywords ?? '') }}" class="zpf-input" placeholder="Meta keywords">
                  <button type="button" id="btn-ai-seo" class="zpf-btn">AI Generate SEO</button>
                </div>
              </div>
            </section>

            <section class="zpf-data-panel" data-panel="facebook">
              <div class="zpf-panel-block">
                <label class="zpf-check"><input type="checkbox" name="facebook_sync" value="1" @checked(old('facebook_sync', $editorState['facebook_sync'] ?? false))> Sync this product to Facebook Shop</label>
                <p class="zpf-note">Keep this section ready for future Facebook catalog fields.</p>
              </div>
            </section>
          </div>
        </div>
        </div>
      </article>

      <article class="zpf-card" data-box="short-desc-box" data-main-metabox-id="short-desc-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product short description</h3>
        </header>
        <div class="zpf-card-content">
        <div class="zpf-card-body">
          <textarea id="short_description" name="short_description" maxlength="500" rows="6" class="zpf-textarea zpf-editor shopadmin-text-editor" data-min-height="220" placeholder="Short description">{{ old('short_description',$product->short_description ?? '') }}</textarea>
        </div>
        </div>
      </article>
    </div>

    <aside class="zpf-side" id="zpfSidebarMetaboxes">
      <article class="zpf-card zpf-publish" data-box="publish-box" data-metabox-id="publish-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Publish</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body">
          <div class="zpf-publish-actions">
            <button type="submit" class="zpf-btn-lite" data-submit-action="draft">Save Draft</button>
            @if($isEditing)
              <a href="{{ url('/product/'.$product->slug) }}" class="zpf-btn-lite text-center" target="_blank" rel="noopener">Preview</a>
            @else
              <button type="button" class="zpf-btn-lite" disabled>Preview</button>
            @endif
          </div>
          <p class="zpf-meta-line">Status:
            <select name="publish_status" class="zpf-input">
              <option value="draft" @selected($publishStatusDefault === 'draft')>Draft</option>
              <option value="pending" @selected($publishStatusDefault === 'pending')>Pending review</option>
              <option value="publish" @selected($publishStatusDefault === 'publish')>Published</option>
              <option value="private" @selected($publishStatusDefault === 'private')>Private</option>
            </select>
          </p>
          <p class="zpf-meta-line">Visibility:
            <select name="catalog_visibility" class="zpf-input">
              <option value="visible" @selected(old('catalog_visibility', $editorState['catalog_visibility'] ?? 'visible') === 'visible')>Public</option>
              <option value="catalog" @selected(old('catalog_visibility', $editorState['catalog_visibility'] ?? '') === 'catalog')>Catalog</option>
              <option value="search" @selected(old('catalog_visibility', $editorState['catalog_visibility'] ?? '') === 'search')>Search</option>
              <option value="hidden" @selected(old('catalog_visibility', $editorState['catalog_visibility'] ?? '') === 'hidden')>Hidden</option>
            </select>
          </p>
          <input type="datetime-local" name="publish_on" value="{{ old('publish_on', $editorState['publish_on'] ?? '') }}" class="zpf-input">
          <label class="zpf-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$product->is_published ?? false))> Published</label>
          <label class="zpf-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$product->is_featured ?? false))> Featured</label>
          <label class="zpf-check"><input type="checkbox" name="is_todays_deal" value="1" @checked(old('is_todays_deal',$product->is_todays_deal ?? false))> Today's Deal</label>
        </div>
        <footer class="zpf-card-foot">
          <button class="zpf-btn zpf-btn-primary w-full" data-submit-action="publish">{{ $isEditing ? 'Update' : 'Publish' }}</button>
        </footer>
      </article>

      <article class="zpf-card" data-box="image-box" data-metabox-id="image-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product image</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body">
          <input type="hidden" id="remove_thumbnail" name="remove_thumbnail" value="0">
          <input type="hidden" id="thumbnail_media_id" name="thumbnail_media_id" value="{{ old('thumbnail_media_id', $product->thumbnail_media_id ?? '') }}">
          <button
            type="button"
            class="zpf-wp-media-link zpf-wp-set-image-link"
            data-toggle="media-picker"
            data-input="#thumbnail_media_id"
            data-preview="#thumbnailMediaPreview"
            data-type="image"
            data-multiple="false"
            data-modal-title="Product image"
            data-action-label="Set product image"
            @if(!empty($product?->thumbnail_url)) style="display:none" @endif
          >
            Set product image
          </button>
          <div id="thumbnailMediaPreview" class="zpf-media-preview-single mt-2">
            @if(!empty($product?->thumbnail_url))
              <div class="zpf-wp-image-card" @if(!empty($product?->thumbnail_media_id)) data-media-id="{{ $product->thumbnail_media_id }}" @endif>
                <img class="zpf-wp-image-main" src="{{ $product->thumbnail_url }}" alt="Thumbnail">
                <p class="zpf-wp-help">Click the image to edit or update</p>
                <button type="button" class="zpf-wp-remove-link">Remove product image</button>
                <button type="button" class="zpf-wp-video-btn">+ Video</button>
              </div>
            @endif
          </div>
        </div>
      </article>

      <article class="zpf-card" data-box="gallery-box" data-metabox-id="gallery-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product gallery</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body">
          <input type="hidden" id="delete_existing_gallery" name="delete_existing_gallery" value="0">
          <input type="hidden" id="gallery_media_ids" name="gallery_media_ids" value="{{ old('gallery_media_ids', isset($product) ? $product->images->pluck('media_asset_id')->filter()->implode(',') : '') }}">
          <div id="galleryMediaPreview" class="zpf-media-preview-gallery mt-2">
            @foreach(($product->images ?? collect())->take(8) as $image)
              <div class="zpf-wp-gallery-item" @if(!empty($image->media_asset_id)) data-media-id="{{ $image->media_asset_id }}" @endif>
                <img class="zpf-wp-gallery-thumb" src="{{ asset('storage/'.$image->image) }}" alt="Gallery">
              </div>
            @endforeach
          </div>
          <button
            type="button"
            class="zpf-wp-media-link mt-2"
            data-toggle="media-picker"
            data-input="#gallery_media_ids"
            data-preview="#galleryMediaPreview"
            data-type="image"
            data-multiple="true"
            data-modal-title="Product gallery"
            data-action-label="Add to gallery"
          >
            Add product gallery images
          </button>
        </div>
      </article>

      <article class="zpf-card" data-box="categories-box" data-metabox-id="categories-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product categories</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body zpf-tax-list">
          @foreach($categories as $c)
            <div class="zpf-cat-row">
              <label class="zpf-check">
                <input type="radio" name="category_id" value="{{ $c->id }}" @checked((string)old('category_id',$product->category_id ?? '') === (string)$c->id)>
                {{ $c->name }}
              </label>
              <label class="zpf-check zpf-check-sub">
                <input type="checkbox" name="additional_categories[]" value="{{ $c->id }}" @checked(in_array((string)$c->id, collect(old('additional_categories', isset($product) ? $product->categories->pluck('id')->all() : []))->map(fn($v)=>(string)$v)->all(), true))>
                Also add
              </label>
            </div>
          @endforeach
        </div>
      </article>

      <article class="zpf-card" data-box="tags-box" data-metabox-id="tags-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Product tags</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body">
          <textarea name="tags_input" rows="4" class="zpf-textarea" placeholder="Add tags separated by commas">{{ old('tags_input', implode(', ', $product->tags ?? [])) }}</textarea>
        </div>
      </article>

      <article class="zpf-card" data-box="brands-box" data-metabox-id="brands-box" draggable="true">
        <header class="zpf-card-head">
          <h3>Brands</h3>
          <span class="zpf-head-tools">
            <button type="button" class="zpf-collapse" data-collapse aria-label="Toggle panel"></button>
            <button type="button" class="zpf-drag-handle" data-drag-handle draggable="true" aria-label="Drag panel"></button>
          </span>
        </header>
        <div class="zpf-card-body">
          <select name="brand_id" class="zpf-input">
            <option value="">Select Brand</option>
            @foreach($brands as $b)
              <option value="{{ $b->id }}" @selected(old('brand_id',$product->brand_id ?? null)==$b->id)>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      </article>
    </aside>
    <datalist id="product-link-options">
      @foreach(collect($productOptions ?? [])->reject(fn ($option) => $isEditing && $option->id === $product->id) as $option)
        <option value="{{ $option->name }}{{ $option->sku ? ' (#'.$option->sku.')' : '' }}">{{ $option->id }}</option>
      @endforeach
    </datalist>
    <script type="application/json" id="product-link-options-data">@json(collect($productOptions ?? [])->reject(fn ($option) => $isEditing && $option->id === $product->id)->map(fn ($option) => ['id' => $option->id, 'name' => $option->name, 'sku' => $option->sku])->values())</script>
    <script type="application/json" id="existing-variants-data">@json(old('variants_payload') ? json_decode(old('variants_payload'), true) : $existingVariants)</script>
  </form>
</section>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
@endpush
@vite('resources/js/admin/product-form.js')
@endsection
