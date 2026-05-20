<header
    class="admin-topbar ztb border-b border-[hsl(var(--border))] bg-[hsl(var(--card))]"
    x-data="{ notifOpen: false, profileOpen: false }"
    @click.outside="notifOpen = false; profileOpen = false"
    @keydown.escape.window="notifOpen = false; profileOpen = false"
>
    <button class="ztb-mobile-toggle lg-hidden" @click="mobileOpen = !mobileOpen" aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
    </button>

    <form class="ztb-search" action="{{ route('admin.products.index') }}" method="GET">
        <span class="ztb-search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </span>
        <input id="admin-topbar-search" type="text" name="search" placeholder="Search pages, actions..." autocomplete="off">
        <span class="ztb-kbd hidden md:inline-flex">Ctrl K</span>
        <div id="admin-search-panel" class="ztb-search-panel" hidden>
            <div class="ztb-search-head">Quick actions</div>
            <div id="admin-search-results" class="ztb-search-results"></div>
            <div id="admin-search-empty" class="ztb-search-empty" hidden>No matching action</div>
        </div>
    </form>

    <div class="ztb-actions">
        <button class="ztb-icon-btn" id="admin-theme-toggle" type="button" aria-label="Toggle dark mode" aria-pressed="false">
            <svg class="ztb-theme-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9c0-.35-.03-.7-.08-1.04A7 7 0 0 1 12 3z"/></svg>
            <svg class="ztb-theme-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        </button>
        <div class="ztb-pop-wrap">
            <button class="ztb-icon-btn ztb-notif-btn" aria-label="Notifications" @click.stop="notifOpen = !notifOpen; profileOpen = false" :aria-expanded="notifOpen ? 'true' : 'false'">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M9 17a3 3 0 0 0 6 0"/></svg>
                <span class="ztb-notif-dot"></span>
            </button>
            <div
                class="ztb-menu ztb-notif-menu"
                x-show="notifOpen"
                x-transition:enter="transition ease-out duration-160"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-120"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                x-cloak
            >
                <div class="ztb-menu-head">
                    <strong>Notifications</strong>
                    <button type="button">Mark all read</button>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="ztb-menu-item">
                    <strong>New order received</strong>
                    <small>Order #1024 just placed</small>
                </a>
                <a href="{{ route('admin.sellers.index') }}" class="ztb-menu-item">
                    <strong>Seller request</strong>
                    <small>1 new seller pending approval</small>
                </a>
                <a href="{{ route('admin.products.index') }}" class="ztb-menu-item">
                    <strong>Low stock warning</strong>
                    <small>3 products below stock threshold</small>
                </a>
            </div>
        </div>
        <span class="ztb-separator"></span>
        <div class="ztb-pop-wrap">
            <button type="button" class="ztb-user" @click.stop="profileOpen = !profileOpen; notifOpen = false" :aria-expanded="profileOpen ? 'true' : 'false'">
                <div class="ztb-avatar initials">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</div>
                <div class="ztb-user-meta">
                    <strong>{{ auth()->user()->name ?? 'Admin User' }}</strong>
                    @if((auth()->user()->role ?? null) === 'staff')
                        <small class="text-[hsl(var(--warning))] font-medium">Staff</small>
                    @else
                        <small>Super Admin</small>
                    @endif
                </div>
                <svg class="ztb-user-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div
                class="ztb-menu ztb-profile-menu"
                x-show="profileOpen"
                x-transition:enter="transition ease-out duration-160"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-120"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                x-cloak
            >
                <a class="ztb-menu-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="ztb-menu-link" href="{{ route('admin.settings.index') }}">Settings</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="ztb-menu-link ztb-menu-link-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

