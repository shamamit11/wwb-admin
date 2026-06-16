@props([
    'caption' => null,
])

<div {{ $attributes->class('overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm text-[var(--color-ink)]">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif

            {{ $slot }}
        </table>
    </div>
</div>
