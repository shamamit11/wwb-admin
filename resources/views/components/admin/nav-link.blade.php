@props([
    'active' => false,
    'disabled' => false,
    'placeholder' => false,
])

@php
    $classes = $active
        ? 'bg-[var(--color-accent-soft)] text-[var(--color-ink)] ring-1 ring-[var(--color-line)]'
        : 'text-[var(--color-muted)] hover:bg-[var(--color-panel-soft)] hover:text-[var(--color-ink)]';

    if ($disabled) {
        $classes = 'cursor-not-allowed text-[var(--color-muted)] opacity-60';
    }
@endphp

<a
    {{ $attributes->class('flex items-center justify-between rounded-[var(--radius-button)] px-3 py-2.5 text-sm font-medium transition-colors '.$classes) }}
    @if ($disabled) aria-disabled="true" @endif
>
    <span>{{ $slot }}</span>
    @if ($placeholder || $disabled)
        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]">{{ $disabled ? 'Soon' : 'Placeholder' }}</span>
    @endif
</a>
