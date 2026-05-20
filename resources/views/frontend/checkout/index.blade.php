@extends('frontend.layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="checkout-page" x-data="CheckoutPage()">
    <div class="checkout-progress-bar">
        <div class="container">
            <div class="checkout-steps">
                <div class="checkout-step" :class="{ active: step >= 1, done: step > 1 }"><div class="step-num"><span x-show="step <= 1">1</span><span x-show="step > 1">✓</span></div><span class="step-label">Shipping</span></div>
                <div class="step-connector" :class="{ done: step > 1 }"></div>
                <div class="checkout-step" :class="{ active: step >= 2, done: step > 2 }"><div class="step-num"><span x-show="step <= 2">2</span><span x-show="step > 2">✓</span></div><span class="step-label">Payment</span></div>
                <div class="step-connector" :class="{ done: step > 2 }"></div>
                <div class="checkout-step" :class="{ active: step >= 3 }"><div class="step-num">3</div><span class="step-label">Review</span></div>
            </div>
        </div>
    </div>

    <div class="container">
        <form id="checkout-form" class="checkout-layout" method="POST" action="{{ route('checkout.place') }}">
            @csrf
            <div class="checkout-form-col">
                <div x-show="step === 1" x-transition>
                    <div class="checkout-section-card card">
                        <h2 class="checkout-section-title">Shipping Contact</h2>
                        @guest
                            <div class="form-row-2">
                                <div class="form-group"><label class="form-label">First Name *</label><input name="guest_name" x-model="form.first_name" class="form-input"></div>
                                <div class="form-group"><label class="form-label">Email *</label><input name="guest_email" x-model="form.email" class="form-input"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Phone *</label><input name="guest_phone" x-model="form.phone" class="form-input" required></div>
                            <div class="form-group">
                                <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="create_account" value="1"> Create an account with this order</label>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password (if creating account)</label>
                                <input type="password" name="password" class="form-input" placeholder="Minimum 6 characters">
                            </div>
                        @endguest
                    </div>
                    <div class="checkout-section-card card" style="margin-top:16px">
                        <h2 class="checkout-section-title">Shipping Address</h2>
                        <div class="form-row-2">
                            <div class="form-group"><label class="form-label">Name *</label><input name="shipping_name" class="form-input" value="{{ $defaultShipping->name ?? '' }}" required></div>
                            <div class="form-group"><label class="form-label">Phone *</label><input name="shipping_phone" class="form-input" value="{{ $defaultShipping->phone ?? '' }}" required></div>
                        </div>
                        <div class="form-group"><label class="form-label">Email</label><input name="shipping_email" class="form-input" value="{{ $defaultShipping->email ?? '' }}"></div>
                        <div class="form-group"><label class="form-label">Address *</label><input name="shipping_address" class="form-input" value="{{ $defaultShipping->address_line1 ?? '' }}" required></div>
                        <div class="form-row-2">
                            <div class="form-group"><label class="form-label">City *</label><input name="shipping_city" class="form-input" value="{{ $defaultShipping->city ?? '' }}" required></div>
                            <div class="form-group"><label class="form-label">Postal code</label><input name="shipping_postal_code" class="form-input" value="{{ $defaultShipping->postal_code ?? '' }}"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Country *</label><input name="shipping_country" class="form-input" value="{{ $defaultShipping->country ?? setting('default_country', 'BD') }}" required></div>
                        <div class="form-group"><label class="form-label">Notes</label><textarea name="order_notes" class="form-textarea" rows="3"></textarea></div>
                        <button type="button" class="btn-checkout-continue" @click="goToStep(2)">Continue to Payment →</button>
                    </div>
                </div>

                <div x-show="step === 2" x-transition>
                    <div class="checkout-section-card card">
                        <div class="step-back"><button type="button" class="btn-back-step" @click="step = 1">← Back</button><h2 class="checkout-section-title" style="margin:0">Payment Method</h2></div>
                        <div class="shipping-methods" id="shipping-methods">
                            @foreach($shippingMethods as $m)
                                <label class="shipping-method-item">
                                    <input type="radio" name="shipping_method_id" value="{{ $m->id }}" data-cost="{{ (float) $m->cost }}" @if($loop->first) checked @endif>
                                    <div class="shipping-method-info"><div class="method-name">{{ $m->name }}</div><div class="method-eta">{{ $m->estimated_days ?? 'Estimated delivery in 2-5 days' }}</div></div>
                                    <div class="method-price">{{ currency_format((float) $m->cost) }}</div>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" class="btn-checkout-continue" @click="goToStep(3)">Continue to Review →</button>
                    </div>
                </div>

                <div x-show="step === 3" x-transition>
                    <div class="checkout-section-card card">
                        <div class="step-back"><button type="button" class="btn-back-step" @click="step = 2">← Back</button><h2 class="checkout-section-title" style="margin:0">Review & Confirm</h2></div>
                        <div class="payment-methods-grid">
                            @foreach($paymentMethods as $pm)
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="{{ $pm->slug }}" x-model="paymentMethod" @if($loop->first) checked @endif>
                                    <div class="pm-icon">💳</div><div class="pm-info"><div class="pm-name">{{ $pm->name }}</div><div class="pm-desc">{{ ucfirst($pm->slug) }} payment</div></div>
                                </label>
                            @endforeach
                        </div>
                        @auth
                            @if(function_exists('feature') && feature('wallet'))
                                @include('frontend.checkout.partials.wallet-option')
                            @endif
                            @if(function_exists('feature') && feature('club_points'))
                                @include('frontend.checkout.partials.points-option')
                            @endif
                        @endauth
                        <button type="button" id="place-order" class="btn-place-order" @click="placeOrder()"><span x-show="!placing">🔒 Place Order</span><span x-show="placing">⏳ Processing...</span></button>
                    </div>
                </div>
            </div>

            <div class="checkout-summary-col">
                <div class="order-summary-sticky">
                    <div class="summary-card">
                        <h3 class="summary-heading">Order Summary</h3>
                        <div class="summary-items">
                            @foreach($items as $item)
                                <div class="summary-item">
                                    <div class="summary-item-image"><img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product_name }}"><span class="item-qty-badge">{{ $item->quantity }}</span></div>
                                    <div class="summary-item-info"><div class="summary-item-name">{{ $item->product_name }}</div></div>
                                    <div class="summary-item-price">{{ currency_format($item->subtotal) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="summary-totals">
                            <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">{{ currency_format($summary['subtotal']) }}</span></div>
                            <div class="summary-row"><span>Shipping</span><span id="sum-shipping">{{ currency_format($summary['shipping']) }}</span></div>
                            <div class="summary-row"><span>Tax</span><span id="sum-tax">{{ currency_format($summary['tax']) }}</span></div>
                            <div class="summary-row"><span>Discount</span><span id="sum-discount">-{{ currency_format($summary['discount']) }}</span></div>
                            <div class="summary-row summary-total-row"><span>Total</span><span class="total-amount" id="sum-total">{{ currency_format($summary['total']) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
window.csrfToken = '{{ csrf_token() }}';
window.stripeKey = '{{ setting('stripe_publishable_key', '') }}';
window.checkoutSummary = @json($summary);
</script>
@vite('resources/js/frontend/checkout.js')
@endsection
