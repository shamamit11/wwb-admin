@props([
    'as' => 'button',
    'href' => null,
    'tone' => 'default',
])

@php
    $classes = $tone === 'danger'
        ? 'text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]'
        : null;
@endphp

<x-ui.button
    :as="$as"
    :href="$href"
    variant="ghost"
    size="xs"
    {{ $attributes->class($classes) }}
>
    {{ $slot }}
</x-ui.button>
