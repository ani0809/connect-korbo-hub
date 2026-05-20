@props([
    'label',
    'icon',
    'groupKey',
    'active' => false,
])
<button
    type="button"
    @click="if (sidebarOpen) { toggleGroup('{{ addslashes($groupKey) }}'); } else { toggleSidebar(); if (!expandedGroups.includes('{{ addslashes($groupKey) }}')) expandedGroups.push('{{ addslashes($groupKey) }}'); }"
    :title="!sidebarOpen ? @js($label) : ''"
    :class="!sidebarOpen ? 'is-collapsed' : ''"
    @class([
        'zsb-item zsb-group-trigger',
        'is-active' => $active,
    ])
>
    <x-admin.nav-icon :name="$icon" class="zsb-icon" />
    <span class="zsb-label" x-show="sidebarOpen" x-transition.opacity.duration.150ms x-cloak>{{ $label }}</span>
    <svg xmlns="http://www.w3.org/2000/svg" class="zsb-chevron" :class="expandedGroups.includes('{{ addslashes($groupKey) }}') ? 'is-open' : ''" x-show="sidebarOpen" x-transition.opacity.duration.150ms viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div
    class="zsb-sub"
    x-show="sidebarOpen && expandedGroups.includes('{{ addslashes($groupKey) }}')"
    x-transition:enter="transition ease-out duration-180"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-140"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    x-cloak
>
    {{ $slot }}
</div>
