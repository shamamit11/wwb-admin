@props([
    'as' => 'button',
    'href' => null,
    'tone' => 'default',
])

@php
    $classes = $tone === 'danger'
        ? 'h-8 px-2.5 text-xs text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]'
        : 'h-8 px-2.5 text-xs';
@endphp

<x-ui.button
    :as="$as"
    :href="$href"
    variant="ghost"
    size="sm"
    {{ $attributes->class($classes) }}
>
    {{ $slot }}
</x-ui.button>
