@props([
    'status' => null,
])

@php
    $normalized = str($status)->lower()->replace([' ', '-'], '_')->value();

    $map = [
        'draft' => ['label' => 'Draft', 'tone' => 'muted'],
        'scheduled' => ['label' => 'Scheduled', 'tone' => 'warning'],
        'published' => ['label' => 'Published', 'tone' => 'success'],
        'unpublished' => ['label' => 'Unpublished', 'tone' => 'default'],
        'archived' => ['label' => 'Archived', 'tone' => 'danger'],
        'active' => ['label' => 'Active', 'tone' => 'success'],
        'inactive' => ['label' => 'Inactive', 'tone' => 'muted'],
        'processing' => ['label' => 'Processing', 'tone' => 'warning'],
        'failed' => ['label' => 'Failed', 'tone' => 'danger'],
        'review_needed' => ['label' => 'Review Needed', 'tone' => 'warning'],
    ];

    $resolved = $map[$normalized] ?? ['label' => str($status)->headline(), 'tone' => 'default'];
@endphp

<x-ui.badge :tone="$resolved['tone']" {{ $attributes }}>
    {{ $resolved['label'] }}
</x-ui.badge>
