@props([
    'status' => null,
])

@php
    $normalized = str($status)->lower()->replace([' ', '-'], '_')->value();

    $map = [
        'suggested' => ['label' => 'Suggested', 'tone' => 'warning'],
        'approved' => ['label' => 'Approved', 'tone' => 'success'],
        'rejected' => ['label' => 'Rejected', 'tone' => 'danger'],
        'used' => ['label' => 'Used', 'tone' => 'default'],
        'draft' => ['label' => 'Draft', 'tone' => 'muted'],
        'scheduled' => ['label' => 'Scheduled', 'tone' => 'warning'],
        'published' => ['label' => 'Published', 'tone' => 'success'],
        'unpublished' => ['label' => 'Unpublished', 'tone' => 'default'],
        'archived' => ['label' => 'Archived', 'tone' => 'danger'],
        'active' => ['label' => 'Active', 'tone' => 'success'],
        'inactive' => ['label' => 'Inactive', 'tone' => 'muted'],
        'pending' => ['label' => 'Pending', 'tone' => 'muted'],
        'queued' => ['label' => 'Queued', 'tone' => 'warning'],
        'processing' => ['label' => 'Processing', 'tone' => 'warning'],
        'completed' => ['label' => 'Completed', 'tone' => 'success'],
        'cancelled' => ['label' => 'Cancelled', 'tone' => 'muted'],
        'reviewed' => ['label' => 'Reviewed', 'tone' => 'default'],
        'failed' => ['label' => 'Failed', 'tone' => 'danger'],
        'review_needed' => ['label' => 'Review Needed', 'tone' => 'warning'],
        'discovered' => ['label' => 'Discovered', 'tone' => 'warning'],
        'screened' => ['label' => 'Screened', 'tone' => 'default'],
        'extracted' => ['label' => 'Extracted', 'tone' => 'success'],
        'routed' => ['label' => 'Routed', 'tone' => 'success'],
        'ignored' => ['label' => 'Ignored', 'tone' => 'muted'],
    ];

    $resolved = $map[$normalized] ?? ['label' => str($status)->headline(), 'tone' => 'default'];
@endphp

<x-ui.badge :tone="$resolved['tone']" {{ $attributes }}>
    {{ $resolved['label'] }}
</x-ui.badge>
