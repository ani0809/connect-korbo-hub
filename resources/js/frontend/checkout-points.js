/**
 * Points redemption slider: clamp value to min/max from Alpine or attributes.
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('checkout-form');
  const range = form?.querySelector('input[name="points_to_redeem"][type="range"]');
  if (!range) return;

  const clamp = () => {
    const min = parseInt(range.min || '0', 10);
    const max = parseInt(range.max || '0', 10);
    let v = parseInt(range.value || '0', 10);
    if (v < min) range.value = String(min);
    if (v > max) range.value = String(max);
  };

  range.addEventListener('input', clamp);
  range.addEventListener('change', clamp);
});
