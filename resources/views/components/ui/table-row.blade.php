@props([
    'interactive' => false,
])

<tr {{ $attributes->class($interactive ? 'transition-colors hover:bg-[color-mix(in_srgb,var(--color-panel-soft)_55%,white)]' : '') }}>
    {{ $slot }}
</tr>
