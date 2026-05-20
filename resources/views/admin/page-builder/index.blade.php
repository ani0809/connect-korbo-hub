@extends('admin.layouts.app')

@section('title', 'Page Builder')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Page Builder</h1>
        <p class="page-subtitle">Create and manage custom pages with drag-and-drop builder</p>
    </div>
    <a href="{{ route('admin.builder.create') }}" class="btn btn-primary">+ New Page</a>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-card-icon primary">📄</div>
        <div class="stat-card-value">{{ $stats['total'] }}</div>
        <div class="stat-card-label">Total Pages</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon success">✅</div>
        <div class="stat-card-value">{{ $stats['published'] }}</div>
        <div class="stat-card-label">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon warning">📝</div>
        <div class="stat-card-value">{{ $stats['draft'] }}</div>
        <div class="stat-card-label">Drafts</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
@forelse($pages as $page)
    <div class="card">
        <div style="height:160px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);display:flex;align-items:center;justify-content:center;font-size:48px;border-radius:12px 12px 0 0;position:relative">
            📄
            <div style="position:absolute;top:12px;right:12px">
                <span class="badge {{ $page->status === 'published' ? 'badge-success' : 'badge-gray' }}">{{ ucfirst($page->status) }}</span>
            </div>
            @if($page->is_homepage)
            <div style="position:absolute;top:12px;left:12px">
                <span class="badge badge-primary">🏠 Homepage</span>
            </div>
            @endif
        </div>
        <div class="card-body">
            <h3 style="font-size:15px;font-weight:600;color:var(--gray-900);margin-bottom:4px">{{ $page->name }}</h3>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                <span style="font-size:12px;color:var(--gray-400)">/p/{{ $page->slug }}</span>
                <span style="font-size:11px;background:var(--gray-100);color:var(--gray-500);padding:2px 6px;border-radius:4px">{{ $page->page_type }}</span>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('admin.builder.edit', $page->id) }}" class="btn btn-primary btn-sm">✏️ Edit</a>
                @if($page->status === 'published')
                <a href="{{ route('builder.page.show', $page->slug) }}" target="_blank" class="btn btn-secondary btn-sm">👁️ View</a>
                @endif
                <div class="dropdown">
                    <button class="btn btn-ghost btn-sm" onclick="toggleMenu({{ $page->id }})">⋯</button>
                    <div class="dropdown-menu" id="menu-{{ $page->id }}" style="display:none">
                        @if(!$page->is_homepage && $page->status === 'published')
                        <button class="dropdown-item" onclick="setHomepage({{ $page->id }})">🏠 Set as Homepage</button>
                        @endif
                        <button class="dropdown-item" onclick="duplicatePage({{ $page->id }})">⧉ Duplicate</button>
                        @if($page->status === 'draft')
                        <button class="dropdown-item" onclick="publishPage({{ $page->id }})">🚀 Publish</button>
                        @else
                        <button class="dropdown-item" onclick="unpublishPage({{ $page->id }})">📄 Unpublish</button>
                        @endif
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item danger" onclick="deletePage({{ $page->id }}, @js($page->name))">🗑️ Delete</button>
                    </div>
                </div>
            </div>

            <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--gray-100);font-size:12px;color:var(--gray-400)">
                Updated {{ $page->updated_at->diffForHumans() }} by {{ $page->creator?->name ?? 'System' }}
            </div>
        </div>
    </div>
@empty
    <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--gray-400)">
        <div style="font-size:48px;margin-bottom:12px">📄</div>
        <h3 style="font-size:18px;font-weight:600;color:var(--gray-600);margin-bottom:8px">No pages yet</h3>
        <p style="margin-bottom:20px">Create your first page with the visual builder</p>
        <a href="{{ route('admin.builder.create') }}" class="btn btn-primary">+ Create First Page</a>
    </div>
@endforelse
</div>

<div style="margin-top:18px">{{ $pages->links() }}</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function toggleMenu(id) {
    const el = document.getElementById(`menu-${id}`);
    if (!el) return;
    const show = el.style.display !== 'block';
    document.querySelectorAll('.dropdown-menu').forEach((m) => { m.style.display = 'none'; });
    el.style.display = show ? 'block' : 'none';
}

async function postJson(url, method = 'POST') {
    const r = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    return r.json();
}

async function setHomepage(pageId) {
    if (!confirm('Set this page as the homepage?')) return;
    const data = await postJson(`/admin/page-builder/${pageId}/set-homepage`);
    if (data.success) location.reload();
}

async function deletePage(pageId, name) {
    if (!confirm(`Delete "${name}"? Cannot be undone.`)) return;
    const data = await postJson(`/admin/page-builder/${pageId}`, 'DELETE');
    if (data.success) location.reload();
}

async function duplicatePage(pageId) {
    const data = await postJson(`/admin/page-builder/${pageId}/duplicate`);
    if (data.success) location.reload();
}

async function publishPage(pageId) {
    const data = await postJson(`/admin/page-builder/${pageId}/publish`);
    if (data.success) location.reload();
}

async function unpublishPage(pageId) {
    const data = await postJson(`/admin/page-builder/${pageId}/unpublish`);
    if (data.success) location.reload();
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach((m) => { m.style.display = 'none'; });
    }
});
</script>
@endpush
