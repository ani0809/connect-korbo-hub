<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\BuilderService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show(string $slug, BuilderService $builder, TrackingService $tracking)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->published()
            ->with([
                'category',
                'categories',
                'attributes.values',
                'brand',
                'seller.user',
                'variants.attributeValues.attribute',
                'images',
                'reviews' => fn ($q) => $q->where('is_approved', true)->with('user')->latest()->take(5),
            ])
            ->firstOrFail();

        Product::query()->where('id', $product->id)->increment('views');
        $tracking->trackViewContent($product);
        $this->trackRecentlyViewed($product->id);
        $variationData = $this->buildVariationData($product);
        $variationAttributes = $this->buildVariationAttributes($product);
        $visibleAttributes = $this->buildVisibleAttributes($product);
        $layoutConfig = $builder->getProductPageLayout($product);
        $editorState = (array) data_get($product->custom_tabs, 'editor', []);
        $relatedProducts = $this->resolveCuratedProducts(
            array_merge((array) ($editorState['upsell_products'] ?? []), (array) ($editorState['linked_products'] ?? [])),
            $product->id,
            8
        );
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()
                ->published()
                ->where('id', '!=', $product->id)
                ->where(function ($q) use ($product): void {
                    $q->where('category_id', $product->category_id)
                        ->orWhere('brand_id', $product->brand_id);
                })
                ->with(['images', 'variants', 'category', 'brand'])
                ->latest()
                ->take(8)
                ->get();
        }

        $frequentlyBought = $this->resolveCuratedProducts((array) ($editorState['bundle_products'] ?? []), $product->id, 3);
        if ($frequentlyBought->isEmpty()) {
            $frequentlyBought = Product::query()
                ->published()
                ->where('id', '!=', $product->id)
                ->where('category_id', $product->category_id)
                ->with(['images', 'variants', 'category', 'brand'])
                ->inRandomOrder()
                ->take(3)
                ->get();
        }

        $recentlyViewedProducts = collect(session('recently_viewed', []))
            ->reject(fn ($id) => (int) $id === (int) $product->id)
            ->take(8)
            ->pipe(function ($ids) {
                if ($ids->isEmpty()) return collect();
                return Product::query()
                    ->published()
                    ->whereIn('id', $ids->all())
                    ->with(['images', 'variants', 'category', 'brand'])
                    ->get();
            });

        $pendingReviewOrderItem = null;
        if (auth()->check()) {
            $pendingReviewOrderItem = OrderItem::query()
                ->where('is_reviewed', false)
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('order_status', 'delivered'))
                ->first();
        }

        return view('frontend.product.show', compact(
            'product',
            'variationData',
            'variationAttributes',
            'visibleAttributes',
            'layoutConfig',
            'pendingReviewOrderItem',
            'relatedProducts',
            'frequentlyBought',
            'recentlyViewedProducts'
        ));
    }

    public function quickView(int $id): JsonResponse
    {
        $product = Product::query()->with(['images', 'variants.attributeValues'])->findOrFail($id);
        $html = view('frontend.partials.quick-view', compact('product'))->render();

        return response()->json(['success' => true, 'message' => 'Quick view loaded', 'data' => ['html' => $html]]);
    }

    private function trackRecentlyViewed(int $productId): void
    {
        $ids = array_values(array_unique(array_merge([$productId], session('recently_viewed', []))));
        session(['recently_viewed' => array_slice($ids, 0, 20)]);
    }

    private function buildVariationData(Product $product): array
    {
        return $product->variants
            ->sortBy('sort_order')
            ->values()
            ->map(function ($variant) use ($product) {
                return [
                'id' => $variant->id,
                'price' => (float) $variant->price,
                'sale_price' => $variant->sale_price ? (float) $variant->sale_price : null,
                'stock' => (int) $variant->stock,
                'sku' => $variant->sku,
                'image' => $variant->image ? asset('storage/'.$variant->image) : null,
                'is_available' => $product->allowsBackorders() || (int) $variant->stock > 0,
                'attributes' => $variant->attributeValues
                    ->mapWithKeys(fn ($value) => [(string) $value->attribute_id => (int) $value->id])
                    ->all(),
                'attribute_value_ids' => $variant->attributeValues->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            ];
            })
            ->all();
    }

    private function buildVariationAttributes(Product $product): array
    {
        $attributeConfig = collect(data_get($product->custom_tabs, 'editor.attribute_config', []));
        $selectedValuesByAttribute = $product->variants
            ->flatMap(fn ($variant) => $variant->attributeValues)
            ->groupBy('attribute_id');

        return $product->attributes
            ->map(function ($attribute) use ($attributeConfig, $selectedValuesByAttribute) {
                $selectedValues = collect($selectedValuesByAttribute->get($attribute->id, []))
                    ->unique('id')
                    ->sortBy('sort_order')
                    ->values();

                if ($selectedValues->isEmpty()) {
                    return null;
                }

                $config = (array) $attributeConfig->get($attribute->id, []);
                $usedForVariation = (bool) ($config['variation'] ?? true);
                if (! $usedForVariation) {
                    return null;
                }

                return [
                    'id' => (int) $attribute->id,
                    'name' => $attribute->name,
                    'values' => $selectedValues->map(fn ($value) => [
                        'id' => (int) $value->id,
                        'name' => $value->value,
                        'slug' => $value->slug,
                        'color' => $value->color_code,
                    ])->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildVisibleAttributes(Product $product): array
    {
        $attributeConfig = collect(data_get($product->custom_tabs, 'editor.attribute_config', []));
        $selectedValuesByAttribute = collect(data_get($product->custom_tabs, 'editor.attribute_values', []))
            ->map(fn ($ids) => collect((array) $ids)->map(fn ($id) => (int) $id)->filter()->values());

        return $product->attributes
            ->map(function ($attribute) use ($attributeConfig, $selectedValuesByAttribute) {
                $config = (array) $attributeConfig->get($attribute->id, []);
                $visible = (bool) ($config['visible'] ?? false);
                if (! $visible) {
                    return null;
                }

                $values = $attribute->values
                    ->whereIn('id', $selectedValuesByAttribute->get($attribute->id, collect())->all())
                    ->sortBy('sort_order')
                    ->pluck('value')
                    ->values();

                if ($values->isEmpty()) {
                    return null;
                }

                return [
                    'name' => $attribute->name,
                    'values' => $values->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveCuratedProducts(array $ids, int $excludeId, int $limit)
    {
        $orderedIds = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $excludeId)
            ->unique()
            ->take($limit)
            ->values();

        if ($orderedIds->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->published()
            ->whereIn('id', $orderedIds->all())
            ->with(['images', 'variants', 'category', 'brand'])
            ->get()
            ->keyBy('id');

        return $orderedIds
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->values();
    }
}
