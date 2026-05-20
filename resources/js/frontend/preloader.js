window.addEventListener('load', () => {
  const loader = document.querySelector('.preloader-overlay');
  if (!loader) return;
  loader.classList.add('hide');
  setTimeout(() => loader.remove(), 300);
});
