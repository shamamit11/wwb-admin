@props([
    'as' => 'button',
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
])

@php
    $isLink = $as === 'a' || $href;
    $isDisabled = $attributes->has('disabled') || $loading;
    $base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius-button)] font-medium transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-ring)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-page)] disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-[var(--color-accent)] text-[var(--color-accent-contrast)] shadow-[0_12px_24px_rgba(249,115,22,0.22)] hover:bg-[var(--color-accent-strong)] hover:shadow-[0_16px_28px_rgba(200,90,13,0.24)] active:scale-[0.98]',
        'secondary' => 'bg-[var(--color-panel)] text-[var(--color-ink)] ring-1 ring-[var(--color-line)] hover:bg-[var(--color-panel-soft)]',
        'ghost' => 'bg-transparent text-[var(--color-ink)] hover:bg-[var(--color-panel-soft)]',
        'destructive' => 'bg-[var(--color-danger)] text-white hover:bg-[var(--color-danger-strong)]',
        'outline' => 'bg-transparent text-[var(--color-ink)] ring-1 ring-[var(--color-line-strong)] hover:bg-[var(--color-panel-soft)]',
    ];

    $sizes = [
        'xs' => 'h-8 px-2.5 text-xs',
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-11 px-4 text-sm',
        'lg' => 'h-12 px-5 text-sm',
    ];

    if ($isDisabled) {
        $variants = array_map(
            fn (string $classes): string => $classes.' opacity-50',
            $variants,
        );
    }

    $classes = implode(' ', [
        $base,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($isLink)
    <a
        @if (! $isDisabled) href="{{ $href }}" @endif
        @if ($isDisabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->except(['disabled'])->class($classes) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $attributes->get('type', 'button') }}"
        @if ($loading) disabled @endif
        {{ $attributes->class($classes) }}
    >
        {{ $slot }}
    </button>
@endif
