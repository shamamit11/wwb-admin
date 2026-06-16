@props([
    'as' => 'div',
    'padded' => true,
    'padding' => 'md',
])

@php
    $paddingClasses = [
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];
@endphp

<{{ $as }} {{ $attributes->class('rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]') }}>
    <div @class([$padded ? ($paddingClasses[$padding] ?? $paddingClasses['md']) : ''])>
        {{ $slot }}
    </div>
</{{ $as }}>
