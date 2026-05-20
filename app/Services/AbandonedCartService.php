<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbandonedCartService
{
    public function findAbandoned(int $hoursAgo = 1): Collection
    {
        return Cart::query()->whereHas('items')->whereDoesntHave('user.orders', fn ($q) => $q->where('created_at', '>', now()->subHours($hoursAgo)))->where('updated_at', '<', now()->subHours($hoursAgo))->where('updated_at', '>', now()->subDays(7))->with(['user', 'items.product'])->whereHas('user', fn ($q) => $q->whereNotNull('email'))->get();
    }

    public function sendRecoveryEmails(): void
    {
        if (! setting('abandoned_cart_email_enabled', false)) return;
        $hours = (int) setting('abandoned_cart_hours', 1);
        $carts = $this->findAbandoned($hours);

        foreach ($carts as $cart) {
            $alreadySent = DB::table('abandoned_cart_emails')->where('cart_id', $cart->id)->where('sent_at', '>', now()->subDay())->exists();
            if ($alreadySent) continue;

            $token = encrypt(['cart_id' => $cart->id, 'user_id' => $cart->user_id, 'expires' => now()->addDays(7)->timestamp]);
            $recoveryUrl = url('/cart/recover/'.$token);

            app(NotificationService::class)->send('cart.abandoned', $cart->user, [
                'cart_items' => $cart->items->take(3)->map(fn ($i) => ['name' => $i->product->name, 'price' => currency_format((float) $i->unit_price), 'thumbnail' => $i->product->thumbnail_url])->toArray(),
                'recovery_url' => $recoveryUrl,
                'cart_total' => currency_format((float) $cart->items->sum(fn ($i) => $i->unit_price * $i->quantity)),
            ]);

            DB::table('abandoned_cart_emails')->insert(['cart_id' => $cart->id, 'user_id' => $cart->user_id, 'sent_at' => now()]);
        }
    }
}
