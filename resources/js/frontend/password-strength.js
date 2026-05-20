window.passwordStrength = function passwordStrength() {
  return {
    strength: 0,
    label: '',
    color: '#e2e8f0',
    check(password) {
      if (!password) {
        this.strength = 0;
        this.label = '';
        this.color = '#e2e8f0';
        return;
      }
      let score = 0;
      if (password.length >= 6) score += 20;
      if (password.length >= 10) score += 20;
      if (/[A-Z]/.test(password)) score += 20;
      if (/[0-9]/.test(password)) score += 20;
      if (/[^A-Za-z0-9]/.test(password)) score += 20;
      this.strength = score;
      if (score <= 20) { this.label = 'Weak'; this.color = '#ef4444'; return; }
      if (score <= 40) { this.label = 'Fair'; this.color = '#f59e0b'; return; }
      if (score <= 60) { this.label = 'Good'; this.color = '#eab308'; return; }
      if (score <= 80) { this.label = 'Strong'; this.color = '#22c55e'; return; }
      this.label = 'Very Strong';
      this.color = '#10b981';
    },
  };
};
