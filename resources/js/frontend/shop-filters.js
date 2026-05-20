class ShopFilters {
  constructor() {
    this.form = document.getElementById('shop-filter-form');
    this.productsWrap = document.getElementById('shop-products');
    this.sort = document.getElementById('shop-sort');
    this.bind();
  }

  bind() {
    if (!this.form) return;
    this.form.addEventListener('change', () => this.fetchProducts());
    this.form.addEventListener('submit', (e) => { e.preventDefault(); this.fetchProducts(); });
    document.getElementById('clear-filters')?.addEventListener('click', () => {
      this.form.reset();
      this.fetchProducts();
    });
    this.sort?.addEventListener('change', () => this.fetchProducts());
  }

  serialize() {
    const params = new URLSearchParams(new FormData(this.form));
    if (this.sort?.value) params.set('sort', this.sort.value);
    return params;
  }

  async fetchProducts() {
    const params = this.serialize();
    const url = `/shop?${params.toString()}`;
    history.pushState({}, '', url);

    this.productsWrap.innerHTML = '<div class="p-8 text-center text-gray-500">Loading products...</div>';

    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      this.productsWrap.innerHTML = data.html || '<div class="p-6">No data</div>';
    } catch {
      this.productsWrap.innerHTML = '<div class="p-8 text-center text-red-500">Could not load products.</div>';
    }
  }
}
new ShopFilters();
