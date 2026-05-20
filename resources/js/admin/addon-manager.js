window.addonManager = function addonManager() {
  return {
    showLicense: false,
    showUpload: false,
    form: { slug: '', key: '' },
    progress: '',
    prefillSlug(slug) { this.form.slug = slug; this.showLicense = true; },
    async installLicense() {
      this.progress = 'Verifying license...';
      const res = await fetch('/admin/addons/install', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify({ addon_slug: this.form.slug, addon_key: this.form.key }) });
      const data = await res.json();
      this.progress = data.message || (data.success ? 'Addon installed!' : 'Failed');
      if (data.success) setTimeout(() => location.reload(), 1000);
    },
    async uploadInstall() {
      const file = document.getElementById('zip-file')?.files?.[0];
      if (!file) return;
      const fd = new FormData();
      fd.append('zip_file', file);
      this.progress = 'Uploading...';
      const res = await fetch('/admin/addons/upload-install', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: fd });
      const data = await res.json();
      this.progress = data.message || (data.success ? 'Installed' : 'Failed');
      if (data.success) setTimeout(() => location.reload(), 1000);
    },
    async toggleAddon(slug, active) {
      const endpoint = active ? '/admin/addons/deactivate' : '/admin/addons/activate';
      const res = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify({ slug }) });
      const data = await res.json();
      alert(data.message || (data.success ? 'Done' : 'Failed'));
      if (data.success) location.reload();
    },
    async uninstallAddon(slug) {
      if (!confirm('Uninstall addon completely?')) return;
      const res = await fetch('/admin/addons/uninstall', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify({ slug, delete_data: false }) });
      const data = await res.json();
      alert(data.message || (data.success ? 'Uninstalled' : 'Failed'));
      if (data.success) location.reload();
    },
  };
};
