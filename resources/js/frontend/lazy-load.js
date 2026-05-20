const lazyImages = document.querySelectorAll('img[data-src]');
if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const img = entry.target;
      img.src = img.dataset.src;
      if (img.dataset.srcset) img.srcset = img.dataset.srcset;
      img.classList.add('loaded');
      observer.unobserve(img);
    });
  }, { rootMargin: '50px 0px' });
  lazyImages.forEach((img) => observer.observe(img));
} else {
  lazyImages.forEach((img) => { img.src = img.dataset.src; });
}