@once
@push('scripts')
<script>
(() => {
    const btn = document.getElementById('admin-theme-toggle');
    const search = document.getElementById('admin-topbar-search');
    const searchPanel = document.getElementById('admin-search-panel');
    const searchResults = document.getElementById('admin-search-results');
    const searchEmpty = document.getElementById('admin-search-empty');
    const searchActions = [
        { label: 'Dashboard', hint: 'Overview and analytics', url: @js(route('admin.dashboard')) },
        { label: 'Orders', hint: 'Manage and track orders', url: @js(route('admin.orders.index')) },
        { label: 'Products', hint: 'Catalog and inventory', url: @js(route('admin.products.index')) },
        { label: 'Customers', hint: 'Customer list and profiles', url: @js(route('admin.customers.index')) },
        { label: 'Sellers', hint: 'Marketplace sellers', url: @js(route('admin.sellers.index')) },
        { label: 'Settings', hint: 'Admin configuration', url: @js(route('admin.settings.index')) },
    ];
    let activeIndex = -1;
    if (!btn) return;

    const applyThemeUi = (mode) => {
        const moon = btn.querySelector('.ztb-theme-moon');
        const sun = btn.querySelector('.ztb-theme-sun');
        const isDark = mode === 'dark';
        btn.classList.toggle('is-dark', isDark);
        if (moon) moon.style.display = isDark ? 'none' : 'block';
        if (sun) sun.style.display = isDark ? 'block' : 'none';
        btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    };

    btn.addEventListener('click', () => {
        const root = document.documentElement;
        const isDark = root.getAttribute('data-theme') === 'dark';
        const next = isDark ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        root.classList.toggle('dark', next === 'dark');
        try { localStorage.setItem('admin_theme_mode', next); } catch (e) {}
        applyThemeUi(next);
    });
    try {
        const saved = localStorage.getItem('admin_theme_mode');
        if (saved === 'dark' || saved === 'light') {
            const root = document.documentElement;
            root.setAttribute('data-theme', saved);
            root.classList.toggle('dark', saved === 'dark');
            applyThemeUi(saved);
        } else {
            applyThemeUi(document.documentElement.getAttribute('data-theme') || 'light');
        }
    } catch (e) {}

    document.addEventListener('keydown', (event) => {
        const isShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';
        if (!isShortcut || !search) return;
        event.preventDefault();
        openSearch();
        search.focus();
        search.select();
    });

    const closeSearch = () => {
        if (!searchPanel) return;
        searchPanel.hidden = true;
        activeIndex = -1;
    };

    const openSearch = () => {
        if (!searchPanel) return;
        searchPanel.hidden = false;
    };

    const renderSearchResults = (query = '') => {
        if (!searchResults || !searchEmpty) return;
        const q = query.trim().toLowerCase();
        const filtered = searchActions.filter((item) =>
            item.label.toLowerCase().includes(q) || item.hint.toLowerCase().includes(q)
        );

        searchResults.innerHTML = '';
        filtered.slice(0, 6).forEach((item, idx) => {
            const row = document.createElement('a');
            row.href = item.url;
            row.className = 'ztb-search-item';
            row.dataset.index = String(idx);
            row.innerHTML = `<strong>${item.label}</strong><small>${item.hint}</small>`;
            row.addEventListener('mouseenter', () => {
                activeIndex = idx;
                updateActiveSearchItem();
            });
            searchResults.appendChild(row);
        });

        if (filtered.length === 0) {
            searchEmpty.hidden = false;
            activeIndex = -1;
        } else {
            searchEmpty.hidden = true;
            activeIndex = 0;
            updateActiveSearchItem();
        }
    };

    const updateActiveSearchItem = () => {
        if (!searchResults) return;
        const rows = searchResults.querySelectorAll('.ztb-search-item');
        rows.forEach((row, idx) => {
            row.classList.toggle('is-active', idx === activeIndex);
        });
    };

    if (search) {
        search.addEventListener('focus', () => {
            openSearch();
            renderSearchResults(search.value);
        });
        search.addEventListener('input', () => {
            openSearch();
            renderSearchResults(search.value);
        });
        search.addEventListener('keydown', (event) => {
            if (!searchPanel || searchPanel.hidden) return;
            const rows = searchResults ? searchResults.querySelectorAll('.ztb-search-item') : [];
            if (event.key === 'ArrowDown' && rows.length > 0) {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % rows.length;
                updateActiveSearchItem();
                return;
            }
            if (event.key === 'ArrowUp' && rows.length > 0) {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + rows.length) % rows.length;
                updateActiveSearchItem();
                return;
            }
            if (event.key === 'Enter' && rows.length > 0 && activeIndex >= 0) {
                event.preventDefault();
                window.location.href = rows[activeIndex].getAttribute('href');
                return;
            }
            if (event.key === 'Escape') {
                closeSearch();
            }
        });
    }

    document.addEventListener('click', (event) => {
        if (!searchPanel || !search) return;
        const target = event.target;
        if (!(target instanceof Node)) return;
        if (search.contains(target) || searchPanel.contains(target)) return;
        closeSearch();
    });
})();
</script>
@endpush
@endonce
