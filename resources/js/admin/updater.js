window.updater = function updater() {
  return {
    checked: false,
    hasUpdate: false,
    latest: '',
    changelog: '',
    checkForUpdate() {
      fetch('/admin/update/check', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } })
        .then((r) => r.json())
        .then((data) => {
          this.checked = true;
          this.hasUpdate = !!data.has_update;
          this.latest = data.latest_version || '';
          const lines = Array.isArray(data.changelog) ? data.changelog : (typeof data.changelog === 'string' ? data.changelog.split('\n') : []);
          this.changelog = lines.map((l) => `<li>${l}</li>`).join('');
        });
    },
    async startUpdate() {
      if (!confirm('This will update your site and create a backup. Continue?')) return;
      document.getElementById('progress-panel')?.classList.remove('hidden');
      const ev = new EventSource('/admin/update/apply');
      ev.onmessage = (event) => {
        const data = JSON.parse(event.data);
        this.updateProgress(data);
        if (data.step === 'complete') { ev.close(); setTimeout(() => window.location.reload(), 2000); }
        if (data.step === 'error') { ev.close(); alert(data.message); }
      };
      ev.onerror = () => { ev.close(); alert('Connection lost during update'); };
    },
    updateProgress(data) {
      const steps = ['backup', 'extract', 'files', 'migrate', 'cache', 'complete'];
      const stepEl = document.getElementById(`step-${data.step}`);
      if (stepEl) { stepEl.classList.remove('pending'); stepEl.classList.add(data.step === 'error' ? 'error' : 'done'); stepEl.textContent = `? ${data.message}`; }
      const current = steps.indexOf(data.step);
      const progress = ((current + 1) / steps.length) * 100;
      const bar = document.querySelector('.progress-bar'); if (bar) bar.style.width = `${progress}%`;
    },
    async uploadManual() {
      const file = document.getElementById('manual-update-zip')?.files?.[0];
      if (!file) return;
      const fd = new FormData(); fd.append('update_zip', file);
      const res = await fetch('/admin/update/upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: fd });
      const data = await res.json(); alert(data.message || (data.success ? 'Uploaded' : 'Failed'));
    },
    startApplyOnly() { this.startUpdate(); },
  };
};
