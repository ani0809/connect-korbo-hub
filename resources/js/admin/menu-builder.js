window.menuBuilder = function menuBuilder() {
  return {
    saveOrder() {
      const items = Array.from(document.querySelectorAll('#menu-items li')).map((li, i) => ({ id: Number(li.dataset.id), parent_id: null, sort_order: i + 1 }));
      fetch('/admin/menus/reorder', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ items }),
      }).then((r) => r.json()).then((d) => alert(d.message || 'Saved'));
    },
  };
};
