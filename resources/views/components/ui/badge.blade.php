@props([
    'tone' => 'default',
])

@php
    $tones = [
        'default' => 'bg-[var(--color-panel-soft)] text-[var(--color-ink)] ring-1 ring-[var(--color-line)]',
        'success' => 'bg-[color-mix(in_srgb,var(--color-success)_12%,white)] text-[var(--color-success-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-success)_22%,white)]',
        'warning' => 'bg-[color-mix(in_srgb,var(--color-warning)_14%,white)] text-[var(--color-warning-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-warning)_20%,white)]',
        'danger' => 'bg-[color-mix(in_srgb,var(--color-danger)_12%,white)] text-[var(--color-danger-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-danger)_22%,white)]',
    ];
@endphp

<span {{ $attributes->class('inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$tones[$tone]) }}>
    {{ $slot }}
</span>
