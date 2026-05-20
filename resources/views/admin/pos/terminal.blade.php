<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — {{ setting('site_name', config('app.name')) }}</title>
    @vite(['resources/js/admin/pos-terminal.js'])
    <script>
        window.__POS_CONFIG = {
            sessionId: {{ (int) $session->id }},
            currency: @json($currency),
            categories: @json($categories),
            bkashNumber: @json((string) setting('pos_bkash_merchant_number', setting('bkash_merchant_number', ''))),
            autoPrint: @json((bool) setting('pos_auto_print_receipt', false)),
            routes: {
                products: @json(route('admin.pos.api.products')),
                customers: @json(route('admin.pos.api.customers')),
                placeOrder: @json(route('admin.pos.api.place-order')),
                cashInOut: @json(route('admin.pos.api.cash-in-out')),
                closeSession: @json(route('admin.pos.close', $session->id)),
                receipt: @json(url('/admin/pos/api/receipt')),
            },
        };
    </script>
</head>
<body class="pos-terminal-body" x-data="PosTerminal(window.__POS_CONFIG)" x-init="init()">
    <div class="pos-offline-banner" x-show="offline" x-cloak>No internet — orders cannot be completed.</div>

    <div class="pos-topbar">
        <div class="pos-logo">{{ setting('site_name') }} POS</div>
        <div class="pos-session-info">
            <span class="session-badge">{{ $session->terminal_id }}</span>
            <span>{{ auth()->user()->name }}</span>
        </div>
        <div class="pos-topbar-right">
            <div class="pos-clock" x-text="clock"></div>
            <button type="button" class="topbar-btn" @click="showCashInOut = true">Cash In/Out</button>
            <button type="button" class="topbar-btn danger" @click="closeSessionPrompt()">Close session</button>
        </div>
    </div>

    <div class="pos-body">
        <div class="pos-products-panel">
            <div class="pos-search-bar">
                <input type="text" class="pos-search-input" x-ref="searchInput" x-model="searchQuery"
                    @input.debounce.300ms="searchProducts()" @keydown="onSearchEnter($event)"
                    placeholder="Search name, SKU, barcode…" autocomplete="off">
                <button type="button" class="barcode-btn" @click="$refs.searchInput.focus()" title="Focus search for scanner">Scan</button>
            </div>

            <div class="pos-category-tabs">
                <button type="button" class="category-tab" :class="{ active: !selectedCategory }" @click="selectCategory(null)">All</button>
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button" class="category-tab" :class="{ active: selectedCategory == cat.id }"
                        @click="selectCategory(cat.id)" x-text="cat.name"></button>
                </template>
            </div>

            <div class="pos-products-grid">
                <template x-if="loadingProducts">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#64748b">Loading…</div>
                </template>
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="pos-product-card" :class="{ 'out-of-stock': product.stock <= 0 }" @click="addToCart(product)">
                        <div class="pos-product-img">
                            <template x-if="product.image">
                                <img :src="product.image" :alt="product.name" loading="lazy" @error="$el.closest('.pos-product-img').innerHTML='📦'">
                            </template>
                            <template x-if="!product.image"><span>📦</span></template>
                        </div>
                        <div class="pos-product-info">
                            <div class="pos-product-name" x-text="product.name"></div>
                            <div class="pos-product-price" x-text="currency + formatNum(product.price)"></div>
                        </div>
                        <div class="pos-product-stock" :class="{ low: product.stock > 0 && product.stock <= 5 }"
                            x-text="product.stock <= 0 ? 'OUT' : product.stock"></div>
                    </div>
                </template>
            </div>
        </div>

        <aside class="pos-cart-panel">
            <div class="cart-header">
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="cart-title">Cart</span>
                    <span class="cart-count" x-text="cartItemCount"></span>
                </div>
                <button type="button" class="clear-cart-btn" @click="clearCart()" x-show="cartItems.length > 0">Clear</button>
            </div>

            <div class="customer-section">
                <div style="position:relative" x-show="!selectedCustomer">
                    <input type="text" class="customer-input" x-model="customerQuery" @input.debounce.300ms="searchCustomers()"
                        placeholder="Customer (optional)" autocomplete="off">
                    <div class="customer-dropdown" x-show="customerResults.length > 0" x-cloak>
                        <template x-for="c in customerResults" :key="c.id">
                            <div class="customer-option" @click="selectCustomer(c)">
                                <div style="font-weight:600;color:#fff" x-text="c.name"></div>
                                <div style="font-size:11px;color:#64748b" x-text="c.phone || c.email || ''"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="selectedCustomer" class="selected-customer" x-cloak style="margin-top:8px">
                    <div>
                        <div style="font-weight:600;color:#5eead4" x-text="selectedCustomer?.name"></div>
                        <div style="font-size:11px;color:#64748b" x-text="selectedCustomer?.phone"></div>
                    </div>
                    <button type="button" class="clear-cart-btn" @click="selectedCustomer = null">×</button>
                </div>
            </div>

            <div class="cart-items">
                <div class="cart-empty" x-show="cartItems.length === 0">
                    <div style="font-size:48px;opacity:.3">🛒</div>
                    <div>Cart is empty</div>
                </div>
                <template x-for="(item, index) in cartItems" :key="item.key">
                    <div class="cart-item">
                        <img :src="item.image || ''" class="cart-item-img" alt="" @error="$el.style.display='none'">
                        <div class="cart-item-info">
                            <div class="cart-item-name" x-text="item.name"></div>
                            <div style="font-size:11px;color:#64748b" x-text="item.variant_name" x-show="item.variant_name"></div>
                            <div style="color:#0d9488;font-weight:600" x-text="currency + formatNum(item.price)"></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px">
                            <button type="button" class="qty-btn" @click="updateQty(index, -1)">−</button>
                            <span style="min-width:24px;text-align:center;font-weight:700" x-text="item.qty"></span>
                            <button type="button" class="qty-btn" @click="updateQty(index, 1)">+</button>
                        </div>
                        <div style="font-weight:700;min-width:64px;text-align:right" x-text="currency + formatNum(item.price * item.qty)"></div>
                        <button type="button" class="clear-cart-btn" @click="removeItem(index)">×</button>
                    </div>
                </template>
            </div>

            <div class="cart-footer">
                <div class="discount-row">
                    <span style="color:#64748b;font-size:13px">Discount</span>
                    <div style="display:flex;background:#0f172a;border:1px solid #334155;border-radius:6px;overflow:hidden">
                        <button type="button" style="padding:6px 10px;border:none;background:transparent;color:#94a3b8;cursor:pointer"
                            :style="discountType === 'fixed' ? 'background:#0d9488;color:#fff' : ''" @click="discountType = 'fixed'">{{ $currency }}</button>
                        <button type="button" style="padding:6px 10px;border:none;background:transparent;color:#94a3b8;cursor:pointer"
                            :style="discountType === 'percent' ? 'background:#0d9488;color:#fff' : ''" @click="discountType = 'percent'">%</button>
                    </div>
                    <input type="number" class="discount-input" x-model="discountValue" min="0" step="any" placeholder="0">
                </div>
                <div class="cart-totals">
                    <div class="totals-row"><span>Subtotal</span><span x-text="currency + formatNum(subtotal)"></span></div>
                    <div class="totals-row" x-show="discountAmount > 0" style="color:#0d9488"><span>Discount</span>
                        <span x-text="'-' + currency + formatNum(discountAmount)"></span></div>
                    <div class="totals-row total"><span>TOTAL</span><span x-text="currency + formatNum(total)"></span></div>
                </div>
                <button type="button" class="btn-payment" @click="showPaymentModal = true" :disabled="cartItems.length === 0 || offline">
                    Charge <span x-text="currency + formatNum(total)"></span>
                </button>
            </div>
        </aside>
    </div>

    {{-- Payment modal --}}
    <div class="modal-overlay" x-show="showPaymentModal" x-cloak x-transition @click.self="showPaymentModal = false">
        <div class="payment-modal" @click.stop>
            <div class="payment-modal-header">
                <span style="font-size:17px;font-weight:700">Payment</span>
                <button type="button" class="modal-close-btn" @click="showPaymentModal = false">×</button>
            </div>
            <div style="padding:20px">
                <div class="payment-total-display">
                    <div style="font-size:13px;color:#64748b;margin-bottom:6px">Amount due</div>
                    <div class="payment-total-amount" x-text="currency + formatNum(total)"></div>
                </div>
                <div class="payment-methods-grid">
                    <button type="button" class="payment-method-btn" :class="{ active: paymentMethod === 'cash' }" @click="paymentMethod = 'cash'">Cash</button>
                    <button type="button" class="payment-method-btn" :class="{ active: paymentMethod === 'card' }" @click="paymentMethod = 'card'">Card</button>
                    <button type="button" class="payment-method-btn" :class="{ active: paymentMethod === 'bkash' }" @click="paymentMethod = 'bkash'">bKash</button>
                    <button type="button" class="payment-method-btn" :class="{ active: paymentMethod === 'nagad' }" @click="paymentMethod = 'nagad'">Nagad</button>
                </div>
                <div x-show="paymentMethod === 'cash'" style="margin-bottom:16px">
                    <label class="cash-label" style="font-size:12px;color:#64748b;display:block;margin-bottom:6px">Cash tendered</label>
                    <input type="number" class="cash-input" x-model="cashTendered" min="0" step="any">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
                        <template x-for="amt in quickAmounts" :key="amt">
                            <button type="button" class="topbar-btn" @click="cashTendered = amt" x-text="currency + formatNum(amt)"></button>
                        </template>
                        <button type="button" class="topbar-btn" @click="cashTendered = total">Exact</button>
                    </div>
                    <div class="change-display" x-show="Number(cashTendered) >= total">
                        <div style="font-size:11px;color:#64748b">Change</div>
                        <div class="change-amount" x-text="currency + formatNum(Math.max(0, Number(cashTendered) - total))"></div>
                    </div>
                </div>
                <div x-show="paymentMethod === 'bkash' || paymentMethod === 'nagad'"
                    style="background:rgba(99,102,241,0.1);border:1px solid #6366f1;border-radius:8px;padding:12px;font-size:13px;margin-bottom:16px">
                    Collect <strong x-text="currency + formatNum(total)"></strong> via <span x-text="paymentMethod"></span>.
                    <template x-if="bkashNumber"><div style="margin-top:6px">Number: <strong x-text="bkashNumber"></strong></div></template>
                </div>
                <button type="button" class="btn-complete-sale" @click="completeSale()"
                    :disabled="processing || offline || (paymentMethod === 'cash' && Number(cashTendered) < total)">
                    Complete sale
                </button>
            </div>
        </div>
    </div>

    {{-- Variant modal --}}
    <div class="modal-overlay" x-show="showVariantModal" x-cloak x-transition @click.self="showVariantModal = false">
        <div class="variant-modal" @click.stop>
            <div class="payment-modal-header">
                <span style="font-weight:700" x-text="variantProduct?.name"></span>
                <button type="button" class="modal-close-btn" @click="showVariantModal = false">×</button>
            </div>
            <div class="variant-grid">
                <template x-for="v in variantProduct?.variants || []" :key="v.id">
                    <div class="variant-option" :class="{ 'out-of-stock': v.stock <= 0 }"
                        @click="addVariantToCart(variantProduct, v)">
                        <div style="font-weight:600;color:#fff" x-text="Object.values(v.attributes || {}).join(' / ') || v.sku"></div>
                        <div style="font-size:10px;color:#64748b;margin-top:4px" x-text="v.stock <= 0 ? 'Out' : v.stock + ' in stock'"></div>
                        <div style="color:#0d9488;font-weight:700;margin-top:4px" x-text="currency + formatNum(v.price)"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Cash in/out --}}
    <div class="modal-overlay" x-show="showCashInOut" x-cloak x-transition @click.self="showCashInOut = false">
        <div class="payment-modal" style="max-width:400px" @click.stop>
            <div class="payment-modal-header">
                <span style="font-weight:700">Cash in / out</span>
                <button type="button" class="modal-close-btn" @click="showCashInOut = false">×</button>
            </div>
            <div style="padding:20px">
                <div class="payment-methods-grid">
                    <button type="button" class="payment-method-btn" :class="{ active: cashInOutType === 'cash_in' }" @click="cashInOutType = 'cash_in'">Cash in</button>
                    <button type="button" class="payment-method-btn" :class="{ active: cashInOutType === 'cash_out' }" @click="cashInOutType = 'cash_out'">Cash out</button>
                </div>
                <label style="font-size:12px;color:#64748b;display:block;margin:12px 0 6px">Amount</label>
                <input type="number" class="cash-input" x-model="cashInOutAmount" min="0.01" step="any">
                <label style="font-size:12px;color:#64748b;display:block;margin:12px 0 6px">Note</label>
                <input type="text" style="width:100%;min-height:44px;padding:10px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff"
                    x-model="cashInOutNote" placeholder="Reason">
                <button type="button" class="btn-complete-sale" style="margin-top:16px" @click="processCashInOut()">Confirm</button>
            </div>
        </div>
    </div>

    {{-- Receipt --}}
    <div class="modal-overlay" x-show="showReceipt" x-cloak x-transition>
        <div class="receipt-modal">
            <div style="padding:16px" id="receipt-print-area" x-html="receiptHtml"></div>
            <div class="receipt-actions">
                <button type="button" class="receipt-btn print" onclick="window.print()">Print</button>
                <button type="button" class="receipt-btn" @click="sendSmsReceipt()">SMS</button>
                <button type="button" class="receipt-btn new-sale" @click="startNewSale()">New sale</button>
            </div>
        </div>
    </div>
</body>
</html>
