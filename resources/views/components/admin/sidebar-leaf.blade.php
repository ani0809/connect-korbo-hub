@props([
    'href',
    'icon',
    'active' => false,
])
<a
    href="{{ $href }}"
    :title="!sidebarOpen ? @js(trim((string) $slot)) : ''"
    @class([
        'zsb-item zsb-leaf',
        'is-active' => $active,
    ])
>
    <x-admin.nav-icon class="zsb-icon" :name="$icon" />
    <span class="zsb-label" x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak>{{ $slot }}</span>
</a>
