window.MegaMenu = function MegaMenu() {
  return {
    active: null,
    activeCat: null,
    timer: null,
    open(id) { clearTimeout(this.timer); this.active = id; },
    closeAll() { this.timer = setTimeout(() => { this.active = null; this.activeCat = null; }, 150); },
  };
};

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('[x-data]').forEach(() => {});
  }
});
