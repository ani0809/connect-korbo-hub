class ProductVariations {
  constructor(variationData, config) {
    this.data = variationData || {};
    this.config = config || { attributes: {} };
    this.selected = {};
    this.init();
  }

  init() {
    this.bindSwatchClicks();
    this.bindDropdownChanges();
    this.checkInitialState();
  }

  bindSwatchClicks() {
    document.querySelectorAll('.variation-swatch').forEach((swatch) => {
      swatch.addEventListener('click', (e) => {
        const target = e.currentTarget;
        this.selectAttribute(target.dataset.attributeId, target.dataset.valueId);
      });
    });
  }

  bindDropdownChanges() {
    document.querySelectorAll('.variation-select').forEach((select) => {
      select.addEventListener('change', (e) => this.selectAttribute(e.target.dataset.attributeId, e.target.value));
    });
  }

  checkInitialState() {
    document.querySelectorAll('.variation-select').forEach((select) => {
      if (select.value) this.selected[select.dataset.attributeId] = select.value;
    });
    if (this.isComplete()) this.findMatchingVariant();
  }

  selectAttribute(attributeId, valueId) {
    this.selected[attributeId] = valueId;
    this.updateSwatchUI(attributeId, valueId);
    if (this.isComplete()) this.findMatchingVariant(); else this.updateAvailability();
  }

  updateSwatchUI(attributeId, valueId) {
    document.querySelectorAll(`.variation-swatch[data-attribute-id="${attributeId}"]`).forEach((el) => el.classList.toggle('active', el.dataset.valueId === valueId));
    const select = document.querySelector(`.variation-select[data-attribute-id="${attributeId}"]`);
    if (select) select.value = valueId;
  }

  isComplete() {
    const required = Object.keys(this.config.attributes || {});
    return required.every((id) => this.selected[id]);
  }

  findMatchingVariant() {
    const combo = Object.values(this.selected).sort().join('-');
    const variant = this.data[combo];
    if (variant) this.updateProductInfo(variant); else this.showUnavailable();
  }

  updateProductInfo(variant) {
    const priceEl = document.querySelector('.product-price');
    if (priceEl) priceEl.innerHTML = this.formatPrice(variant);

    const stockEl = document.querySelector('.stock-status');
    if (stockEl) stockEl.textContent = variant.stock > 0 ? `In Stock (${variant.stock})` : 'Out of Stock';

    const atcBtn = document.querySelector('.add-to-cart-btn');
    if (atcBtn) {
      atcBtn.disabled = variant.stock === 0;
      atcBtn.dataset.variantId = variant.id;
      if (variant.stock === 0) atcBtn.textContent = 'Out of Stock';
    }

    if (variant.image) this.updateGallery(variant.image);

    const skuEl = document.querySelector('.product-sku');
    if (skuEl && variant.sku) skuEl.textContent = variant.sku;
  }

  updateAvailability() {
    // Basic availability fallback; can be expanded for strict matrix logic.
    document.querySelectorAll('.variation-swatch').forEach((el) => el.classList.remove('unavailable'));
  }

  updateGallery(imageUrl) {
    const mainImage = document.querySelector('.product-gallery .main-image img, .p-gallery .main img');
    if (mainImage) mainImage.src = imageUrl;
  }

  formatPrice(variant) {
    if (variant.sale_price) {
      return `<span class="sale-price">${window.formatCurrency ? window.formatCurrency(variant.sale_price) : variant.sale_price}</span> <span class="original-price">${window.formatCurrency ? window.formatCurrency(variant.price) : variant.price}</span>`;
    }
    return `<span class="regular-price">${window.formatCurrency ? window.formatCurrency(variant.price) : variant.price}</span>`;
  }

  showUnavailable() {
    const atcBtn = document.querySelector('.add-to-cart-btn');
    if (atcBtn) {
      atcBtn.disabled = true;
      atcBtn.textContent = 'Unavailable';
    }
  }
}

window.ProductVariations = ProductVariations;

/**
 * Alpine state for the product detail page (variants + reviews/Q&A toggles).
 */
window.ProductPage = function productPage(variationData, variantsMap) {
  return {
    variationData: variationData || {},
    variantsMap: variantsMap || [],
    reviewFormOpen: false,
    askFormOpen: false,
  };
};
