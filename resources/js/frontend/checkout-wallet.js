/**
 * Checkout wallet UI helpers (totals are finalized server-side on submit).
 * Syncs hidden max hints and validates wallet_amount against order total.
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('checkout-form');
  const useWallet = form?.querySelector('input[name="use_wallet"]');
  const walletInput = form?.querySelector('input[name="wallet_amount"]');
  if (!form || !useWallet || !walletInput) return;

  const syncMax = () => {
    const max = parseFloat(walletInput.getAttribute('max') || '0');
    let v = parseFloat(walletInput.value || '0');
    if (v > max) walletInput.value = max.toFixed(2);
    if (v < 0) walletInput.value = '0';
  };

  walletInput.addEventListener('change', syncMax);
  walletInput.addEventListener('blur', syncMax);
  useWallet.addEventListener('change', () => {
    if (!useWallet.checked) walletInput.value = '0';
  });
});
