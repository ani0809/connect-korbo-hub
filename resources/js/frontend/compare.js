class Compare {
  constructor() { this.bind(); this.renderFloatingBar(); }

  bind() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.compare-btn');
      if (!btn) return;
      e.preventDefault();
      this.toggle(btn.dataset.productId, btn);
    });

    document.getElementById('compare-clear')?.addEventListener('click', () => this.clear());
  }

  async toggle(productId, btn) {
    const response = await fetch('/api/compare/toggle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ product_id: productId }),
    });
    const data = await response.json();
    if (!data.success) return window.cart?.showNotification(data.message, 'error');
    btn.classList.toggle('active', data.action === 'added');
    window.cart?.showNotification(data.message, 'success');
    this.renderFloatingBar(data.count);
    if (location.pathname.includes('/compare') && data.action === 'removed') location.reload();
  }

  async clear() {
    const response = await fetch('/api/compare/clear', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
    });
    const data = await response.json();
    window.cart?.showNotification(data.message, 'info');
    location.reload();
  }

  renderFloatingBar(count = null) {
    const existing = document.getElementById('compare-floating-bar');
    if (existing) existing.remove();
    const inferredCount = count ?? document.querySelectorAll('.compare-btn.active').length;
    if (!inferredCount) return;
    const bar = document.createElement('div');
    bar.id = 'compare-floating-bar';
    bar.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#0f172a;color:#fff;padding:10px 16px;z-index:120;display:flex;justify-content:space-between;align-items:center';
    bar.innerHTML = `<span>${inferredCount} item(s) in compare</span><a href="/compare" style="color:#93c5fd">Open Compare</a>`;
    document.body.appendChild(bar);
  }
}

new Compare();
