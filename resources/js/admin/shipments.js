const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

document.getElementById('select-all')?.addEventListener('change', (event) => {
  const checked = Boolean(event.target.checked);
  document.querySelectorAll('.ship-check').forEach((node) => { node.checked = checked; });
});

document.getElementById('bulk-ship-btn')?.addEventListener('click', async () => {
  const ids = Array.from(document.querySelectorAll('.ship-check:checked')).map((el) => Number(el.value)).filter(Boolean).slice(0, 50);
  if (ids.length === 0) {
    window.toast?.error('Select at least one order');
    return;
  }
  const form = document.getElementById('bulk-ship-form');
  const courier = form?.querySelector('[name="courier"]')?.value || 'steadfast';
  const weight = Number(form?.querySelector('[name="weight"]')?.value || 0.5);
  const button = document.getElementById('bulk-ship-btn');
  if (button) button.disabled = true;
  try {
    const response = await fetch('/admin/shipments/bulk-create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
      body: JSON.stringify({ order_ids: ids, courier, weight }),
    });
    const data = await response.json();
    if (data.success) {
      window.toast?.success(`Created: ${data.created}, Failed: ${data.failed}`);
      setTimeout(() => window.location.reload(), 1200);
    } else {
      window.toast?.error(data.message || 'Bulk ship failed');
    }
  } catch (error) {
    window.toast?.error('Bulk ship request failed');
  } finally {
    if (button) button.disabled = false;
  }
});

document.querySelectorAll('.btn-track').forEach((btn) => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    if (!id) return;
    const modal = document.getElementById('tracking-modal');
    const modalTracking = document.getElementById('modal-tracking');
    const modalEvents = document.getElementById('modal-events');
    try {
      const response = await fetch(`/admin/shipments/${id}/track`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf || '' } });
      const data = await response.json();
      if (!data.success) {
        window.toast?.error(data.message || 'Tracking failed');
        return;
      }
      if (modal && modalTracking && modalEvents) {
        modalTracking.textContent = data.tracking_code || '';
        modalEvents.innerHTML = (data.events || []).map((ev) => `
          <div class="border-l-2 border-blue-200 pl-3 py-2">
            <div class="font-medium">${ev.status || ''}</div>
            <div class="text-xs text-slate-500">${ev.time || ''}</div>
            <div class="text-sm">${ev.message || ''}</div>
          </div>
        `).join('');
        modal.style.display = 'flex';
      }
    } catch (error) {
      window.toast?.error('Unable to fetch tracking timeline');
    }
  });
});

document.getElementById('close-tracking-modal')?.addEventListener('click', () => {
  const modal = document.getElementById('tracking-modal');
  if (modal) modal.style.display = 'none';
});
