<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->published()->with(['category', 'brand', 'seller', 'images', 'variants']);

        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->category);
        }
        if ($request->filled('brand')) {
            $query->where('brand_id', (int) $request->brand);
        }
        if ($request->filled('seller')) {
            $query->where('seller_id', (int) $request->seller);
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where('name', 'like', "%{$q}%");
        }

        $sort = $request->string('sort', 'newest');
        match ($sort->value()) {
            'price_low' => $query->withMin('variants', 'price')->orderBy('variants_min_price'),
            'price_high' => $query->withMax('variants', 'price')->orderByDesc('variants_max_price'),
            'popular' => $query->orderByDesc('total_sales'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->latest(),
        };

        $paginator = $query->paginate((int) $request->input('per_page', 20));

        $data = ProductResource::collection($paginator->items());

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->published()
            ->with(['category', 'brand', 'seller.user', 'images', 'variants.attributeValues.attribute'])
            ->firstOrFail();

        return $this->success(new ProductResource($product));
    }

    public function reviews(int $id): JsonResponse
    {
        $product = Product::query()->published()->findOrFail($id);
        $reviews = Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        $mapped = $reviews->getCollection()->map(fn ($r) => [
            'id' => $r->id,
            'rating' => (float) $r->rating,
            'title' => $r->title,
            'comment' => $r->comment,
            'user' => $r->user?->only(['id', 'name']),
            'created_at' => $r->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $mapped,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
                'has_more' => $reviews->hasMorePages(),
            ],
        ]);
    }

    public function qna(int $id): JsonResponse
    {
        $product = Product::query()->published()->findOrFail($id);
        $questions = ProductQuestion::query()
            ->where('product_id', $product->id)
            ->approved()
            ->with(['user:id,name', 'answers' => fn ($q) => $q->where('is_approved', true)->with('user:id,name')])
            ->latest()
            ->paginate(15);

        $questions->getCollection()->transform(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'user' => $q->user?->only(['id', 'name']),
            'answers' => $q->answers->map(fn ($a) => [
                'id' => $a->id,
                'answer' => $a->answer,
                'user' => $a->user?->only(['id', 'name']),
            ]),
            'created_at' => $q->created_at?->toIso8601String(),
        ]);

        return $this->paginated($questions);
    }
}
