<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index()
    {
        $compareIds = session('compare_ids', []);
        $products = Product::query()->whereIn('id', $compareIds)->with(['category', 'brand', 'variants.attributeValues.attribute', 'images'])->get();
        $allAttributes = $products->flatMap(fn ($p) => $p->variants->flatMap(fn ($v) => $v->attributeValues->pluck('attribute')))->filter()->unique('id')->values();

        return view('frontend.compare.index', compact('products', 'allAttributes'));
    }

    public function toggle(Request $request): JsonResponse
    {
        $ids = session('compare_ids', []);
        $productId = (int) $request->integer('product_id');

        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_diff($ids, [$productId]));
            $action = 'removed';
        } else {
            if (count($ids) >= 4) {
                return response()->json(['success' => false, 'message' => 'Maximum 4 products to compare']);
            }
            $ids[] = $productId;
            $action = 'added';
        }

        session(['compare_ids' => array_values($ids)]);

        return response()->json(['success' => true, 'action' => $action, 'count' => count($ids), 'message' => $action === 'added' ? 'Added to compare!' : 'Removed from compare']);
    }

    public function clear(): JsonResponse
    {
        session()->forget('compare_ids');
        return response()->json(['success' => true, 'message' => 'Compare list cleared']);
    }
}
