/**
 * Feature toggles: confirm before disabling critical features.
 */
const CRITICAL = ['multi_vendor', 'wishlist', 'reviews'];

document.addEventListener('change', (e) => {
  const input = e.target;
  if (!(input instanceof HTMLInputElement)) return;
  if (input.type !== 'checkbox' || !input.name?.startsWith('features[')) return;

  const name = input.name.match(/features\[(.+)\]/)?.[1];
  if (!name || !CRITICAL.includes(name)) return;
  if (input.checked) return;

  const ok = window.confirm(
    `Disable "${name}"? Related UI may be hidden until you turn this back on.`,
  );
  if (!ok) {
    input.checked = true;
  }
});
