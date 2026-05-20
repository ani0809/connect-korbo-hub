/**
 * Product import CSV: drag-drop zone + fetch result display.
 */
document.addEventListener('DOMContentLoaded', () => {
  const zone = document.getElementById('import-drop-zone');
  const input = document.getElementById('import-csv-input');
  const form = document.getElementById('product-import-form');
  const out = document.getElementById('import-result');

  if (!zone || !input || !form) return;

  zone.addEventListener('click', () => input.click());

  ['dragenter', 'dragover'].forEach((ev) => {
    zone.addEventListener(ev, (e) => {
      e.preventDefault();
      zone.classList.add('border-blue-500', 'bg-blue-50');
    });
  });
  ['dragleave', 'drop'].forEach((ev) => {
    zone.addEventListener(ev, (e) => {
      e.preventDefault();
      zone.classList.remove('border-blue-500', 'bg-blue-50');
    });
  });

  zone.addEventListener('drop', (e) => {
    const f = e.dataTransfer?.files?.[0];
    if (f && input) {
      const dt = new DataTransfer();
      dt.items.add(f);
      input.files = dt.files;
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!out) return;
    out.textContent = 'Importing…';
    const fd = new FormData(form);
    try {
      const r = await fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      });
      const j = await r.json();
      if (j.queued) {
        out.textContent = j.message || 'Queued.';
        return;
      }
      let msg = JSON.stringify(j, null, 2);
      if (j.errors && j.errors.length) {
        msg += '\n\nErrors:\n' + j.errors.slice(0, 30).join('\n');
      }
      out.textContent = msg;
    } catch {
      out.textContent = 'Import failed.';
    }
  });
});
