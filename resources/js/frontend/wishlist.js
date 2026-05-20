class Wishlist {
  constructor() {
    this.bindToggleButtons();
    this.loadWishlistState();
  }

  bindToggleButtons() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.wishlist-btn');
      if (!btn) return;
      e.preventDefault();
      if (!window.isLoggedIn) {
        this.showLoginPrompt();
        return;
      }
      this.toggle(btn.dataset.productId, btn);
    });
  }

  async toggle(productId, btn) {
    btn.classList.add('loading');
    const response = await fetch('/api/wishlist/toggle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ product_id: productId }),
    });
    const data = await response.json();
    btn.classList.remove('loading');

    if (data.action === 'added') {
      btn.classList.add('active');
      btn.title = 'Remove from wishlist';
    } else {
      btn.classList.remove('active');
      btn.title = 'Add to wishlist';
    }

    document.querySelectorAll('.wishlist-count').forEach((el) => (el.textContent = data.count));
    window.cart?.showNotification(data.message, 'success');
  }

  loadWishlistState() {
    if (!window.isLoggedIn) return;
    fetch('/api/wishlist/ids')
      .then((r) => r.json())
      .then((ids) => {
        ids.forEach((id) => {
          document.querySelectorAll(`.wishlist-btn[data-product-id="${id}"]`).forEach((btn) => btn.classList.add('active'));
        });
      });
  }

  showLoginPrompt() {
    window.cart?.showNotification('Login to save items to wishlist.', 'info');
  }
}

new Wishlist();
