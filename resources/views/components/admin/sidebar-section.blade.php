@props([
    'title' => null,
])

<div {{ $attributes->class('space-y-2') }}>
    @if ($title)
        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">{{ $title }}</p>
    @endif

    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
