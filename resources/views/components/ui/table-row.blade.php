@props([
    'interactive' => false,
])

<tr {{ $attributes->class('border-b border-[color-mix(in_srgb,var(--color-line)_80%,white)] last:border-b-0 '.($interactive ? 'transition-colors hover:bg-[color-mix(in_srgb,var(--color-panel-soft)_55%,white)]' : '')) }}>
    {{ $slot }}
</tr>
