@props([
    'align' => 'left',
    'subdued' => false,
    'width' => null,
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
        'content-primary' => 'w-[40%]',
        'taxonomy-primary' => 'w-[40%]',
    ];
@endphp

<td data-table-cell {{ $attributes->class('align-middle leading-6 '.$alignment[$align].' '.($widths[$width] ?? '').' '.($subdued ? 'text-[var(--color-muted)]' : 'text-[var(--color-ink)]')) }}>
    {{ $slot }}
</td>
