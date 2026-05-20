window.themeSettings = function () {
    return {
        activeTab: 'general',
        unsaved: false,
        previewOpen: false,
        init() {
            const draft = localStorage.getItem('theme_settings_draft');
            if (draft && confirm('Restore unsaved draft settings?')) {
                const parsed = JSON.parse(draft);
                Object.entries(parsed).forEach(([k, v]) => {
                    const el = document.querySelector(`[name="${k}"]`);
                    if (el) {
                        el.value = v;
                    }
                });
            }
            setInterval(() => this.saveDraft(), 30000);
            window.addEventListener('beforeunload', (e) => {
                if (this.unsaved) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        markUnsaved() { this.unsaved = true; },
        saveDraft() {
            const form = document.getElementById('theme-settings-form');
            if (!form) return;
            const fd = new FormData(form);
            const out = {};
            fd.forEach((v, k) => {
                if (typeof v === 'string') out[k] = v;
            });
            localStorage.setItem('theme_settings_draft', JSON.stringify(out));
        },
        async saveAll() {
            const form = document.getElementById('theme-settings-form');
            const res = await fetch('/admin/theme-settings/save', { method: 'POST', body: new FormData(form) });
            const j = await res.json();
            this.toast(j.message, j.success ? 'ok' : 'err');
            if (j.success) { this.unsaved = false; localStorage.removeItem('theme_settings_draft'); }
        },
        async resetSettings() {
            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            fd.append('section', this.activeTab);
            const r = await fetch('/admin/theme-settings/reset', { method: 'POST', body: fd });
            const j = await r.json();
            this.toast(j.message, j.success ? 'ok' : 'err');
        },
        async exportSettings() {
            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            const r = await fetch('/admin/theme-settings/export', { method: 'POST', body: fd });
            const j = await r.json();
            if (!j.success) return this.toast(j.message, 'err');
            const blob = new Blob([JSON.stringify(j.data, null, 2)], { type: 'application/json' });
            const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'theme-settings-backup.json'; a.click();
        },
        async importSettings(event) {
            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            fd.append('file', event.target.files[0]);
            const r = await fetch('/admin/theme-settings/import', { method: 'POST', body: fd });
            const j = await r.json();
            this.toast(j.message, j.success ? 'ok' : 'err');
        },
        togglePreview() { this.previewOpen = !this.previewOpen; },
        toast(msg, type = 'ok') {
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.textContent = msg;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        },
    };
};

document.addEventListener('alpine:init', () => {});
