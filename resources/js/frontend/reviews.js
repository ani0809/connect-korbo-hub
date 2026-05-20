/**
 * Reviews / Q&A: rating bar animation, star rating labels.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.bar-fill').forEach((el) => {
    const w = el.getAttribute('data-width');
    if (w != null && w !== '') {
      requestAnimationFrame(() => {
        el.style.width = `${w}%`;
        el.classList.add('is-ready');
      });
    }
  });
});

const STAR_LABELS = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

export function starLabelFor(n) {
  return STAR_LABELS[n] || '';
}

window.starLabelFor = starLabelFor;
