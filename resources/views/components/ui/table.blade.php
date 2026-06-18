@props([
    'caption' => null,
    'density' => 'comfortable',
])

@php
    $densities = [
        'comfortable' => '[&_[data-table-heading]]:px-5 [&_[data-table-heading]]:py-3.5 sm:[&_[data-table-heading]]:px-6 [&_[data-table-cell]]:px-5 [&_[data-table-cell]]:py-4.5 sm:[&_[data-table-cell]]:px-6',
        'compact' => '[&_[data-table-heading]]:px-4 [&_[data-table-heading]]:py-3 sm:[&_[data-table-heading]]:px-5 [&_[data-table-cell]]:px-4 [&_[data-table-cell]]:py-4 sm:[&_[data-table-cell]]:px-5',
    ];
@endphp

<div {{ $attributes->class('rounded-[0.75rem] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]') }}>
    <div class="overflow-x-auto rounded-[inherit]">
        <table class="min-w-full border-collapse text-left text-sm text-[var(--color-ink)] {{ $densities[$density] ?? $densities['comfortable'] }}">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif

            {{ $slot }}
        </table>
    </div>
</div>
