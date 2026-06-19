@props([
    'label' => null,
    'value' => null,
    'suffix' => null,
    'tone' => 'default',
])

@php
    $cardTones = [
        'default' => 'bg-[var(--color-panel)]',
        'muted' => 'bg-[var(--color-panel)]',
        'success' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-success)_6%,white),var(--color-panel))]',
        'warning' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-warning)_8%,white),var(--color-panel))]',
        'danger' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-danger)_6%,white),var(--color-panel))]',
        'info' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,#3b82f6_8%,white),var(--color-panel))]',
        'accent' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_10%,white),var(--color-panel))]',
        'soft' => 'bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-info)_8%,white),var(--color-panel))]',
    ];

    $iconTones = [
        'default' => 'bg-[var(--color-panel-soft)] text-[var(--color-muted)]',
        'muted' => 'bg-[var(--color-panel-soft)] text-[var(--color-muted)]',
        'success' => 'bg-[color-mix(in_srgb,var(--color-success)_12%,white)] text-[var(--color-success-strong)]',
        'warning' => 'bg-[color-mix(in_srgb,var(--color-warning)_16%,white)] text-[var(--color-warning-strong)]',
        'danger' => 'bg-[color-mix(in_srgb,var(--color-danger)_12%,white)] text-[var(--color-danger-strong)]',
        'info' => 'bg-[color-mix(in_srgb,#3b82f6_12%,white)] text-[#2563eb]',
        'accent' => 'bg-[color-mix(in_srgb,var(--color-accent)_12%,white)] text-[var(--color-accent-strong)]',
        'soft' => 'bg-[color-mix(in_srgb,var(--color-info)_12%,white)] text-[var(--color-info-strong)]',
    ];

    $hasIcon = trim((string) $slot) !== '';
@endphp

<x-ui.card :class="$cardTones[$tone] ?? $cardTones['default']">
    <div class="flex items-center gap-4">
        @if ($hasIcon)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[var(--radius-button)] {{ $iconTones[$tone] ?? $iconTones['default'] }}">
                {{ $slot }}
            </div>
        @endif

        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">
                {{ $value }}
                @if ($suffix)
                    <span class="text-base font-semibold text-[var(--color-muted)]">{{ $suffix }}</span>
                @endif
            </p>
        </div>
    </div>
</x-ui.card>
