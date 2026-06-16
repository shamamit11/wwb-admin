@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'for' => null,
    'required' => false,
    'optional' => false,
])

@php
    $fieldId = $for ?: 'field-'.str()->uuid();
    $hintId = $hint ? $fieldId.'-hint' : null;
    $errorId = $error ? $fieldId.'-error' : null;
@endphp

<div {{ $attributes->class('space-y-2') }}>
    @if ($label)
        <label for="{{ $fieldId }}" class="block text-sm font-semibold tracking-[-0.01em] text-[var(--color-ink)]">
            <span>{{ $label }}</span>
            @if ($required)
                <span class="ml-1 text-[var(--color-danger-strong)]">*</span>
            @elseif ($optional)
                <span class="ml-2 text-xs font-medium uppercase tracking-[0.18em] text-[var(--color-muted)]">Optional</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p id="{{ $errorId }}" class="text-sm text-[var(--color-danger-strong)]" role="alert">{{ $error }}</p>
    @elseif ($hint)
        <p id="{{ $hintId }}" class="text-sm text-[var(--color-muted)]">{{ $hint }}</p>
    @endif
</div>
