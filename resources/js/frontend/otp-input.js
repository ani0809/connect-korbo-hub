window.OtpInputs = function OtpInputs(prefix = 'otp', combinedId = 'otp-combined') {
  return {
    digits: ['', '', '', '', '', ''],
    handleInput(event, index) {
      const value = String(event.target.value || '').replace(/\D/g, '').slice(-1);
      event.target.value = value;
      this.digits[index - 1] = value;
      this.syncCombined(combinedId);
      if (value && index < 6) {
        document.getElementById(`${prefix}-${index + 1}`)?.focus();
      }
      if (this.digits.every((d) => d !== '')) {
        this.$dispatch('otp-complete', { otp: this.digits.join('') });
      }
    },
    handleKeydown(event, index) {
      if (event.key === 'Backspace' && !this.digits[index - 1] && index > 1) {
        document.getElementById(`${prefix}-${index - 1}`)?.focus();
      }
      if (event.key === 'Backspace') {
        this.digits[index - 1] = '';
        this.syncCombined(combinedId);
      }
      if (event.key === 'ArrowLeft' && index > 1) document.getElementById(`${prefix}-${index - 1}`)?.focus();
      if (event.key === 'ArrowRight' && index < 6) document.getElementById(`${prefix}-${index + 1}`)?.focus();
    },
    handlePaste(event) {
      event.preventDefault();
      const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      if (pasted.length !== 6) return;
      pasted.split('').forEach((char, i) => {
        this.digits[i] = char;
        const input = document.getElementById(`${prefix}-${i + 1}`);
        if (input) input.value = char;
      });
      this.syncCombined(combinedId);
      document.getElementById(`${prefix}-6`)?.focus();
      this.$dispatch('otp-complete', { otp: pasted });
    },
    syncCombined(id) {
      const combined = document.getElementById(id);
      if (!combined) return;
      combined.value = this.digits.join('');
      combined.dispatchEvent(new Event('input', { bubbles: true }));
    },
  };
};
