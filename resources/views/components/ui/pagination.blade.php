@props([
    'paginator' => null,
])

@if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <nav {{ $attributes->class('flex flex-col gap-3 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between') }} aria-label="Pagination">
        <div class="text-sm text-[var(--color-muted)]">
            Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
        </div>

        <div class="flex items-center gap-2">
            <x-ui.button
                as="a"
                :href="$paginator->previousPageUrl()"
                variant="secondary"
                size="sm"
                :disabled="$paginator->onFirstPage()"
            >
                Previous
            </x-ui.button>

            <span class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm text-[var(--color-muted)]">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            <x-ui.button
                as="a"
                :href="$paginator->nextPageUrl()"
                variant="secondary"
                size="sm"
                :disabled="! $paginator->hasMorePages()"
            >
                Next
            </x-ui.button>
        </div>
    </nav>
@endif
