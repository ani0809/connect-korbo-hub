/**
 * Admin coupon form: auto-generate code and uppercase code field.
 */
document.addEventListener('DOMContentLoaded', () => {
  const codeInput = document.querySelector('input[name="code"]');
  const btn = document.getElementById('btn-gen-code');
  if (codeInput) {
    codeInput.addEventListener('blur', () => {
      codeInput.value = String(codeInput.value || '').toUpperCase().replace(/[^A-Z0-9_-]/g, '');
    });
  }
  btn?.addEventListener('click', async () => {
    const form = btn.closest('form');
    const url = form?.dataset?.generateUrl || '/admin/coupons/generate-code';
    try {
      const r = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      const j = await r.json();
      if (j.code && codeInput) codeInput.value = j.code;
    } catch {
      /* ignore */
    }
  });
});
