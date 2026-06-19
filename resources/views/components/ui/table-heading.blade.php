@props([
    'sortable' => false,
    'direction' => null,
    'href' => null,
    'align' => 'left',
    'width' => null,
    'sortKey' => null,
    'sortColumn' => null,
    'sortDirection' => null,
    'sortState' => null,
    'sortMethod' => 'sortBy',
])

@php
    $alignment = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $widths = [
        'asset-preview' => 'w-[12%]',
        'workflow-primary' => 'w-[24%]',
        'feed-primary' => 'w-[28%]',
        'content-primary' => 'w-[34%]',
        'taxonomy-primary' => 'w-[40%]',
    ];

    $resolvedDirection = $direction;
    $wireClick = null;

    if ($sortable && $sortKey) {
        if ($resolvedDirection === null && $sortColumn !== null && $sortColumn === $sortKey) {
            $resolvedDirection = $sortDirection;
        }

        if ($sortState !== null) {
            $normalizedSortState = ltrim((string) $sortState, '-');

            if ($resolvedDirection === null && $normalizedSortState === $sortKey) {
                $resolvedDirection = str_starts_with((string) $sortState, '-') ? 'desc' : 'asc';
            }

            $nextSortState = $normalizedSortState === $sortKey && ! str_starts_with((string) $sortState, '-')
                ? '-'.$sortKey
                : $sortKey;

            $wireClick = $sortMethod."('".$nextSortState."')";
        } else {
            $wireClick = $sortMethod."('".$sortKey."')";
        }
    }

    $ariaSort = match ($resolvedDirection) {
        'asc' => 'ascending',
        'desc' => 'descending',
        default => 'none',
    };
@endphp

<th
    data-table-heading
    {{ $attributes->class('text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)] '.$alignment[$align].' '.($widths[$width] ?? '').' [&_button]:uppercase [&_button]:tracking-[0.18em] [&_a]:uppercase [&_a]:tracking-[0.18em]') }}
    aria-sort="{{ $ariaSort }}"
>
    @if ($sortable && $href)
        <a href="{{ $href }}" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
            <span>{{ $slot }}</span>
            <span class="text-[10px] leading-none text-[var(--color-muted)]">
                @if ($resolvedDirection === 'asc')
                    ↑
                @elseif ($resolvedDirection === 'desc')
                    ↓
                @else
                    ↕
                @endif
            </span>
        </a>
    @elseif ($sortable && $wireClick)
        <button type="button" wire:click="{{ $wireClick }}" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
            <span>{{ $slot }}</span>
            <span class="text-[10px] leading-none text-[var(--color-muted)]">
                @if ($resolvedDirection === 'asc')
                    ↑
                @elseif ($resolvedDirection === 'desc')
                    ↓
                @else
                    ↕
                @endif
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
