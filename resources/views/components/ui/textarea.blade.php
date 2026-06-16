@props([
    'invalid' => false,
])

@php
    $classes = $invalid
        ? 'border-[var(--color-danger)] ring-2 ring-[color-mix(in_srgb,var(--color-danger)_20%,transparent)]'
        : 'border-[var(--color-line)] focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--color-accent)_18%,transparent)]';
@endphp

<textarea
    {{ $attributes->class('flex min-h-28 w-full rounded-[var(--radius-card)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)] shadow-sm outline-none transition-colors placeholder:text-[color-mix(in_srgb,var(--color-muted)_80%,white)] disabled:cursor-not-allowed disabled:opacity-50 '.$classes) }}
>{{ $slot }}</textarea>
