@props([
    'title' => null,
    'tone' => 'info',
])

@php
    $tones = [
        'info' => [
            'wrapper' => 'border-[color-mix(in_srgb,var(--color-accent)_18%,white)] bg-[color-mix(in_srgb,var(--color-accent)_7%,white)]',
            'icon' => 'bg-[color-mix(in_srgb,var(--color-accent)_12%,white)] text-[var(--color-accent-strong)]',
            'title' => 'text-[var(--color-ink)]',
            'body' => 'text-[var(--color-muted)]',
        ],
        'warning' => [
            'wrapper' => 'border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)]',
            'icon' => 'bg-[color-mix(in_srgb,var(--color-warning)_16%,white)] text-[var(--color-warning-strong)]',
            'title' => 'text-[var(--color-ink)]',
            'body' => 'text-[var(--color-warning-strong)]',
        ],
    ];

    $styles = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->class('flex items-start gap-3 rounded-[var(--radius-button)] border px-4 py-3 text-sm '.$styles['wrapper']) }}>
    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-[var(--radius-button)] {{ $styles['icon'] }}">
        @if ($tone === 'warning')
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M10 3.5 17 16.5H3L10 3.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                <path d="M10 7.5v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                <circle cx="10" cy="13.9" r="0.9" fill="currentColor" />
            </svg>
        @else
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="10" cy="10" r="6.5" stroke="currentColor" stroke-width="1.6" />
                <path d="M10 8.25v4.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                <circle cx="10" cy="6.1" r="0.9" fill="currentColor" />
            </svg>
        @endif
    </div>

    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold {{ $styles['title'] }}">{{ $title }}</p>
        @endif

        <div @class([
            'leading-6' => true,
            'mt-1' => $title,
            $styles['body'],
        ])>
            {{ $slot }}
        </div>
    </div>
</div>
