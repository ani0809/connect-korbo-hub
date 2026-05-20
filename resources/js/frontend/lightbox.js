class Lightbox {
  constructor() { this.current = 0; this.images = []; this.overlay = null; this.keyHandler = (e) => this.handleKey(e); this.bindEvents(); }
  bindEvents() {
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-lightbox]');
      if (!trigger) return;
      e.preventDefault();
      const group = trigger.dataset.lightbox;
      const all = document.querySelectorAll(`[data-lightbox="${group}"]`);
      this.images = Array.from(all).map((el) => ({ src: el.href || el.dataset.src || el.src, alt: el.dataset.alt || el.alt || '' }));
      this.current = Array.from(all).indexOf(trigger);
      this.open();
    });
  }
  open() {
    this.overlay = document.createElement('div');
    this.overlay.className = 'lightbox-overlay';
    this.overlay.innerHTML = '<button class="lb-close">x</button><button class="lb-prev">‹</button><div class="lb-content"><img src="" alt="" class="lb-img"><div class="lb-caption"></div><div class="lb-counter"></div></div><button class="lb-next">›</button>';
    document.body.appendChild(this.overlay);
    document.body.style.overflow = 'hidden';
    this.update();
    this.overlay.querySelector('.lb-close').addEventListener('click', () => this.close());
    this.overlay.querySelector('.lb-prev').addEventListener('click', () => this.prev());
    this.overlay.querySelector('.lb-next').addEventListener('click', () => this.next());
    this.overlay.addEventListener('click', (e) => { if (e.target === this.overlay) this.close(); });
    document.addEventListener('keydown', this.keyHandler);
  }
  update() {
    const img = this.overlay.querySelector('.lb-img');
    const current = this.images[this.current];
    img.src = current.src;
    this.overlay.querySelector('.lb-caption').textContent = current.alt;
    this.overlay.querySelector('.lb-counter').textContent = `${this.current + 1} / ${this.images.length}`;
  }
  prev() { if (this.current > 0) { this.current--; this.update(); } }
  next() { if (this.current < this.images.length - 1) { this.current++; this.update(); } }
  close() { this.overlay?.remove(); document.body.style.overflow = ''; document.removeEventListener('keydown', this.keyHandler); }
  handleKey(e) { if (e.key === 'Escape') this.close(); if (e.key === 'ArrowLeft') this.prev(); if (e.key === 'ArrowRight') this.next(); }
}
new Lightbox();
