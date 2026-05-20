<div class="checkout-section-card" style="margin-top:12px">
    <label class="checkbox-label" style="display:flex;gap:8px;align-items:center">
        <input type="checkbox" name="use_points" value="1" x-model="usePoints">
        <span>Redeem reward points ({{ number_format($pointsData['balance'] ?? 0) }} pts)</span>
    </label>
    <div x-show="usePoints" style="margin-top:10px">
        <input
            type="number"
            name="points_to_redeem"
            class="form-input"
            min="{{ (int) setting('points_min_redeem', 100) }}"
            max="{{ (int) ($pointsData['max_points'] ?? 0) }}"
            value="{{ (int) setting('points_min_redeem', 100) }}"
        >
    </div>
</div>
