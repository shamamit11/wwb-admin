@props([
    'align' => 'left',
    'subdued' => false,
])

@php
    $alignment = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];
@endphp

<td {{ $attributes->class('px-4 py-4 align-middle sm:px-5 '.$alignment[$align].' '.($subdued ? 'text-[var(--color-muted)]' : 'text-[var(--color-ink)]')) }}>
    {{ $slot }}
</td>
