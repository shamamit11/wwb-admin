@props([
    'padded' => true,
])

<div {{ $attributes->class('rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]') }}>
    <div @class([$padded ? 'p-6' : ''])>
        {{ $slot }}
    </div>
</div>
