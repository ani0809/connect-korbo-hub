const token = document.querySelector('meta[name="csrf-token"]')?.content;
window.trackRecentlyViewed = (productId) => {
  fetch('/recently-viewed/track', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ product_id: productId }) }).catch(() => null);
};
