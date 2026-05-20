@props([
    'href',
    'active' => false,
])
<a
    href="{{ $href }}"
    @class([
        'zsb-sub-link',
        'is-active' => $active,
    ])
>
    {{ $slot }}
</a>
