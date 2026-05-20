<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Support\Collection;

class PromotionEngine
{
    private array $appliedPromotions = [];
    private float $totalDiscount = 0.0;
    private array $freeItems = [];
    private array $messages = [];

    public function analyze(Collection $cartItems, ?User $user = null, float $cartTotal = 0): array
    {
        $this->appliedPromotions = [];
        $this->totalDiscount = 0;
        $this->freeItems = [];
        $this->messages = [];

        $promotions = Promotion::active()->orderByDesc('priority')->get();

        foreach ($promotions as $promotion) {
            if (! $this->checkUsageLimit($promotion, $user)) {
                continue;
            }

            $result = $this->applyPromotion($promotion, $cartItems, $user, $cartTotal);
            if (! ($result['applied'] ?? false)) {
                continue;
            }

            $this->appliedPromotions[] = $promotion;
            $this->totalDiscount += (float) ($result['discount'] ?? 0);
            $this->freeItems = array_merge($this->freeItems, $result['free_items'] ?? []);
            if (! empty($result['message'])) {
                $this->messages[] = $result['message'];
            }

            if (! $promotion->is_stackable) {
                break;
            }
        }

        return [
            'promotions' => $this->appliedPromotions,
            'total_discount' => round($this->totalDiscount, 2),
            'free_items' => $this->freeItems,
            'messages' => $this->messages,
            'summary' => $this->buildSummary(),
        ];
    }

    private function applyPromotion(Promotion $promotion, Collection $cartItems, ?User $user, float $cartTotal): array
    {
        if ($promotion->minimum_cart_amount && $cartTotal < (float) $promotion->minimum_cart_amount) {
            return ['applied' => false];
        }

        if (! $promotion->isCurrentlyActive()) {
            return ['applied' => false];
        }

        $applicableItems = $this->getApplicableItems($promotion, $cartItems);
        if ($applicableItems->isEmpty()) {
            return ['applied' => false];
        }

        return match ($promotion->type) {
            'buy_x_get_y' => $this->applyBuyXGetY($promotion, $applicableItems),
            'quantity_discount' => $this->applyQuantityDiscount($promotion, $applicableItems),
            'bundle' => $this->applyBundle($promotion, $applicableItems),
            'free_shipping' => $this->applyFreeShipping($promotion, $applicableItems, $cartTotal),
            'combo_discount' => $this->applyComboDiscount($promotion, $applicableItems),
            default => ['applied' => false],
        };
    }

    private function applyBuyXGetY(Promotion $promotion, Collection $items): array
    {
        $conditions = $promotion->conditions ?? [];
        $rewards = $promotion->rewards ?? [];
        $buyQty = max(1, (int) ($conditions['buy_quantity'] ?? 2));
        $getQty = max(1, (int) ($rewards['get_quantity'] ?? 1));
        $getType = (string) ($rewards['get_type'] ?? 'cheapest');
        $totalQty = (int) $items->sum('quantity');

        if ($totalQty < $buyQty) {
            return ['applied' => false];
        }

        $setSize = $buyQty + $getQty;
        $triggers = (int) floor($totalQty / $setSize);
        if ($triggers < 1) {
            return ['applied' => false];
        }
        $freeQty = $triggers * $getQty;
        $discount = 0.0;
        $freeItems = [];

        if ($getType === 'cheapest' || $getType === 'same') {
            $sorted = $getType === 'same'
                ? $items->sortByDesc(fn ($i) => (int) $i->quantity)
                : $items->sortBy(fn ($i) => (float) $i->unit_price);

            $remaining = $freeQty;
            foreach ($sorted as $item) {
                if ($remaining <= 0) {
                    break;
                }
                $freeFromItem = min((int) $item->quantity, $remaining);
                $value = (float) $item->unit_price * $freeFromItem;
                $discount += $value;
                $freeItems[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? ('Product #'.$item->product_id),
                    'quantity' => $freeFromItem,
                    'value' => round($value, 2),
                ];
                $remaining -= $freeFromItem;
            }
        }

        return [
            'applied' => $discount > 0,
            'discount' => round($discount, 2),
            'free_items' => $freeItems,
            'message' => "Buy {$buyQty} Get {$getQty} Free applied! You save ".currency_format($discount),
            'type' => 'buy_x_get_y',
        ];
    }

    private function applyQuantityDiscount(Promotion $promotion, Collection $items): array
    {
        $tiers = $promotion->conditions['tiers'] ?? [];
        if (! is_array($tiers) || $tiers === []) {
            return ['applied' => false];
        }

        $totalQty = (int) $items->sum('quantity');
        $totalValue = (float) $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);
        $applicableTier = null;

        usort($tiers, fn ($a, $b) => (int) ($a['quantity'] ?? 0) <=> (int) ($b['quantity'] ?? 0));
        foreach (array_reverse($tiers) as $tier) {
            if ($totalQty >= (int) ($tier['quantity'] ?? 0)) {
                $applicableTier = $tier;
                break;
            }
        }
        if (! $applicableTier) {
            return ['applied' => false];
        }

