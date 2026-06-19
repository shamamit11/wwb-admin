@props([
    'paginator' => null,
    'pagination' => null,
    'itemLabel' => 'result',
    'previousAction' => 'previousPage',
    'nextAction' => 'nextPage',
])

@php
    $usesLaravelPaginator = $paginator && method_exists($paginator, 'hasPages');
    $usesArrayPagination = is_array($pagination) && ($pagination['has_pages'] ?? false);

    if ($usesLaravelPaginator && $paginator->hasPages()) {
        $firstItem = $paginator->firstItem() ?? 0;
        $lastItem = $paginator->lastItem() ?? 0;
        $total = $paginator->total();
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $hasPrevious = ! $paginator->onFirstPage();
        $hasNext = $paginator->hasMorePages();
        $previousUrl = $paginator->previousPageUrl();
        $nextUrl = $paginator->nextPageUrl();
    } elseif ($usesArrayPagination) {
        $firstItem = $pagination['first_item'] ?? 0;
        $lastItem = $pagination['last_item'] ?? 0;
        $total = $pagination['total'] ?? 0;
        $currentPage = $pagination['page'] ?? 1;
        $lastPage = $pagination['last_page'] ?? 1;
        $hasPrevious = $currentPage > 1;
        $hasNext = $currentPage < $lastPage;
        $previousUrl = null;
        $nextUrl = null;
    }

    $hasPages = ($usesLaravelPaginator && $paginator->hasPages()) || $usesArrayPagination;
    $resultsLabel = str($itemLabel)->plural($total ?? 0);
@endphp

@if ($hasPages)
    <nav {{ $attributes->class('flex flex-col gap-3 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between') }} aria-label="Pagination">
        <div class="text-sm text-[var(--color-muted)]">
            Showing {{ $firstItem }}-{{ $lastItem }} of {{ $total }} {{ $resultsLabel }}
        </div>

        <div class="flex items-center gap-2">
            @if ($usesLaravelPaginator)
                <x-ui.button
                    as="a"
                    :href="$previousUrl"
                    variant="secondary"
                    size="sm"
                    :disabled="! $hasPrevious"
                >
                    Previous
                </x-ui.button>
            @else
                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    wire:click="{{ $previousAction }}"
                    :disabled="! $hasPrevious"
                >
                    Previous
                </x-ui.button>
            @endif

            <span class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm text-[var(--color-muted)]">
                Page {{ $currentPage }} of {{ $lastPage }}
            </span>

            @if ($usesLaravelPaginator)
                <x-ui.button
                    as="a"
                    :href="$nextUrl"
                    variant="secondary"
                    size="sm"
                    :disabled="! $hasNext"
                >
                    Next
                </x-ui.button>
            @else
                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    wire:click="{{ $nextAction }}"
                    :disabled="! $hasNext"
                >
                    Next
                </x-ui.button>
            @endif
        </div>
    </nav>
@endif
