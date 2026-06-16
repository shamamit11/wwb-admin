@props([
    'href' => null,
    'destructive' => false,
])

@php
    $classes = 'flex w-full items-center rounded-[calc(var(--radius-button)-0.1rem)] px-3 py-2 text-sm transition-colors hover:bg-[var(--color-panel-soft)] '.($destructive ? 'text-[var(--color-danger-strong)]' : 'text-[var(--color-ink)]');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
