function formatCurrency(amount, code = 'USD') {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: code }).format(Number(amount || 0));
}

window.ProductPage = function ProductPage(config) {
  return {
    ...config,
    qty: Number(config.soldIndividually ? 1 : (config.minPurchaseQty || 1)),
    minQty: Number(config.soldIndividually ? 1 : (config.minPurchaseQty || 1)),
    maxQty: 99,
    addingToCart: false,
    inStock: Boolean(config.inStock),
    selectedVariant: null,
    selectedAttributes: {},
    showStickyBar: false,
    stockText: Boolean(config.inStock) ? '✓ In Stock' : '✗ Out of Stock',
    currentPrice: formatCurrency(config.basePrice, config.currency),
    originalPrice: formatCurrency(config.baseOriginalPrice, config.currency),
    init() {
      window.addEventListener('scroll', () => {
        const info = document.getElementById('product-info-sticky');
        if (!info) return;
        const rect = info.getBoundingClientRect();
        this.showStickyBar = rect.bottom < 0;
      });
      const pid = this.productId;
      if (pid) {
        fetch('/recently-viewed/track', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
          body: JSON.stringify({ product_id: pid }),
        }).catch(() => null);
      }
      if (window.location.hash === '#reviews' || window.location.hash === '#qna') {
        setTimeout(() => document.querySelector('.product-tabs-section')?.scrollIntoView({ behavior: 'smooth' }), 200);
      }
      this.maxQty = this.soldIndividually ? 1 : Math.max(this.minQty, Number(this.maxPurchaseQtyLimit || 99));
      this.syncSelectionState();
      this.updateStockText();
    },
    matchingVariants(attrs = {}) {
      return (this.variants || []).filter((variant) => {
        const variantAttrs = variant.attributes || {};
        return Object.entries(attrs).every(([key, value]) => String(variantAttrs[key]) === String(value));
      });
    },
    isAttributeSelected(attributeId, valueId) {
      return String(this.selectedAttributes?.[attributeId] || '') === String(valueId);
    },
    selectedAttributeLabel(attributeId) {
      const selectedId = this.selectedAttributes?.[attributeId];
      if (!selectedId) return '';
      const attribute = (this.variationAttributes || []).find((item) => Number(item.id) === Number(attributeId));
      const value = attribute?.values?.find((item) => Number(item.id) === Number(selectedId));
      return value ? `: ${value.name}` : '';
    },
    isAttributeValueAvailable(attributeId, valueId) {
      const nextSelections = { ...this.selectedAttributes, [attributeId]: valueId };
      return this.matchingVariants(nextSelections).some((variant) => Boolean(variant.is_available));
    },
    selectAttributeValue(attributeId, valueId) {
      if (!this.isAttributeValueAvailable(attributeId, valueId)) return;
      if (this.isAttributeSelected(attributeId, valueId)) {
        delete this.selectedAttributes[attributeId];
      } else {
        this.selectedAttributes[attributeId] = valueId;
      }
      this.syncSelectionState();
    },
    syncSelectionState() {
      const requiredCount = Array.isArray(this.variationAttributes) ? this.variationAttributes.length : 0;
      const selectedCount = Object.keys(this.selectedAttributes || {}).length;
      const matches = this.matchingVariants(this.selectedAttributes);
      const variant = selectedCount === requiredCount
        ? matches.find((item) => Boolean(item.is_available)) || matches[0] || null
        : null;

      if (!variant && requiredCount > 0) {
        this.selectedVariant = null;
        this.inStock = matches.some((item) => Boolean(item.is_available));
        this.currentPrice = formatCurrency(config.basePrice, this.currency);
        this.originalPrice = config.baseOriginalPrice > config.basePrice
          ? formatCurrency(config.baseOriginalPrice, this.currency)
          : '';
        this.maxQty = this.soldIndividually ? 1 : Math.max(this.minQty, Number(this.maxPurchaseQtyLimit || 99));
        this.updateStockText();
        return;
      }

      if (variant) {
        this.selectVariantByAttributes(variant);
        return;
      }

      if (this.productType !== 'variable' && Array.isArray(this.variants) && this.variants[0]) {
        this.selectVariantByAttributes(this.variants[0]);
      }
    },
    selectVariantByAttributes(variant) {
      if (!variant) return;
      this.selectedVariant = variant;
      this.inStock = Boolean(variant.is_available);
      const stockLimit = this.allowsBackorders ? Number(this.maxPurchaseQtyLimit || 99) : Number(variant.stock || 0);
      const configuredMax = Number(this.maxPurchaseQtyLimit || 0);
      const computedMax = configuredMax > 0 ? Math.min(stockLimit || configuredMax, configuredMax) : (stockLimit || 99);
      this.maxQty = this.soldIndividually ? 1 : Math.max(this.minQty, computedMax || this.minQty);
      this.qty = Math.min(Math.max(this.qty, this.minQty), this.maxQty);
      this.currentPrice = formatCurrency(variant.sale_price || variant.price, this.currency);
      this.originalPrice = variant.sale_price ? formatCurrency(variant.price, this.currency) : '';
      this.updateStockText();
      if (variant.image) window.dispatchEvent(new CustomEvent('variant-image-change', { detail: { image: variant.image } }));
    },
    updateStockText() {
      const stock = Number(this.selectedVariant?.stock ?? this.baseStock ?? 0);
      if (this.manageStock) {
        if (stock > 0) {
          this.stockText = `✓ ${stock} in stock`;
          return;
        }
        if (this.allowsBackorders) {
          this.stockText = this.backorderMode === 'notify' ? '✓ Available on backorder (notify customer)' : '✓ Available on backorder';
          return;
        }
      }
      this.stockText = this.inStock ? '✓ In Stock' : '✗ Out of Stock';
    },
    async addToCart() {
      if (this.addingToCart) return;
      if (this.productType === 'variable' && !this.selectedVariant) {
        window.toast?.error('Please choose product options');
        return false;
      }
      this.addingToCart = true;
      try {
        const response = await fetch('/api/cart/add', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
          body: JSON.stringify({ product_id: this.productId, variant_id: this.selectedVariant?.id || null, quantity: this.qty }),
        });
        const data = await response.json();
        if (data.success) {
          window.toast?.success(data.message || 'Added to cart');
          window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.data }));
          return true;
        } else {
          window.toast?.error(data.message || 'Failed to add to cart');
          return false;
        }
      } catch (_) {
        window.toast?.error('Network error');
        return false;
      } finally {
        this.addingToCart = false;
      }
    },
    buyNow() {
      this.addToCart().then((success) => {
        if (success) {
          window.location.href = '/checkout';
        }
      });
    },
  };
};

