@props([
    'active' => false,
    'href' => null,
])

@php
    $classes = $active
        ? 'border-[var(--color-accent)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_84%,white)_0%,var(--color-accent)_100%)] text-[var(--color-accent-contrast)] shadow-[0_10px_18px_rgba(249,115,22,0.24),inset_0_1px_0_rgba(255,255,255,0.28)]'
        : 'border-transparent bg-transparent text-[color-mix(in_srgb,var(--color-muted)_84%,var(--color-ink))] hover:bg-[var(--color-panel)] hover:text-[var(--color-ink)]';
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        role="tab"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        {{ $attributes->class('inline-flex min-h-10 items-center justify-center rounded-full border px-4 py-2 text-sm font-semibold tracking-[-0.01em] transition-[color,background-color,border-color,box-shadow,transform] duration-200 hover:-translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color-mix(in_srgb,var(--color-accent)_38%,white)] focus-visible:ring-offset-2 focus-visible:ring-offset-[color-mix(in_srgb,var(--color-panel-soft)_70%,white)] '.$classes) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="button"
        role="tab"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        {{ $attributes->class('inline-flex min-h-10 items-center justify-center rounded-full border px-4 py-2 text-sm font-semibold tracking-[-0.01em] transition-[color,background-color,border-color,box-shadow,transform] duration-200 hover:-translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color-mix(in_srgb,var(--color-accent)_38%,white)] focus-visible:ring-offset-2 focus-visible:ring-offset-[color-mix(in_srgb,var(--color-panel-soft)_70%,white)] '.$classes) }}
    >
        {{ $slot }}
    </button>
@endif
