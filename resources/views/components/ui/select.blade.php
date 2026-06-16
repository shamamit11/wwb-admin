@props([
    'invalid' => false,
])

@php
    $classes = $invalid
        ? 'border-[var(--color-danger)] ring-2 ring-[color-mix(in_srgb,var(--color-danger)_20%,transparent)]'
        : 'border-[var(--color-line)] focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--color-accent)_18%,transparent)]';
@endphp

<select
    {{ $attributes->class('flex h-11 w-full rounded-[var(--radius-button)] bg-[var(--color-panel)] px-3.5 text-sm text-[var(--color-ink)] shadow-sm outline-none transition-colors disabled:cursor-not-allowed disabled:opacity-50 '.$classes) }}
>
    {{ $slot }}
</select>
