@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between') }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $eyebrow }}</p>
        @endif

        @if ($title)
            <h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)] sm:text-4xl">{{ $title }}</h1>
        @endif

        @if ($description)
            <p class="mt-3 text-sm leading-7 text-[var(--color-muted)] sm:text-base">{{ $description }}</p>
        @endif
    </div>

    @if (trim($slot))
        <div class="flex flex-wrap items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
