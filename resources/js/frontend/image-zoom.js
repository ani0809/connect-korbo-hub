document.addEventListener('mousemove', (e) => {
  const wrap = e.target.closest('[data-image-zoom]');
  if (!wrap) return;
  const img = wrap.querySelector('img');
  if (!img) return;
  const r = wrap.getBoundingClientRect();
  const x = ((e.clientX - r.left) / r.width) * 100;
  const y = ((e.clientY - r.top) / r.height) * 100;
  img.style.transformOrigin = `${x}% ${y}%`;
  img.style.transform = 'scale(1.8)';
});
document.addEventListener('mouseleave', (e) => {
  const wrap = e.target.closest?.('[data-image-zoom]');
  if (!wrap) return;
  const img = wrap.querySelector('img');
  if (img) img.style.transform = 'scale(1)';
}, true);
