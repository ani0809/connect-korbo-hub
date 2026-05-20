<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $htmlDir ?? (function_exists('current_language_dir') ? current_language_dir() : 'ltr') }}" class="{{ setting('dark_mode_default') === 'dark' ? 'dark' : '' }}" data-theme="{{ setting('dark_mode_default') === 'dark' ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($faviconPath = setting('favicon'))
    @if($faviconPath)
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$faviconPath) }}">
    @endif
    <title>{{ config('app.name') }} Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin/app.css','resources/css/admin/theme-settings.css','resources/css/admin/design-handoff-admin.css','resources/js/admin/app.js','resources/js/admin/theme-settings.js','resources/js/shared/media-picker.js'])
    <script>
      // Prevent stale frontend service worker assets from affecting admin JS updates.
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then((regs) => {
          regs.forEach((reg) => reg.unregister());
        });
      }
    </script>
    @stack('styles')
</head>
<body
    class="admin-body"
    x-data="{
        sidebarOpen: (() => {
            try {
                const saved = localStorage.getItem('admin_sidebar_open');
                return saved === null ? true : saved === '1';
            } catch (e) {
                return true;
            }
        })(),
        mobileOpen: false,
        expandedGroups: (() => {
            try {
                const saved = localStorage.getItem('admin_expanded_groups');
                const parsed = saved ? JSON.parse(saved) : null;
                return Array.isArray(parsed) && parsed.length > 0 ? parsed : ['Catalog', 'Sales'];
            } catch (e) {
                return ['Catalog', 'Sales'];
            }
        })(),
        lastExpandedGroups: [],
        toggleSidebar() {
            if (this.sidebarOpen) {
                this.lastExpandedGroups = [...this.expandedGroups];
                this.expandedGroups = [];
            } else if (this.lastExpandedGroups.length > 0) {
                this.expandedGroups = [...this.lastExpandedGroups];
            }
            this.sidebarOpen = !this.sidebarOpen;
            try {
                localStorage.setItem('admin_sidebar_open', this.sidebarOpen ? '1' : '0');
                localStorage.setItem('admin_expanded_groups', JSON.stringify(this.expandedGroups));
            } catch (e) {}
        },
        toggleGroup(label) {
            const i = this.expandedGroups.indexOf(label);
            if (i >= 0) this.expandedGroups.splice(i, 1);
            else this.expandedGroups.push(label);
            try { localStorage.setItem('admin_expanded_groups', JSON.stringify(this.expandedGroups)); } catch (e) {}
        },
    }"
>
<div class="admin-shell">
    @include('admin.layouts.sidebar')

    <div class="admin-main">
        @include('admin.layouts.topbar')

        <div class="admin-breadcrumb px-shell">
            <span>@yield('breadcrumb', 'Dashboard')</span>
        </div>

        <main class="admin-content px-shell">
            <div class="admin-page-header">
                <h1 class="admin-page-title">@yield('title', 'Cibato Commerce Admin')</h1>
                <div class="admin-page-actions">@yield('actions')</div>
            </div>

            @if(session('ok'))
                <div class="admin-alert success">{{ session('ok') }}</div>
            @endif
            @if(session('success'))
                <div class="admin-alert success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="admin-alert warning">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>

        <footer class="admin-footer px-shell">v{{ config('shop.version') }} - {{ date('Y') }} {{ config('shop.name') }}</footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
