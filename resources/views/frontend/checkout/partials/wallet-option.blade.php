<div class="checkout-section-card" style="margin-top:12px">
    <label class="checkbox-label" style="display:flex;gap:8px;align-items:center">
        <input type="checkbox" name="use_wallet" value="1" x-model="useWallet">
        <span>Use wallet balance ({{ currency_format($walletBalance ?? 0) }} available)</span>
    </label>
    <div x-show="useWallet" style="margin-top:10px">
        <input type="number" name="wallet_amount" step="0.01" min="0" max="{{ $walletBalance ?? 0 }}" class="form-input" placeholder="Amount to use">
    </div>
</div>
