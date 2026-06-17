@props([
    'align' => 'right',
])

@php
    $alignment = [
        'left' => 'left-0',
        'right' => 'right-0',
    ];
@endphp

<details {{ $attributes->class('relative') }}>
    <summary class="list-none cursor-pointer">
        {{ $trigger ?? '' }}
    </summary>

    <div class="absolute z-50 mt-2 min-w-48 rounded-[0.75rem] border border-[var(--color-line)] bg-[var(--color-panel)] p-1.5 shadow-[0_20px_48px_rgba(33,27,21,0.12)] {{ $alignment[$align] }}">
        {{ $slot }}
    </div>
</details>
