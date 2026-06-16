@props([
    'score' => null,
])

@php
    $numeric = is_numeric($score) ? (int) $score : null;

    if ($numeric === null) {
        $tone = 'muted';
        $label = 'Not Scored';
    } elseif ($numeric >= 85) {
        $tone = 'success';
        $label = 'Excellent';
    } elseif ($numeric >= 70) {
        $tone = 'success';
        $label = 'Good';
    } elseif ($numeric >= 50) {
        $tone = 'warning';
        $label = 'Fair';
    } else {
        $tone = 'danger';
        $label = 'Poor';
    }
@endphp

<x-ui.badge :tone="$tone" {{ $attributes }}>
    @if ($numeric === null)
        {{ $label }}
    @else
        SEO {{ $numeric }} · {{ $label }}
    @endif
</x-ui.badge>
