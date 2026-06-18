<div {{ $attributes->class('flex flex-col gap-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between') }}>
    <div class="flex min-w-0 flex-1 flex-col gap-3 lg:flex-row lg:items-center">
        @isset($search)
            <div class="min-w-0 flex-1 lg:max-w-md">
                {{ $search }}
            </div>
        @endisset

        @isset($filters)
            <div class="flex flex-wrap items-center gap-3">
                {{ $filters }}
            </div>
        @endisset
    </div>

    @if (isset($actions) || isset($results) || isset($secondary))
        <div class="flex flex-wrap items-center gap-3 lg:justify-end">
            @isset($results)
                <div class="shrink-0 text-sm text-[var(--color-muted)]">
                    {{ $results }}
                </div>
            @elseif (isset($secondary))
                <div class="shrink-0 text-sm text-[var(--color-muted)]">
                    {{ $secondary }}
                </div>
            @endif

            @isset($secondary)
                @if (! isset($results))
                @else
                    {{ $secondary }}
                @endif
            @endisset

            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    @endif
</div>
