/**
 * Staff permission checkboxes: select all / deselect all + preset bundles.
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-staff-permissions-form]');
  if (!form) return;

  const allBoxes = () => [...form.querySelectorAll('input[type="checkbox"][name^="permissions"]')];

  document.querySelector('[data-perm-select-all]')?.addEventListener('click', (e) => {
    e.preventDefault();
    allBoxes().forEach((cb) => {
      cb.checked = true;
    });
  });

  document.querySelector('[data-perm-deselect-all]')?.addEventListener('click', (e) => {
    e.preventDefault();
    allBoxes().forEach((cb) => {
      cb.checked = false;
    });
  });

  const presets = {
    product_manager: [
      'view_dashboard',
      'view_products',
      'create_products',
      'edit_products',
      'publish_products',
      'view_categories',
      'manage_categories',
      'view_brands',
      'manage_brands',
    ],
    order_manager: [
      'view_dashboard',
      'view_orders',
      'update_order_status',
      'export_orders',
      'view_invoices',
    ],
    support_agent: [
      'view_dashboard',
      'view_customers',
      'view_orders',
      'view_tickets',
      'reply_tickets',
      'close_tickets',
    ],
    marketing_manager: [
      'view_dashboard',
      'manage_coupons',
      'manage_flash_deals',
      'send_newsletters',
      'manage_banners',
    ],
  };

  document.querySelectorAll('[data-perm-preset]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const key = btn.getAttribute('data-perm-preset');
      const list = presets[key];
      if (!list) return;
      allBoxes().forEach((cb) => {
        const name = cb.value;
        cb.checked = list.includes(name);
      });
    });
  });

  document.querySelector('[data-preset-full-no-settings]')?.addEventListener('click', (e) => {
    e.preventDefault();
    const no = new Set(['view_settings', 'manage_settings', 'manage_theme']);
    allBoxes().forEach((cb) => {
      cb.checked = !no.has(cb.value);
    });
  });
});
