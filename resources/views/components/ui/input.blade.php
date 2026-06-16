@props([
    'invalid' => false,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-11 px-3.5 text-sm',
    ];

    $classes = $invalid
        ? 'border-[var(--color-danger)] ring-2 ring-[color-mix(in_srgb,var(--color-danger)_20%,transparent)] focus:border-[var(--color-danger)] focus:ring-[color-mix(in_srgb,var(--color-danger)_18%,transparent)]'
        : 'border-[var(--color-line)] focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[color-mix(in_srgb,var(--color-accent)_18%,transparent)]';
@endphp

<input
    @if ($invalid) aria-invalid="true" @endif
    {{ $attributes->class('flex w-full rounded-[var(--radius-button)] bg-[var(--color-panel)] text-[var(--color-ink)] shadow-sm outline-none transition-colors placeholder:text-[color-mix(in_srgb,var(--color-muted)_80%,white)] disabled:cursor-not-allowed disabled:border-[var(--color-line)] disabled:bg-[color-mix(in_srgb,var(--color-panel)_88%,var(--color-page))] disabled:text-[color-mix(in_srgb,var(--color-muted)_92%,white)] '.$sizes[$size].' '.$classes) }}
>
