@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'for' => null,
])

<div {{ $attributes->class('space-y-2') }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="block text-sm font-semibold tracking-[-0.01em] text-[var(--color-ink)]">
            {{ $label }}
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="text-sm text-[var(--color-danger-strong)]">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-[var(--color-muted)]">{{ $hint }}</p>
    @endif
</div>
