@props([
    'active' => false,
    'href' => null,
])

@php
    $classes = $active
        ? 'bg-[var(--color-accent-soft)] text-[var(--color-ink)]'
        : 'text-[var(--color-muted)] hover:bg-[var(--color-panel-soft)] hover:text-[var(--color-ink)]';
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        role="tab"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        {{ $attributes->class('rounded-[calc(var(--radius-button)-0.05rem)] px-3 py-2 text-sm font-medium transition-colors '.$classes) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="button"
        role="tab"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        {{ $attributes->class('rounded-[calc(var(--radius-button)-0.05rem)] px-3 py-2 text-sm font-medium transition-colors '.$classes) }}
    >
        {{ $slot }}
    </button>
@endif
