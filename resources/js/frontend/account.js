(function(){
  const showTab = (id) => {
    document.querySelectorAll('.account-tab').forEach((el) => el.classList.add('hidden'));
    const tab = document.getElementById(`tab-${id}`); if (tab) tab.classList.remove('hidden');
  };
  document.querySelectorAll('.tab-btn').forEach((btn) => btn.addEventListener('click', () => showTab(btn.dataset.tab)));

  const open = document.getElementById('open-address-modal');
  const close = document.getElementById('close-address-modal');
  const modal = document.getElementById('address-modal');
  open?.addEventListener('click', () => { modal?.classList.remove('hidden'); modal?.classList.add('flex'); });
  close?.addEventListener('click', () => { modal?.classList.add('hidden'); modal?.classList.remove('flex'); });

  const pwd = document.getElementById('new-password') || document.getElementById('reg-password');
  const meter = document.getElementById('password-strength') || document.getElementById('password-strength-register');
  pwd?.addEventListener('input', () => {
    const v = pwd.value; let s = 0;
    if (v.length >= 8) s++; if (/[A-Z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
    const map = ['Weak','Fair','Good','Strong']; const colors = ['text-red-600','text-orange-500','text-yellow-600','text-green-600'];
    meter.className = `text-xs ${colors[Math.max(0,s-1)]}`; meter.textContent = v ? map[Math.max(0,s-1)] : '';
  });

  const otpInputs = Array.from(document.querySelectorAll('.otp-input'));
  otpInputs.forEach((input, i) => {
    input.addEventListener('input', () => { if (input.value && otpInputs[i + 1]) otpInputs[i + 1].focus(); if (otpInputs.every((x) => x.value)) document.getElementById('otp-form')?.submit(); });
    input.addEventListener('keydown', (e) => { if (e.key === 'Backspace' && !input.value && otpInputs[i - 1]) otpInputs[i - 1].focus(); });
  });
  document.getElementById('otp-form')?.addEventListener('paste', (e) => {
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
    if (!text) return; e.preventDefault(); text.split('').forEach((ch, i) => { if (otpInputs[i]) otpInputs[i].value = ch; });
    if (otpInputs.every((x) => x.value)) document.getElementById('otp-form')?.submit();
  });

  document.getElementById('toggle-ticket-form')?.addEventListener('click', () => document.getElementById('ticket-form')?.classList.toggle('hidden'));
})();