        $discount = (($applicableTier['type'] ?? 'percent') === 'percent')
            ? $totalValue * ((float) ($applicableTier['value'] ?? 0) / 100)
            : $totalQty * (float) ($applicableTier['value'] ?? 0);
        $discount = min(round($discount, 2), $totalValue);

        return [
            'applied' => $discount > 0,
            'discount' => $discount,
            'free_items' => [],
            'message' => (($applicableTier['label'] ?? 'Quantity discount').' applied! Save '.currency_format($discount)),
            'type' => 'quantity_discount',
        ];
    }

    private function applyBundle(Promotion $promotion, Collection $items): array
    {
        $requiredProducts = $promotion->conditions['required_products'] ?? [];
        if (! is_array($requiredProducts) || $requiredProducts === []) {
            return ['applied' => false];
        }

        foreach ($requiredProducts as $req) {
            $found = $items->first(fn ($i) => (int) $i->product_id === (int) ($req['product_id'] ?? 0) && (int) $i->quantity >= (int) ($req['quantity'] ?? 1));
            if (! $found) {
                return ['applied' => false];
            }
        }

        $bundleTotal = (float) $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);
        $rewards = $promotion->rewards ?? [];
        $discount = (($rewards['discount_type'] ?? 'percent') === 'percent')
            ? ($bundleTotal * ((float) ($rewards['discount_value'] ?? 0) / 100))
            : (float) ($rewards['discount_value'] ?? 0);
        $discount = min(round($discount, 2), $bundleTotal);

        return ['applied' => $discount > 0, 'discount' => $discount, 'free_items' => [], 'message' => 'Bundle deal applied! Save '.currency_format($discount), 'type' => 'bundle'];
    }

    private function applyFreeShipping(Promotion $promotion, Collection $items, float $cartTotal): array
    {
        $minAmount = (float) ($promotion->conditions['min_amount'] ?? 0);
        if ($cartTotal < $minAmount) {
            return ['applied' => false];
        }

        return ['applied' => true, 'discount' => 0, 'free_items' => [], 'free_shipping' => true, 'message' => 'Free shipping applied!', 'type' => 'free_shipping'];
    }

    private function applyComboDiscount(Promotion $promotion, Collection $items): array
    {
        $comboProducts = $promotion->conditions['combo_products'] ?? [];
        $allInCart = collect($comboProducts)->every(fn ($pid) => $items->contains('product_id', (int) $pid));
        if (! $allInCart) {
            return ['applied' => false];
        }

        $rewards = $promotion->rewards ?? [];
        $discountProductId = $rewards['discount_product_id'] ?? null;
        $discountItem = $discountProductId
            ? $items->firstWhere('product_id', (int) $discountProductId)
            : $items->sortBy('unit_price')->first();
        if (! $discountItem) {
            return ['applied' => false];
        }

        $discount = (($rewards['discount_type'] ?? 'percent') === 'percent')
            ? ((float) $discountItem->unit_price * ((float) ($rewards['discount_value'] ?? 0) / 100))
            : (float) ($rewards['discount_value'] ?? 0);

        return ['applied' => $discount > 0, 'discount' => round($discount, 2), 'free_items' => [], 'message' => 'Combo deal applied! Save '.currency_format($discount), 'type' => 'combo_discount'];
    }

    private function getApplicableItems(Promotion $promotion, Collection $cartItems): Collection
    {
        $excluded = array_map('intval', $promotion->exclude_ids ?? []);
        $baseItems = $cartItems->filter(fn ($i) => ! in_array((int) $i->product_id, $excluded, true));

        if ($promotion->applies_to === 'all') {
            return $baseItems;
        }

        $ids = array_map('intval', $promotion->applies_to_ids ?? []);
        return match ($promotion->applies_to) {
            'products' => $baseItems->filter(fn ($i) => in_array((int) $i->product_id, $ids, true)),
            'categories' => $baseItems->filter(fn ($i) => $i->product?->category_id && in_array((int) $i->product->category_id, $ids, true)),
            'brands' => $baseItems->filter(fn ($i) => $i->product?->brand_id && in_array((int) $i->product->brand_id, $ids, true)),
            'sellers' => $baseItems->filter(fn ($i) => $i->product?->seller_id && in_array((int) $i->product->seller_id, $ids, true)),
            default => $baseItems,
        };
    }

    private function checkUsageLimit(Promotion $promotion, ?User $user): bool
    {
        if ($promotion->usage_limit && $promotion->used_count >= $promotion->usage_limit) {
            return false;
        }

        if ($user && $promotion->usage_per_user > 0) {
            $userUsage = PromotionUsage::query()->where(['promotion_id' => $promotion->id, 'user_id' => $user->id])->count();
            if ($userUsage >= $promotion->usage_per_user) {
                return false;
            }
        }

        return true;
    }

    private function buildSummary(): array
    {
        return [
            'count' => count($this->appliedPromotions),
            'discount' => round($this->totalDiscount, 2),
            'has_free_items' => ! empty($this->freeItems),
            'messages' => $this->messages,
        ];
    }
}
