@props([
    'title' => 'Nothing here yet',
    'message' => null,
])

<div {{ $attributes->class('rounded-[var(--radius-card)] border border-dashed border-[var(--color-line-strong)] bg-[var(--color-panel-soft)] p-8 text-center') }}>
    <div class="mx-auto max-w-md">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Empty State</p>
        <h3 class="mt-3 text-xl font-semibold tracking-[-0.02em] text-[var(--color-ink)]">{{ $title }}</h3>
        @if ($message)
            <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">{{ $message }}</p>
        @endif

        @if (trim($slot))
            <div class="mt-5 flex justify-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
