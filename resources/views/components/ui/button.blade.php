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
    $base = 'inline-flex items-center justify-center rounded-[var(--radius-button)] font-medium transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-ring)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-page)] disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-[var(--color-accent)] text-[var(--color-accent-contrast)] hover:bg-[var(--color-accent-strong)]',
        'secondary' => 'bg-[var(--color-panel)] text-[var(--color-ink)] ring-1 ring-[var(--color-line)] hover:bg-[var(--color-panel-soft)]',
        'ghost' => 'bg-transparent text-[var(--color-ink)] hover:bg-[var(--color-panel-soft)]',
        'destructive' => 'bg-[var(--color-danger)] text-white hover:bg-[var(--color-danger-strong)]',
        'outline' => 'bg-transparent text-[var(--color-ink)] ring-1 ring-[var(--color-line-strong)] hover:bg-[var(--color-panel-soft)]',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
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
