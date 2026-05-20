<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Builder — {{ $page->name }}</title>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <script type="module" src="{{ Vite::asset('resources/js/admin/builder/index.js') }}"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, system-ui, sans-serif; background: #0f172a; height: 100vh; overflow: hidden; }
        .builder-topbar { height: 54px; background: #020617; border-bottom: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; padding: 0 14px; }
        .left, .right, .center { display: flex; align-items: center; gap: 8px; }
        .name { color: #fff; font-weight: 600; font-size: 14px; }
        .status { font-size: 11px; border-radius: 999px; padding: 2px 8px; font-weight: 700; }
        .status.draft { background: #fef3c7; color: #92400e; }
        .status.published { background: #d1fae5; color: #065f46; }
        .btn { border: 1px solid #334155; background: #1e293b; color: #cbd5e1; border-radius: 6px; font-size: 12px; padding: 6px 10px; cursor: pointer; }
        .btn:hover { background: #334155; color: #fff; }
        .btn.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .btn.success { background: #10b981; border-color: #10b981; color: #fff; }
        .device button.active { background: #475569; color: #fff; }
        .save-indicator { color: #94a3b8; font-size: 11px; min-width: 110px; }
        .save-indicator.saving { color: #f59e0b; }
        .save-indicator.saved { color: #10b981; }
        .builder-main { height: calc(100vh - 54px); display: grid; grid-template-columns: 280px 1fr 320px; }
        .panel { background: #0f172a; border-right: 1px solid #1e293b; overflow: hidden; display: flex; flex-direction: column; }
        .panel.right { border-right: 0; border-left: 1px solid #1e293b; }
        .panel-tabs { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #1e293b; }
        .panel-tab { border: 0; background: transparent; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; padding: 9px; cursor: pointer; }
        .panel-tab.active { color: #60a5fa; border-bottom: 2px solid #2563eb; }
        .panel-content { flex: 1; overflow: auto; }
        #gjs { height: 100%; }
        .canvas { position: relative; background: #e2e8f0; }
        .loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 5; background: #e2e8f0; color: #334155; font-size: 14px; }
        .right-head { padding: 10px 12px; color: #cbd5e1; font-size: 12px; border-bottom: 1px solid #1e293b; background: #020617; }
        .page-settings { padding: 12px; color: #cbd5e1; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 11px; margin-bottom: 6px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
        .field input, .field textarea { width: 100%; background: #1e293b; color: #fff; border: 1px solid #334155; border-radius: 6px; padding: 8px 9px; font-size: 13px; }
        .toast { position: fixed; top: 68px; right: 16px; z-index: 9999; background: #2563eb; color: white; padding: 10px 12px; border-radius: 8px; font-size: 12px; }
        .toast.success { background: #10b981; } .toast.error { background: #ef4444; }
    </style>
</head>
<body>
<div class="builder-topbar">
    <div class="left">
        <a href="{{ route('admin.builder.index') }}" class="btn">← Exit</a>
        <span class="name">{{ $page->name }}</span>
        <span class="status {{ $page->status }}">{{ ucfirst($page->status) }}</span>
    </div>
    <div class="center device">
        <button class="btn active" id="d-desktop" onclick="setDevice('Desktop','d-desktop')">🖥</button>
        <button class="btn" id="d-tablet" onclick="setDevice('Tablet','d-tablet')">📱</button>
        <button class="btn" id="d-mobile" onclick="setDevice('Mobile portrait','d-mobile')">📲</button>
    </div>
    <div class="right">
        <span id="save-indicator" class="save-indicator">● Saved</span>
        <button class="btn" onclick="builder?.Commands.run('core:undo')">↩</button>
        <button class="btn" onclick="builder?.Commands.run('core:redo')">↪</button>
        <button class="btn" onclick="toggleSettings()">⚙ Settings</button>
        <button class="btn" onclick="savePage()">💾 Save</button>
        <button class="btn success" onclick="publishPage()">🚀 Publish</button>
    </div>
</div>

<div class="builder-main">
    <div class="panel">
        <div class="panel-tabs">
            <button class="panel-tab active" onclick="switchTab('blocks',this)">Blocks</button>
            <button class="panel-tab" onclick="switchTab('layers',this)">Layers</button>
            <button class="panel-tab" onclick="switchTab('assets',this)">Assets</button>
        </div>
        <div class="panel-content">
            <div id="tab-blocks"><div id="gjs-blocks"></div></div>
            <div id="tab-layers" style="display:none"><div id="gjs-layers"></div></div>
            <div id="tab-assets" style="display:none"><div id="gjs-assets"></div></div>
        </div>
    </div>
    <div class="canvas">
        <div id="loading" class="loading">Loading editor…</div>
        <div id="gjs"></div>
    </div>
    <div class="panel right">
        <div id="element-settings">
            <div class="right-head">Element Settings</div>
            <div class="panel-content">
                <div id="gjs-traits"></div>
                <div id="gjs-styles"></div>
            </div>
        </div>
        <div id="page-settings" style="display:none">
            <div class="right-head">Page Settings</div>
            <div class="panel-content page-settings">
                <div class="field">
                    <label>Page Name</label>
                    <input id="setting-page-name" value="{{ $page->name }}">
                </div>
                <div class="field">
                    <label>Meta Title</label>
                    <input id="setting-meta-title" value="{{ $page->meta_title }}">
                </div>
                <div class="field">
                    <label>Meta Description</label>
                    <textarea id="setting-meta-desc" rows="4">{{ $page->meta_description }}</textarea>
                </div>
                <div class="field">
                    <label>Slug</label>
                    <input id="setting-slug" value="{{ $page->slug }}">
                </div>
                <button class="btn primary" style="width:100%" onclick="savePageSettings()">Save Settings</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/grapesjs@0.21.7/dist/grapes.min.js"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage@1.0.2/dist/grapesjs-preset-webpage.min.js"></script>
<script src="https://unpkg.com/grapesjs-blocks-basic@0.1.6/dist/grapesjs-blocks-basic.min.js"></script>
<script src="https://unpkg.com/grapesjs-plugin-forms@2.0.5/dist/grapesjs-plugin-forms.min.js"></script>
<script src="https://unpkg.com/grapesjs-plugin-export@1.0.3/dist/grapesjs-plugin-export.min.js"></script>
<script src="https://unpkg.com/grapesjs-custom-code@1.0.0/dist/grapesjs-custom-code.min.js"></script>
<script>
const PAGE_ID = {{ $page->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let builder = null;
let isDirty = false;

function toast(message, type = 'success', ms = 2500) {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), ms);
}

function indicator(state, savedAt = '') {
    const el = document.getElementById('save-indicator');
    el.className = `save-indicator ${state}`;
    if (state === 'saving') el.textContent = '⟳ Saving...';
    else if (state === 'saved') el.textContent = savedAt ? `✓ Saved ${savedAt}` : '✓ Saved';
    else if (state === 'error') el.textContent = '✗ Save failed';
    else el.textContent = '● Unsaved changes';
}

function switchTab(tab, btn) {
    ['blocks','layers','assets'].forEach((k) => document.getElementById(`tab-${k}`).style.display = k === tab ? 'block' : 'none');
    document.querySelectorAll('.panel-tab').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
}

function toggleSettings() {
    const a = document.getElementById('element-settings');
    const b = document.getElementById('page-settings');
    const showPage = b.style.display === 'none';
    a.style.display = showPage ? 'none' : 'block';
    b.style.display = showPage ? 'block' : 'none';
}

function setDevice(deviceName, btnId) {
    builder?.setDevice(deviceName);
    document.querySelectorAll('.device .btn').forEach((b) => b.classList.remove('active'));
    document.getElementById(btnId)?.classList.add('active');
}

async function savePage(auto = false) {
    if (!builder) return;
    indicator('saving');
    try {
        const payload = {
            html: builder.getHtml(),
            css: builder.getCss(),
            components: builder.getComponents(),
            styles: builder.getStyle(),
        };
        const r = await fetch(`/api/builder/save/${PAGE_ID}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await r.json();
        if (!data.success) throw new Error('save');
        isDirty = false;
        indicator('saved', data.saved_at || '');
        if (!auto) toast('Draft saved', 'success');
    } catch (e) {
        indicator('error');
        toast('Save failed', 'error');
    }
}

async function publishPage() {
    await savePage(true);
    try {
        const r = await fetch(`/admin/page-builder/${PAGE_ID}/publish`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await r.json();
        if (!data.success) throw new Error('publish');
        toast('Page published', 'success');
    } catch (e) {
        toast('Publish failed', 'error');
    }
}

async function savePageSettings() {
    const payload = {
        name: document.getElementById('setting-page-name').value,
        slug: document.getElementById('setting-slug').value,
        meta_title: document.getElementById('setting-meta-title').value,
        meta_description: document.getElementById('setting-meta-desc').value,
    };
    const r = await fetch(`/admin/page-builder/${PAGE_ID}/settings`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    });
    const data = await r.json();
    toast(data.success ? 'Settings saved' : 'Settings failed', data.success ? 'success' : 'error');
}

function ecommerceBlocks() {
    return [
        { id: 'hero', label: 'Hero', category: 'Sections', content: '<section style="padding:80px 24px;text-align:center;background:#1e3a8a;color:#fff"><h1 style="font-size:42px">Hero section</h1><p>Drag and edit content</p></section>' },
        { id: 'product-grid', label: 'Products Grid', category: 'eCommerce', content: '<div data-gjs-type="ec-products-grid" data-count="4" style="padding:24px"><h2>Featured products</h2><div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px"><div style="background:#fff;border:1px solid #ddd;padding:12px">Product</div><div style="background:#fff;border:1px solid #ddd;padding:12px">Product</div><div style="background:#fff;border:1px solid #ddd;padding:12px">Product</div><div style="background:#fff;border:1px solid #ddd;padding:12px">Product</div></div></div>' },
        { id: 'cat-grid', label: 'Categories', category: 'eCommerce', content: '<div data-gjs-type="ec-categories" data-count="6" style="padding:24px"><h2>Categories</h2><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px"><div style="background:#fff;border:1px solid #ddd;padding:16px">Category</div><div style="background:#fff;border:1px solid #ddd;padding:16px">Category</div><div style="background:#fff;border:1px solid #ddd;padding:16px">Category</div></div></div>' },
    ];
}

document.addEventListener('DOMContentLoaded', async () => {
    let initial = { components: '', styles: '' };
    try {
        const r = await fetch(`/api/builder/load/${PAGE_ID}`, { headers: { 'Accept': 'application/json' } });
        initial = await r.json();
    } catch {}

    const extended = window.BuilderWidgets?.getExtendedBlocks?.() || [];
    builder = grapesjs.init({
        container: '#gjs',
        fromElement: false,
        height: '100%',
        width: 'auto',
        storageManager: false,
        panels: { defaults: [] },
        blockManager: { appendTo: '#gjs-blocks', blocks: [...ecommerceBlocks(), ...extended] },
        layerManager: { appendTo: '#gjs-layers' },
        assetManager: {
            appendTo: '#gjs-assets',
            upload: '/api/builder/upload-image',
            uploadName: 'file',
            headers: { 'X-CSRF-TOKEN': CSRF },
        },
        traitManager: { appendTo: '#gjs-traits' },
        styleManager: { appendTo: '#gjs-styles' },
        deviceManager: { devices: [{ name: 'Desktop', width: '' }, { name: 'Tablet', width: '768px', widthMedia: '768px' }, { name: 'Mobile portrait', width: '480px', widthMedia: '480px' }] },
        canvas: { styles: ['{{ Vite::asset('resources/css/frontend/app.css') }}'] },
        components: initial.components || '',
        style: initial.styles || '',
        plugins: ['grapesjs-preset-webpage', 'grapesjs-blocks-basic', 'grapesjs-plugin-forms', 'grapesjs-plugin-export', 'grapesjs-custom-code'],
        pluginsOpts: { 'grapesjs-blocks-basic': { flexGrid: true } },
    });

    window.BuilderWidgets?.registerDynamicComponents?.(builder);
    document.getElementById('loading').style.display = 'none';
    builder.on('change:changesCount', () => { isDirty = true; indicator('unsaved'); });
    setInterval(() => { if (isDirty) savePage(true); }, 30000);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); savePage(); }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); builder.Commands.run('core:undo'); }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') { e.preventDefault(); builder.Commands.run('core:redo'); }
    });

    window.addEventListener('beforeunload', (e) => {
        if (!isDirty) return;
        e.preventDefault();
        e.returnValue = '';
    });
});
</script>
</body>
</html>
