<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\MediaAsset;
use App\Services\CartService;
use App\Services\ClubPointsService;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()->where('user_id', auth()->id())
            ->with(['items' => fn ($q) => $q->with(['product', 'variant'])])
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('order_status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('order_number', 'like', '%'.$request->search.'%'))
            ->latest()->paginate(10);
        return view('frontend.account.orders.index', compact('orders'));
    }

    public function show(string $number)
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => auth()->id()])
            ->with(['items.product', 'items.variant.attributeValues.attribute', 'items.seller', 'statusHistory', 'transactions'])
            ->firstOrFail();
        return view('frontend.account.orders.show', compact('order'));
    }

    public function cancel(Request $request, string $number)
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => auth()->id()])->with('items')->firstOrFail();
        if (! in_array($order->order_status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'This order cannot be cancelled');
        }

        DB::transaction(function () use ($order, $request): void {
            $order->update(['order_status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => $request->input('reason')]);
            foreach ($order->items as $item) {
                if (! $item->product?->managesStock()) {
                    continue;
                }

                $resolvedVariant = $item->product?->resolvePurchasableVariant($item->variant);

                if ($resolvedVariant) {
                    ProductVariant::query()->where('id', $resolvedVariant->id)->increment('stock', $item->quantity);
                } else {
                    Product::query()->where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
            if ($order->coupon_id) {
                Coupon::query()->where('id', $order->coupon_id)->decrement('used_count');
            }
            if ($order->payment_status === 'paid') {
                $order->update(['payment_status' => 'refunded']);
            }
            app(ClubPointsService::class)->handleOrderCancellation($order->fresh());
            $order->statusHistory()->create([
                'status' => 'cancelled',
                'comment' => $request->input('reason', 'Cancelled by customer'),
                'changed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Order cancelled successfully');
    }

    public function reorder(string $number, CartService $cartService)
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => auth()->id()])->with(['items.product', 'items.variant'])->firstOrFail();
        $added = 0;
        $failed = [];

        foreach ($order->items as $item) {
            if (! $item->product?->is_published) {
                $failed[] = $item->product_name.' (no longer available)';
                continue;
            }
            $result = $cartService->addItem((int) $item->product_id, (int) $item->quantity, $item->product_variant_id ? (int) $item->product_variant_id : null);
            if ($result['success']) {
                $added++;
            } else {
                $failed[] = $item->product_name.': '.$result['message'];
            }
        }

        $message = $added.' item(s) added to cart';
        if ($failed !== []) {
            $message .= '. Could not add: '.implode(', ', $failed);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function repay(string $number, PaymentService $paymentService)
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => auth()->id()])->firstOrFail();
        if (! in_array($order->payment_status, ['pending', 'failed'], true)) {
            return back()->with('error', 'Payment already processed');
        }

        $gateway = $paymentService->getGateway((string) $order->payment_method);
        $result = $gateway->createPayment($order);

        return redirect($result->redirectUrl ?? route('payment.failed', $order->id));
    }

    public function invoice(string $number, OrderService $orderService)
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => auth()->id()])->with(['items.product', 'items.variant'])->firstOrFail();
        $pdf = $orderService->generateInvoice($order);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-'.$order->order_number.'.pdf"',
        ]);
    }

    public function review(Request $request, int $id, ImageService $imageService)
    {
        abort_unless(setting('reviews_enabled', true), 403);

        $item = OrderItem::query()->where(['id' => $id, 'is_reviewed' => false])
            ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('order_status', 'delivered'))
            ->firstOrFail();

        $maxImages = (int) setting('review_max_images', 5);
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'comment' => 'required|string|min:10|max:1000',
            'images' => 'nullable|array|max:'.$maxImages,
            'images.*' => 'nullable|image|max:2048',
            'image_media_ids' => 'nullable|string',
        ]);

        DB::transaction(function () use ($item, $request, $imageService, $maxImages): void {
            $images = [];
            $imageMediaIds = collect(explode(',', (string) $request->input('image_media_ids', '')))
                ->map(fn ($id) => (int) trim((string) $id))
                ->filter()
                ->unique()
                ->take($maxImages)
                ->values();

            if ($imageMediaIds->isNotEmpty()) {
                $assets = MediaAsset::query()->whereIn('id', $imageMediaIds->all())->get()->keyBy('id');
                $imageMediaIds = $imageMediaIds->filter(fn ($id) => isset($assets[$id]))->values();
                $images = $imageMediaIds->map(fn ($id) => $assets[$id]->path)->values()->all();
            }

            if (setting('review_allow_images', true) && $request->hasFile('images')) {
                foreach (array_slice($request->file('images') ?? [], 0, $maxImages) as $img) {
                    $images[] = $imageService->upload($img, 'reviews');
                }
            }

            $title = setting('review_allow_title', true) ? $request->input('title') : null;

            Review::query()->create([
                'product_id' => $item->product_id,
                'user_id' => auth()->id(),
                'order_item_id' => $item->id,
                'rating' => $request->integer('rating'),
                'title' => $title,
                'comment' => $request->input('comment'),
                'images' => $images,
                'image_media_ids' => $imageMediaIds->all(),
                'is_verified_purchase' => true,
                'is_approved' => ! setting('review_approval_required', false),
                'is_rejected' => false,
            ]);

            $item->update(['is_reviewed' => true]);

            $approved = Review::query()
                ->where('product_id', $item->product_id)
                ->where('is_approved', true)
                ->where('is_rejected', false);
            Product::query()->where('id', $item->product_id)->update([
                'rating' => round((float) $approved->avg('rating'), 2),
                'total_reviews' => $approved->count(),
            ]);
        });

        return back()->with('success', 'Review submitted!');
    }
}
