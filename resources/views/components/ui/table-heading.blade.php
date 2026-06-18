@props([
    'sortable' => false,
    'direction' => null,
    'href' => null,
    'align' => 'left',
])

@php
    $alignment = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];
@endphp

<th {{ $attributes->class('px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)] sm:px-5 '.$alignment[$align].' [&_button]:uppercase [&_button]:tracking-[0.18em] [&_a]:uppercase [&_a]:tracking-[0.18em]') }}>
    @if ($sortable && $href)
        <a href="{{ $href }}" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
            <span>{{ $slot }}</span>
            <span class="text-[10px] leading-none text-[var(--color-muted)]">
                @if ($direction === 'asc')
                    ↑
                @elseif ($direction === 'desc')
                    ↓
                @else
                    ↕
                @endif
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
