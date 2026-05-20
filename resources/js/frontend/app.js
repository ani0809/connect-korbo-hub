import './utils/helpers.js';
import './utils/toast.js';
import './utils/format.js';

import './cart.js';
import './wishlist.js';
import './compare.js';
import './search.js';
import './variations.js';
import './reviews.js';
import './recently-viewed.js';
import './otp-input.js';
import './password-strength.js';

import './theme-applier.js';
import './ui.js';
import './sticky-header.js';
import './mobile-menu.js';
import './lazy-load.js';
import './scroll-to-top.js';
import './countdown.js';
import './image-zoom.js';
import './lightbox.js';
import './push-init.js';
import '../shared/media-picker.js';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Global micro-interactions: button loading + card hover polish.
document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;
  const submit = form.querySelector('button[type="submit"], .btn-primary');
  if (!(submit instanceof HTMLButtonElement)) return;
  if (submit.dataset.loadingApplied === '1') return;
  submit.dataset.loadingApplied = '1';
  submit.dataset.originalText = submit.textContent || 'Processing';
  submit.disabled = true;
  submit.classList.add('is-loading');
  submit.textContent = 'Please wait...';
});

document.querySelectorAll('.card').forEach((el) => {
  el.classList.add('ui-hover-lift');
});
