import Alpine from 'alpinejs';
import '../../css/admin/pos-terminal.css';

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

window.PosTerminal = function PosTerminal(cfg) {
    return {
        sessionId: cfg.sessionId,
        currency: cfg.currency,
        categories: cfg.categories || [],
        routes: cfg.routes,
        bkashNumber: cfg.bkashNumber || '',
        autoPrint: !!cfg.autoPrint,

        products: [],
        filteredProducts: [],
        loadingProducts: false,
        searchQuery: '',
        selectedCategory: null,

        cartItems: [],
        selectedCustomer: null,
        customerQuery: '',
        customerResults: [],
        discountValue: 0,
        discountType: 'fixed',

        showPaymentModal: false,
        showReceipt: false,
        showVariantModal: false,
        showCashInOut: false,
        showCloseSession: false,
        offline: false,

        paymentMethod: 'cash',
        cashTendered: 0,
        processing: false,

        variantProduct: null,

        cashInOutType: 'cash_in',
        cashInOutAmount: 0,
        cashInOutNote: '',

        receiptHtml: '',
        lastChange: 0,
        clock: '',

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            window.addEventListener('online', () => { this.offline = false; });
            window.addEventListener('offline', () => { this.offline = true; });
            this.offline = !navigator.onLine;

            this.loadProducts();

            this.$nextTick(() => {
                this.$refs.searchInput?.focus?.();
            });

            document.addEventListener('keydown', (e) => this.handleHotkeys(e));

        },

        updateClock() {
            this.clock = new Date().toLocaleTimeString('en-GB', { hour12: false });
        },

        async apiGet(url) {
            const r = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            return r.json();
        },

        async loadProducts(categoryId = null) {
            this.loadingProducts = true;
            try {
                const u = new URL(this.routes.products, window.location.origin);
                if (categoryId) u.searchParams.set('category_id', categoryId);
                const data = await this.apiGet(u.toString());
                this.products = data.products || [];
                this.filteredProducts = this.products;
            } finally {
                this.loadingProducts = false;
            }
        },

        async searchProducts() {
            this.loadingProducts = true;
            try {
                const u = new URL(this.routes.products, window.location.origin);
                if (this.searchQuery) u.searchParams.set('q', this.searchQuery);
                if (this.selectedCategory) u.searchParams.set('category_id', this.selectedCategory);
                const data = await this.apiGet(u.toString());
                this.filteredProducts = data.products || [];
            } finally {
                this.loadingProducts = false;
            }
        },

        selectCategory(catId) {
            this.selectedCategory = catId;
            this.searchQuery = '';
            this.loadProducts(catId);
        },

        addToCart(product) {
            if (product.stock <= 0) return;
            if (product.has_variants && product.variants?.length > 1) {
                this.variantProduct = product;
                this.showVariantModal = true;
                return;
            }
            const v = product.variants?.[0] || null;
            this.addItemToCart(product, v);
        },

        addVariantToCart(product, variant) {
            if (!variant || variant.stock <= 0) return;
            this.addItemToCart(product, variant);
            this.showVariantModal = false;
        },

        addItemToCart(product, variant) {
            const vid = variant?.id ?? null;
            const price = variant ? Number(variant.price) : Number(product.price);
            const stock = variant ? Number(variant.stock) : Number(product.stock);
            const key = `${product.id}-${vid ?? '0'}`;
            const variantName = variant?.attributes
                ? Object.entries(variant.attributes).map(([k, val]) => `${k}: ${val}`).join(', ')
                : '';

            const idx = this.cartItems.findIndex((i) => i.key === key);
            if (idx >= 0) {
                if (this.cartItems[idx].qty >= stock) return;
                this.cartItems[idx].qty += 1;
                this.cartItems = [...this.cartItems];
                return;
            }
            this.cartItems.push({
                key,
                product_id: product.id,
                variant_id: vid,
                name: product.name,
                variant_name: variantName,
                image: product.image,
                price,
                qty: 1,
                stock,
            });
        },

        updateQty(index, delta) {
            const item = this.cartItems[index];
            const newQty = item.qty + delta;
            if (newQty <= 0) {
                this.cartItems.splice(index, 1);
                this.cartItems = [...this.cartItems];
                return;
            }
            if (newQty > item.stock) return;
            this.cartItems[index].qty = newQty;
            this.cartItems = [...this.cartItems];
        },

        removeItem(index) {
            this.cartItems.splice(index, 1);
            this.cartItems = [...this.cartItems];
        },

        clearCart() {
            if (!confirm('Clear all items?')) return;
            this.cartItems = [];
            this.discountValue = 0;
            this.selectedCustomer = null;
            this.customerQuery = '';
        },

        get cartItemCount() {
            return this.cartItems.reduce((s, i) => s + i.qty, 0);
        },

        get subtotal() {
            return this.cartItems.reduce((s, i) => s + i.price * i.qty, 0);
        },

        get discountAmount() {
            const v = Number(this.discountValue) || 0;
            if (v <= 0) return 0;
            if (this.discountType === 'percent') {
                return Math.min(this.subtotal * (v / 100), this.subtotal);
            }
            return Math.min(v, this.subtotal);
        },

        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        get quickAmounts() {
            const t = this.total;
            const s = new Set();
            [50, 100, 500, 1000].forEach((x) => {
                const r = Math.ceil(t / x) * x;
                if (r >= t) s.add(r);
            });
            return [...s].sort((a, b) => a - b).slice(0, 4);
        },

        async searchCustomers() {
            if (this.customerQuery.length < 2) {
                this.customerResults = [];
                return;
            }
            const u = new URL(this.routes.customers, window.location.origin);
            u.searchParams.set('q', this.customerQuery);
            const data = await this.apiGet(u.toString());
            this.customerResults = data.customers || [];
        },

        selectCustomer(c) {
            this.selectedCustomer = c;
            this.customerResults = [];
            this.customerQuery = '';
        },

        async completeSale() {
            if (this.cartItems.length === 0 || this.offline) return;
            if (this.paymentMethod === 'cash' && Number(this.cashTendered) < this.total - 0.001) return;

            this.processing = true;
            try {
                const body = {
                    session_id: this.sessionId,
                    items: this.cartItems.map((i) => ({
                        product_id: i.product_id,
                        variant_id: i.variant_id,
                        name: i.name,
                        price: i.price,
                        quantity: i.qty,
                    })),
                    customer_id: this.selectedCustomer?.id ?? null,
                    discount: this.discountAmount,
                    payment_method: this.paymentMethod,
                    payment_details: {
                        cash_tendered: this.paymentMethod === 'cash' ? Number(this.cashTendered) : this.total,
                        change: this.paymentMethod === 'cash' ? Math.max(0, Number(this.cashTendered) - this.total) : 0,
                    },
                };

                const r = await fetch(this.routes.placeOrder, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                const data = await r.json();
                if (!data.success) {
                    alert(data.message || 'Checkout failed');
                    return;
                }
                this.lastChange = Number(data.change || 0);
                this.showPaymentModal = false;
                await this.buildReceipt(data.order_id);
                this.showReceipt = true;
                if (this.autoPrint) {
                    setTimeout(() => window.print(), 300);
                }
            } catch (e) {
                alert('Network error');
            } finally {
                this.processing = false;
            }
        },

        async buildReceipt(orderId) {
            const base = String(this.routes.receipt || '').replace(/\/$/, '');
            const data = await this.apiGet(`${base}/${orderId}`);
            const { order, store } = data;
            this.receiptHtml = `
        <div style="text-align:center;font-size:18px;font-weight:800">${store.name || ''}</div>
        <div style="text-align:center;color:#64748b;font-size:11px;margin:8px 0">${store.address || ''}<br>${store.phone || ''}${store.vat_number ? '<br>VAT: ' + store.vat_number : ''}</div>
        ${store.header_text ? '<div style="text-align:center;margin:8px 0">' + store.header_text + '</div>' : ''}
        <hr style="border:none;border-top:1px dashed #cbd5e1;margin:10px 0">
        <div style="font-size:11px;color:#64748b">Order: ${order.number}<br>Date: ${order.date}<br>Cashier: ${order.cashier}</div>
        <table width="100%" style="margin-top:10px;font-size:12px">${order.items.map((item) => `<tr><td style="padding:2px 0">${item.name}</td><td style="text-align:center">x${item.qty}</td><td style="text-align:right">${this.currency}${this.formatNum(item.subtotal)}</td></tr>`).join('')}</table>
        <hr style="border:none;border-top:1px dashed #cbd5e1;margin:10px 0">
        <div style="display:flex;justify-content:space-between;font-size:12px"><span>Subtotal</span><span>${this.currency}${this.formatNum(order.subtotal)}</span></div>
        ${order.discount > 0 ? `<div style="display:flex;justify-content:space-between;font-size:12px"><span>Discount</span><span>-${this.currency}${this.formatNum(order.discount)}</span></div>` : ''}
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:8px"><span>TOTAL</span><span>${this.currency}${this.formatNum(order.total)}</span></div>
        <div style="margin-top:8px;font-size:12px">Payment: ${String(order.payment_method).toUpperCase()}</div>
        ${order.payment_method === 'cash' ? `<div style="font-size:12px">Change: ${this.currency}${this.formatNum(order.change)}</div>` : ''}
        <div style="text-align:center;margin-top:12px;font-size:11px;color:#64748b">${store.footer_text || ''}</div>`;
        },

        startNewSale() {
            this.showReceipt = false;
            this.cartItems = [];
            this.selectedCustomer = null;
            this.discountValue = 0;
            this.paymentMethod = 'cash';
            this.cashTendered = 0;
            this.loadProducts(this.selectedCategory);
            this.$nextTick(() => this.$refs.searchInput?.focus?.());
        },

        async processCashInOut() {
            if (!this.cashInOutAmount || !this.cashInOutNote) return;
            const r = await fetch(this.routes.cashInOut, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    type: this.cashInOutType,
                    amount: this.cashInOutAmount,
                    note: this.cashInOutNote,
                }),
            });
            const data = await r.json();
            if (data.success) {
                this.showCashInOut = false;
                this.cashInOutAmount = 0;
                this.cashInOutNote = '';
            }
        },

        async closeSessionPrompt() {
            const closing = window.prompt('Enter counted closing cash:');
            if (closing === null) return;
            const notes = window.prompt('Session notes (optional):') || '';
            const r = await fetch(this.routes.closeSession, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify({
                    closing_balance: parseFloat(closing),
                    notes,
                }),
            });
            const data = await r.json();
            if (data.success && data.report_url) {
                window.location.href = data.report_url;
            }
        },

        handleHotkeys(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                this.$refs.searchInput?.focus?.();
            }
            if (e.key === 'F4') {
                e.preventDefault();
                this.clearCart();
            }
            if (e.key === 'F9' && this.cartItems.length > 0) {
                e.preventDefault();
                this.showPaymentModal = true;
            }
            if (e.key === 'Escape') {
                this.showPaymentModal = false;
                this.showVariantModal = false;
                this.showCashInOut = false;
            }
        },

        formatNum(n) {
            return (Number(n) || 0).toFixed(2);
        },

        onSearchEnter(e) {
            if (e.key === 'Enter' && this.filteredProducts.length === 1) {
                this.addToCart(this.filteredProducts[0]);
                this.searchQuery = '';
            }
        },

        sendSmsReceipt() {
            alert('SMS receipt is not configured.');
        },
    };
};

window.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
