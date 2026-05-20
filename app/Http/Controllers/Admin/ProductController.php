<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\MediaAsset;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with(['category', 'brand', 'seller', 'variants']);
        $perPage = max(10, min(100, (int) $request->integer('per_page', 20)));

        if ($request->filled('status')) {
            $query->where('is_published', $request->string('status')->value() === 'published');
        }
        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->category);
        }
        if ($request->filled('brand')) {
            $query->where('brand_id', (int) $request->brand);
        }
        if ($request->filled('seller')) {
            $query->where('seller_id', (int) $request->seller);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->value());
        }
        if ($request->filled('stock_status')) {
            $request->string('stock_status')->value() === 'in'
                ? $query->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                : $query->whereHas('variants', fn ($q) => $q->where('stock', '<=', 0));
        }
        if ($request->filled('search')) {
            $s = '%'.$request->string('search')->value().'%';
            $query->where(fn ($q) => $q->where('name', 'like', $s)->orWhere('sku', 'like', $s)->orWhere('barcode', 'like', $s));
        }

        match ($request->string('sort', 'newest')->value()) {
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            'sales' => $query->orderByDesc('total_sales'),
            'stock' => $query->withSum('variants', 'stock')->orderByDesc('variants_sum_stock'),
            'price' => $query->withMin('variants', 'price')->orderBy('variants_min_price'),
            default => $query->latest(),
        };

        return view('admin.products.index', [
            'products' => $query->paginate($perPage)->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'sellers' => Seller::query()->orderBy('shop_name')->get(),
            'filters' => $request->all(),
            'perPage' => $perPage,
            'listCounts' => [
                'all' => Product::query()->count(),
                'published' => Product::query()->where('is_published', true)->count(),
                'draft' => Product::query()->where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($request, $data): void {
            $product = new Product();
            $this->fillProduct($product, $data, $request);
            $product->save();

            $this->syncCategories($product, $data);
            $this->syncVariants($product, $data);
            $this->syncAttributes($product, $data);
            $this->syncGallery($product, $request);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(int $id): View
    {
        $product = Product::query()->with(['images', 'variants.attributeValues', 'categories'])->findOrFail($id);
        return view('admin.products.form', array_merge($this->formData(), ['product' => $product]));
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        DB::transaction(function () use ($id, $request, $data): void {
            $product = Product::query()->with(['images', 'variants'])->findOrFail($id);
            $this->fillProduct($product, $data, $request);
            $product->save();

            $this->syncCategories($product, $data);
            $this->syncVariants($product, $data, true);
            $this->syncAttributes($product, $data);
            $this->syncGallery($product, $request, true);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $hasPending = $product->orderItems()->whereHas('order', fn ($q) => $q->whereIn('order_status', ['pending', 'processing']))->exists();
        if ($hasPending) {
            return back()->with('error', 'Cannot delete product with pending orders.');
        }
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $payload = $request->validate(['action' => 'required|string', 'ids' => 'required|array']);
        $ids = array_map('intval', $payload['ids']);
        $q = Product::query()->whereIn('id', $ids);

        match ($payload['action']) {
            'publish' => $q->update(['is_published' => true]),
            'unpublish' => $q->update(['is_published' => false]),
            'feature' => $q->update(['is_featured' => true]),
            'unfeatured' => $q->update(['is_featured' => false]),
            'delete' => $q->delete(),
            default => null,
        };

        return response()->json(['success' => true, 'message' => 'Bulk action applied.']);
    }

    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $head = array_map('trim', $rows[0] ?? []);
        $report = ['created' => 0, 'errors' => []];

        foreach (array_slice($rows, 1) as $i => $row) {
            try {
                $item = array_combine($head, $row);
                Product::query()->create([
                    'name' => $item['name'] ?? 'Untitled',
                    'slug' => Str::slug(($item['name'] ?? 'product').'-'.Str::random(5)),
                    'type' => $item['type'] ?? 'simple',
                    'category_id' => (int) ($item['category_id'] ?? 1),
                    'is_published' => true,
                ]);
                $report['created']++;
            } catch (\Throwable $e) {
                $report['errors'][] = ['row' => $i + 2, 'message' => $e->getMessage()];
            }
        }

        return response()->json(['success' => true, 'report' => $report]);
    }

    public function exportProducts(Request $request)
    {
        $products = Product::query()->with('variants')->latest()->get();
        $csv = "id,name,sku,type,category,brand,published,variant_count\n";
        foreach ($products as $p) {
            $csv .= implode(',', [$p->id, '"'.str_replace('"', '""', $p->name).'"', $p->sku, $p->type, $p->category_id, $p->brand_id, (int) $p->is_published, $p->variants->count()])."\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=products.csv']);
    }

    public function generateSku(Request $request): JsonResponse
    {
        $cat = strtoupper(Str::limit((string) Category::query()->find($request->integer('category_id'))?->name, 3, ''));
        do {
            $sku = 'PRD-'.($cat ?: 'GEN').'-'.strtoupper(Str::random(6));
        } while (ProductVariant::query()->where('sku', $sku)->exists() || Product::query()->where('sku', $sku)->exists());

        return response()->json(['success' => true, 'sku' => $sku]);
    }

    public function aiGenerateContent(Request $request): JsonResponse
    {
        return app(ProductAiController::class)->generate($request);
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $productId = $updating ? (int) $request->route('id') : null;
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('products', 'slug')->ignore($productId)],
            'type' => 'required|in:simple,variable,digital,classified',
            'category_id' => 'required|exists:categories,id',
            'additional_categories' => 'nullable|array',
            'additional_categories.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'stock' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric',
            'tax_rate' => 'nullable|numeric',
            'tax_type' => 'nullable|in:flat,percent',
            'shipping_type' => 'nullable|in:free,flat,product_wise',
            'shipping_cost' => 'nullable|numeric',
            'estimated_delivery' => 'nullable|string|max:191',
            'club_point' => 'nullable|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'tags_input' => 'nullable|string',
            'publish_status' => 'nullable|in:draft,pending,publish,private',
            'submit_action' => 'nullable|in:draft,publish',
            'catalog_visibility' => 'nullable|in:visible,catalog,search,hidden',
            'publish_on' => 'nullable|date',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at' => 'nullable|date',
            'purchase_note' => 'nullable|string',
            'menu_order' => 'nullable|integer',
            'enable_reviews' => 'nullable|boolean',
            'manage_stock' => 'nullable|boolean',
            'stock_status' => 'nullable|in:instock,outofstock,onbackorder',
            'backorder_mode' => 'nullable|in:no,notify,yes',
            'sold_individually' => 'nullable|boolean',
            'virtual' => 'nullable|boolean',
            'downloadable' => 'nullable|boolean',
            'linked_products' => 'nullable|string',
            'upsell_products' => 'nullable|string',
            'cross_sell_products' => 'nullable|string',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shipping_class' => 'nullable|string|max:191',
            'pos_available' => 'nullable|boolean',
            'bundle_products' => 'nullable|string',
            'facebook_sync' => 'nullable|boolean',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'array',
            'attribute_values.*.*' => 'integer|exists:attribute_values,id',
            'attribute_config' => 'nullable|array',
            'attribute_config.*.variation' => 'nullable|boolean',
            'attribute_config.*.visible' => 'nullable|boolean',
            'variants_payload' => 'nullable|string',
            'remove_thumbnail' => 'nullable|boolean',
            'delete_existing_gallery' => 'nullable|boolean',
            'thumbnail_media_id' => 'nullable|integer|exists:media_assets,id',
            'gallery_media_ids' => 'nullable|string',
            'thumbnail' => $updating ? 'nullable|image|max:2048|mimes:jpg,png,webp,jpeg' : 'nullable|image|max:2048|mimes:jpg,png,webp,jpeg',
            'gallery.*' => 'nullable|image|max:2048',
        ]);

        $data['variants'] = $this->normalizeVariantPayload($request->input('variants_payload'));

        if (($data['type'] ?? 'simple') === 'variable' && empty($data['variants'])) {
            throw ValidationException::withMessages([
                'variants_payload' => 'Generate at least one variation for a variable product.',
            ]);
        }

        return $data;
    }

    private function fillProduct(Product $product, array $data, Request $request): void
    {
        $soldIndividually = (bool) $request->boolean('sold_individually');
        $minPurchaseQty = $soldIndividually ? 1 : (int) $request->integer('min_purchase_qty', 1);
        $maxPurchaseQty = $soldIndividually ? 1 : ($request->integer('max_purchase_qty') ?: null);
        if ($maxPurchaseQty !== null && $maxPurchaseQty < $minPurchaseQty) {
            $maxPurchaseQty = $minPurchaseQty;
        }

        $product->fill([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: ($product->slug ?: Str::slug($data['name']).'-'.Str::lower(Str::random(4))),
            'type' => $data['type'],
            'category_id' => (int) $data['category_id'],
            'brand_id' => $data['brand_id'] ?? null,
            'sku' => $request->string('sku')->value() ?: $product->sku,
            'barcode' => $request->string('barcode')->value() ?: null,
            'short_description' => $request->string('short_description')->value() ?: null,
            'description' => $request->input('description'),
            'is_published' => $this->resolvePublishedState($request),
            'is_featured' => (bool) $request->boolean('is_featured'),
            'is_todays_deal' => (bool) $request->boolean('is_todays_deal'),
            'min_purchase_qty' => $minPurchaseQty,
            'max_purchase_qty' => $maxPurchaseQty,
            'estimated_delivery' => $request->string('estimated_delivery')->value() ?: null,
            'club_point' => (int) $request->integer('club_point', 0),
            'unit' => $request->string('unit')->value() ?: null,
            'weight' => $request->filled('weight') ? (float) $request->input('weight') : null,
            'tax_rate' => (float) $request->input('tax_rate', 0),
            'tax_type' => $request->string('tax_type')->value() ?: 'percent',
            'shipping_type' => $request->string('shipping_type')->value() ?: 'free',
            'shipping_cost' => (float) $request->input('shipping_cost', 0),
            'meta_title' => $request->string('meta_title')->value() ?: null,
            'meta_description' => $request->string('meta_description')->value() ?: null,
            'meta_keywords' => $request->string('meta_keywords')->value() ?: null,
            'thumbnail_media_id' => $request->integer('thumbnail_media_id') ?: null,
            'tags' => $this->parseTags($request->string('tags_input')->value()),
        ]);

        $customTabs = is_array($product->custom_tabs) ? $product->custom_tabs : [];
        $customTabs['editor'] = [
            'publish_status' => $this->resolvePublishStatus($request, $product),
            'catalog_visibility' => $request->string('catalog_visibility')->value() ?: 'visible',
            'publish_on' => $request->string('publish_on')->value() ?: null,
            'sale_starts_at' => $request->string('sale_starts_at')->value() ?: null,
            'sale_ends_at' => $request->string('sale_ends_at')->value() ?: null,
            'purchase_note' => $request->string('purchase_note')->value() ?: null,
            'menu_order' => (int) $request->integer('menu_order', 0),
            'enable_reviews' => $request->has('enable_reviews') ? (bool) $request->boolean('enable_reviews') : true,
            'manage_stock' => (bool) $request->boolean('manage_stock'),
            'stock_status' => $request->string('stock_status')->value() ?: 'instock',
            'backorder_mode' => $request->string('backorder_mode')->value() ?: 'no',
            'sold_individually' => $soldIndividually,
            'virtual' => (bool) $request->boolean('virtual'),
            'downloadable' => (bool) $request->boolean('downloadable'),
            'linked_products' => $this->parseIdCsv($request->string('linked_products')->value()),
            'upsell_products' => $this->parseIdCsv($request->string('upsell_products')->value()),
            'cross_sell_products' => $this->parseIdCsv($request->string('cross_sell_products')->value()),
            'dimensions' => [
                'length' => $request->filled('length') ? (float) $request->input('length') : null,
                'width' => $request->filled('width') ? (float) $request->input('width') : null,
                'height' => $request->filled('height') ? (float) $request->input('height') : null,
            ],
            'shipping_class' => $request->string('shipping_class')->value() ?: null,
            'attribute_config' => $request->input('attribute_config', []),
            'attribute_values' => $this->normalizeAttributeValuesByAttribute($request->input('attribute_values', [])),
            'pos_available' => $request->has('pos_available') ? (bool) $request->boolean('pos_available') : true,
            'bundle_products' => $this->parseIdCsv($request->string('bundle_products')->value()),
            'facebook_sync' => (bool) $request->boolean('facebook_sync'),
        ];
        $product->custom_tabs = $customTabs;

        if ($product->thumbnail_media_id) {
            $asset = MediaAsset::query()->find($product->thumbnail_media_id);
            if ($asset) {
                $product->thumbnail = $asset->path;
            }
        }

        if ($request->boolean('remove_thumbnail') && ! $request->hasFile('thumbnail') && ! $product->thumbnail_media_id) {
            $product->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            $product->thumbnail = $request->file('thumbnail')->store('products/thumbs', 'public');
            $product->thumbnail_media_id = null;
        }
    }

    private function syncCategories(Product $product, array $data): void
    {
        $ids = array_unique(array_filter(array_merge([$product->category_id], $data['additional_categories'] ?? [])));
        $product->categories()->sync($ids);
    }

    private function syncVariants(Product $product, array $data, bool $replace = false): void
    {
        if ($product->type === 'simple') {
            $variant = $product->variants()->orderBy('id')->first() ?: new ProductVariant(['product_id' => $product->id]);
            $variant->fill([
                'sku' => $product->sku ?: strtoupper(Str::random(8)),
                'price' => (float) ($data['price'] ?? 0),
                'sale_price' => $data['sale_price'] ?? null,
                'sale_starts_at' => request()->input('sale_starts_at') ?: null,
                'sale_ends_at' => request()->input('sale_ends_at') ?: null,
                'stock' => (int) request()->integer('stock', 0),
                'low_stock_threshold' => (int) request()->integer('low_stock_threshold', 5),
                'sort_order' => 0,
            ]);
            $variant->product_id = $product->id;
            $variant->save();
            $keepId = $variant->id;
            $product->variants()->where('id', '!=', $keepId)->get()->each(function (ProductVariant $other): void {
                if (OrderItem::query()->where('product_variant_id', $other->id)->exists()) {
                    $other->update(['stock' => 0]);
                    return;
                }
                $other->delete();
            });
            return;
        }

        if ($product->type === 'variable') {
            $submittedIds = [];
            foreach (($data['variants'] ?? []) as $index => $variant) {
                $variantId = (int) ($variant['id'] ?? 0);
                $v = $variantId ? $product->variants()->find($variantId) : null;
                if (! $v) {
                    $v = new ProductVariant(['product_id' => $product->id]);
                }
                $v->fill([
                    'sku' => $variant['sku'] ?? strtoupper(Str::random(8)),
                    'price' => (float) ($variant['price'] ?? 0),
                    'sale_price' => $variant['sale_price'] ?? null,
                    'sale_starts_at' => $variant['sale_starts_at'] ?? null,
                    'sale_ends_at' => $variant['sale_ends_at'] ?? null,
                    'stock' => (int) ($variant['stock'] ?? 0),
                    'low_stock_threshold' => (int) ($variant['low_stock_threshold'] ?? request()->integer('low_stock_threshold', 5)),
                    'sort_order' => $index,
                ]);
                $v->product_id = $product->id;
                $v->save();
                $submittedIds[] = $v->id;
                if (! empty($variant['attribute_value_ids']) && is_array($variant['attribute_value_ids'])) {
                    $v->attributeValues()->sync(array_values(array_unique(array_map('intval', $variant['attribute_value_ids']))));
                } else {
                    $v->attributeValues()->sync([]);
                }
            }

            $product->variants()->whereNotIn('id', $submittedIds)->get()->each(function (ProductVariant $variant): void {
                if (OrderItem::query()->where('product_variant_id', $variant->id)->exists()) {
                    $variant->update(['stock' => 0]);
                    return;
                }
                $variant->delete();
            });
            return;
        }

        $product->variants()->get()->each(function (ProductVariant $variant): void {
            if (OrderItem::query()->where('product_variant_id', $variant->id)->exists()) {
                $variant->update(['stock' => 0]);
                return;
            }
            $variant->delete();
        });
    }

    private function syncAttributes(Product $product, array $data): void
    {
        $attributeValues = $data['attribute_values'] ?? [];
        $attributeIds = [];
        foreach ($attributeValues as $values) {
            foreach ((array) $values as $valueId) {
                $attr = AttributeValue::query()->find((int) $valueId)?->attribute_id;
                if ($attr) {
                    $attributeIds[] = $attr;
                }
            }
        }
        $product->attributes()->sync(array_unique($attributeIds));
    }

    private function parseTags(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function parseIdCsv(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($id) => (int) trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeAttributeValuesByAttribute(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->mapWithKeys(function ($ids, $attributeId) {
                return [
                    (int) $attributeId => collect((array) $ids)
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn ($ids) => ! empty($ids))
            ->all();
    }

    private function resolvePublishedState(Request $request): bool
    {
        if ($request->string('submit_action')->value() === 'draft') {
            return false;
        }

        $status = $request->string('publish_status')->value();

        return $status === 'publish' || (bool) $request->boolean('is_published');
    }

    private function resolvePublishStatus(Request $request, Product $product): string
    {
        if ($request->string('submit_action')->value() === 'draft') {
            return 'draft';
        }

        return $request->string('publish_status')->value() ?: ($product->is_published ? 'publish' : 'draft');
    }

    private function normalizeVariantPayload(mixed $payload): array
    {
        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($variant) => is_array($variant))
            ->map(function (array $variant): array {
                return [
                    'id' => isset($variant['id']) ? (int) $variant['id'] : null,
                    'sku' => trim((string) ($variant['sku'] ?? '')) ?: null,
                    'price' => (float) ($variant['price'] ?? 0),
                    'sale_price' => ($variant['sale_price'] ?? '') !== '' ? (float) $variant['sale_price'] : null,
                    'sale_starts_at' => ! empty($variant['sale_starts_at']) ? $variant['sale_starts_at'] : null,
                    'sale_ends_at' => ! empty($variant['sale_ends_at']) ? $variant['sale_ends_at'] : null,
                    'stock' => (int) ($variant['stock'] ?? 0),
                    'low_stock_threshold' => (int) ($variant['low_stock_threshold'] ?? 5),
                    'attribute_value_ids' => collect($variant['attribute_value_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function syncGallery(Product $product, Request $request, bool $replace = false): void
    {
        if ($replace && $request->boolean('delete_existing_gallery')) {
            $product->images()->delete();
        }

        $galleryMediaIds = collect(explode(',', (string) $request->input('gallery_media_ids', '')))
            ->map(fn ($id) => (int) trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($replace) {
            $keepMediaIds = $galleryMediaIds->all();
            $product->images()->get()->each(function (ProductImage $image) use ($keepMediaIds): void {
                if ($image->media_asset_id && in_array((int) $image->media_asset_id, $keepMediaIds, true)) {
                    return;
                }
                if (OrderItem::query()->where('thumbnail', 'like', '%'.$image->image.'%')->exists()) {
                    return;
                }
                $image->delete();
            });
        }

        foreach ($galleryMediaIds as $idx => $mediaId) {
            $asset = MediaAsset::query()->find($mediaId);
            if (! $asset) {
                continue;
            }
            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'media_asset_id' => $asset->id],
                ['image' => $asset->path, 'sort_order' => $idx]
            );
        }

        foreach (($request->file('gallery') ?? []) as $idx => $image) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'image' => $image->store('products/gallery', 'public'),
                'sort_order' => $idx,
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'categories' => Category::query()->with('children')->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'attributes' => Attribute::query()->with('values')->orderBy('name')->get(),
            'productOptions' => Product::query()
                ->select(['id', 'name', 'sku'])
                ->orderBy('name')
                ->get(),
            'flashDeals' => FlashDeal::query()->where('is_active', true)->orderByDesc('id')->get(),
            'sellers' => Seller::query()->orderBy('shop_name')->get(),
        ];
    }
}
