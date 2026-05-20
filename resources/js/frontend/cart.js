class Cart {
  constructor() {
    this.bindAddToCartButtons();
    this.bindQuantityControls();
    this.bindRemoveButtons();
    this.bindCouponForm();
    this.bindMiniCartToggle();
  }

  bindAddToCartButtons() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.add-to-cart-btn:not([disabled])');
      if (!btn) return;
      e.preventDefault();
      this.addToCart(btn);
    });
  }

  async addToCart(btn) {
    const productId = btn.dataset.productId;
    const variantId = btn.dataset.variantId || null;
    const qty = Number(document.querySelector(`[data-product="${productId}"] .qty-input`)?.value || 1);

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';
    btn.disabled = true;

    try {
      const response = await fetch('/api/cart/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
          Accept: 'application/json',
        },
        body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: qty }),
      });

      const data = await response.json();
      if (data.success) {
        this.updateCartCount(data.cart_count);
        this.showNotification(data.message, 'success');
        btn.innerHTML = '? Added!';
        btn.classList.add('btn-success');
        setTimeout(() => {
          btn.innerHTML = originalHtml;
          btn.classList.remove('btn-success');
          btn.disabled = false;
        }, 1500);
        this.refreshMiniCart();
      } else {
        this.showNotification(data.message || 'Failed.', 'error');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    } catch {
      this.showNotification('Something went wrong', 'error');
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    }
  }

  bindQuantityControls() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.qty-btn');
      if (!btn) return;
      const wrap = btn.closest('[data-item-id]');
      const input = wrap?.querySelector('.qty-input');
      if (!input) return;
      const delta = Number(btn.dataset.delta || 0);
      const min = Number(btn.dataset.min || input.dataset.min || 1);
      const max = Number(btn.dataset.max || input.dataset.max || 0);
      let next = Math.max(min, Number(input.value || min) + delta);
      if (max > 0) next = Math.min(max, next);
      if (next === Number(input.value || min)) return;
      input.value = String(next);
      this.updateQuantity(btn.dataset.itemId, next);
    });
  }

  bindRemoveButtons() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove-item-btn');
      if (!btn) return;
      const row = btn.closest('[data-item-id]');
      const amountText = row?.querySelector('.item-total')?.textContent || '$0';
      const amount = Number(amountText.replace(/[^\d.]/g, ''));
      if (amount > 50 && !confirm('Remove this high-value item?')) return;
      this.removeItem(btn.dataset.itemId);
    });
  }

  bindCouponForm() {
    document.getElementById('apply-coupon')?.addEventListener('click', () => {
      const code = document.getElementById('coupon-code')?.value?.trim();
      if (!code) return;
      this.applyCoupon(code);
    });
  }

  bindMiniCartToggle() {
    document.addEventListener('click', (e) => {
      if (e.target.closest('.mini-cart-close')) {
        e.target.closest('.mini-cart-content')?.classList.add('hidden');
      }
    });
  }

  async updateQuantity(itemId, quantity) {
    const response = await fetch('/api/cart/update', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ item_id: itemId, quantity }),
    });
    const data = await response.json();
    if (!data.success) return this.showNotification(data.message || 'Update failed', 'error');
    this.showNotification('Cart updated', 'info');
    location.reload();
  }

  async removeItem(itemId) {
    const response = await fetch('/api/cart/remove', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ item_id: itemId }),
    });
    const data = await response.json();
    if (data.success) {
      this.showNotification('Item removed', 'info');
      location.reload();
    }
  }

  async applyCoupon(code) {
    const response = await fetch('/api/cart/coupon', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ code }),
    });
    const data = await response.json();
    this.showNotification(data.message || 'Coupon update', data.success ? 'success' : 'error');
    if (data.success) location.reload();
  }

  async refreshMiniCart() {
    const response = await fetch('/api/cart/mini');
    const data = await response.json();
    document.querySelectorAll('.mini-cart-content').forEach((el) => (el.innerHTML = data.html));
    this.updateCartCount(data.count);
  }

  updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach((el) => {
      el.textContent = count;
      el.classList.toggle('hidden', Number(count) === 0);
    });
  }

  showNotification(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = 'position:fixed;top:16px;right:16px;z-index:120;background:#0ea5e9;color:#fff;padding:10px 14px;border-radius:8px;opacity:0;transition:.2s';
    if (type === 'success') toast.style.background = '#16a34a';
    if (type === 'error') toast.style.background = '#dc2626';
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => (toast.style.opacity = '1'));
    const timeout = type === 'error' ? 5000 : 3000;
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 250); }, timeout);
  }
}

window.cart = new Cart();