window.ProductGallery = function ProductGallery(images = []) {
  return {
    images: images.length ? images : ['/images/placeholder.png'],
    currentIndex: 0,
    zoomActive: false,
    zoomLensStyle: '',
    get currentImage() {
      return this.images[this.currentIndex] || '/images/placeholder.png';
    },
    init() {
      window.addEventListener('variant-image-change', (e) => {
        const image = e.detail?.image;
        if (!image) return;
        this.images = [image, ...this.images.filter((i) => i !== image)];
        this.currentIndex = 0;
      });
    },
    setImage(index) { this.currentIndex = index; },
    nextImage() { this.currentIndex = this.currentIndex < this.images.length - 1 ? this.currentIndex + 1 : 0; },
    prevImage() { this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.images.length - 1; },
    handleZoom(event) {
      if (window.matchMedia('(max-width: 768px)').matches) return;
      this.zoomActive = true;
      const rect = event.currentTarget.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width) * 100;
      const y = ((event.clientY - rect.top) / rect.height) * 100;
      this.zoomLensStyle = `left:${x - 10}%;top:${y - 10}%`;
      const result = this.$refs.zoomResult;
      if (result) {
        result.style.backgroundImage = `url(${this.currentImage})`;
        result.style.backgroundPosition = `${x}% ${y}%`;
        result.style.backgroundSize = '250%';
      }
    },
  };
};
