@props([
    'open' => false,
    'title' => null,
    'description' => null,
    'maxWidth' => 'md',
    'tone' => 'default',
])

@php
    $widths = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
    ];

    $tones = [
        'default' => 'border-[var(--color-line)]',
        'destructive' => 'border-[color-mix(in_srgb,var(--color-danger)_24%,white)]',
    ];
@endphp

@if ($open)
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-[rgba(23,20,16,0.42)] px-4 py-6" role="presentation">
        <div
            {{ $attributes->class('w-full rounded-[var(--radius-card)] border bg-[var(--color-panel)] shadow-[0_28px_80px_rgba(33,27,21,0.18)] '.$widths[$maxWidth].' '.$tones[$tone]) }}
            role="dialog"
            aria-modal="true"
            @if ($title) aria-labelledby="dialog-title" @endif
            @if ($description) aria-describedby="dialog-description" @endif
        >
            <div class="border-b border-[var(--color-line)] px-6 py-5">
                @if ($title)
                    <h2 id="dialog-title" class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p id="dialog-description" class="mt-2 text-sm leading-6 text-[var(--color-muted)]">{{ $description }}</p>
                @endif
            </div>

            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-[var(--color-line)] px-6 py-4">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
@endif
