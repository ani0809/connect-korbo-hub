window.CheckoutPage = function CheckoutPage() {
  return {
    step: 1,
    placing: false,
    paymentMethod: document.querySelector('[name="payment_method"]:checked')?.value || '',
    useWallet: false,
    usePoints: false,
    form: { first_name: '', email: '', phone: '' },
    async init() {
      document.querySelectorAll('[name="shipping_method_id"]').forEach((node) => {
        node.addEventListener('change', () => {
          this.refreshSummary();
          this.syncSelectionStates();
        });
      });
      document.querySelectorAll('[name="payment_method"]').forEach((node) => {
        node.addEventListener('change', () => this.syncSelectionStates());
      });
      const country = document.querySelector('[name="shipping_country"]');
      const city = document.querySelector('[name="shipping_city"]');
      let timer = null;
      [country, city].forEach((el) => {
        el?.addEventListener('input', () => {
          clearTimeout(timer);
          timer = setTimeout(() => this.refreshSummary(), 500);
        });
      });
      this.syncSelectionStates();
      await this.refreshSummary();
    },
    syncSelectionStates() {
      document.querySelectorAll('.shipping-method-item').forEach((item) => {
        const radio = item.querySelector('input[type="radio"]');
        item.classList.toggle('is-selected', !!radio?.checked);
      });
      document.querySelectorAll('.payment-method-card').forEach((item) => {
        const radio = item.querySelector('input[type="radio"]');
        item.classList.toggle('is-selected', !!radio?.checked);
      });
    },
    goToStep(newStep) {
      if (newStep > this.step && !this.validateStep(this.step)) return;
      this.step = newStep;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    validateStep(currentStep) {
      if (currentStep !== 1) return true;
      const required = ['shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_country'];
      for (const key of required) {
        const input = document.querySelector(`[name="${key}"]`);
        if (!input || !input.value.trim()) {
          input?.focus();
          window.toast?.error('Please fill all required fields');
          return false;
        }
      }
      return true;
    },
    async refreshSummary() {
      const selected = document.querySelector('[name="shipping_method_id"]:checked');
      if (!selected) return;
      try {
        const response = await fetch('/checkout/calculate-shipping', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
          body: JSON.stringify({ shipping_method_id: selected.value }),
        });
        const data = await response.json();
        const summary = data.summary || {};
        const fmt = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(n || 0));
        const set = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
        set('sum-subtotal', fmt(summary.subtotal));
        set('sum-shipping', fmt(summary.shipping));
        set('sum-tax', fmt(summary.tax));
        set('sum-discount', `-${fmt(summary.discount)}`);
        set('sum-total', fmt(summary.total));
      } catch (_) {
        // Keep graceful fallback; existing totals remain visible.
      }
    },
    placeOrder() {
      if (this.placing) return;
      if (!this.validateStep(1)) {
        this.step = 1;
        return;
      }
      const shipping = document.querySelector('[name="shipping_method_id"]:checked');
      const payment = document.querySelector('[name="payment_method"]:checked');
      if (!shipping || !payment) {
        window.toast?.error('Select shipping and payment method');
        return;
      }
      this.placing = true;
      document.getElementById('checkout-form')?.submit();
    },
  };
};
