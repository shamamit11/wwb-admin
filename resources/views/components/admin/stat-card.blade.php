@props([
    'label' => null,
    'value' => null,
    'tone' => 'default',
])

@php
    $tones = [
        'default' => 'bg-[var(--color-panel)]',
        'accent' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_9%,white),var(--color-panel))]',
        'soft' => 'bg-[var(--color-panel-soft)]',
    ];
@endphp

<x-ui.card :class="$tones[$tone] ?? $tones['default']">
    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $label }}</p>
    <div class="mt-4 flex items-end justify-between gap-4">
        <p class="text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $value }}</p>
        {{ $slot }}
    </div>
</x-ui.card>
