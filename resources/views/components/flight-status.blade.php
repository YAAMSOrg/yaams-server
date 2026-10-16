@props(['flight'])
@php
    // Keyed on the hardcoded status ids (1 pending, 2 accepted, 3 rejected):
    // the status names differ between installs, so they are never matched.
    [$label, $classes, $icon] = match ((int) $flight->status_id) {
        1 => ['Pending', 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', 'bi-hourglass-split'],
        2 => ['Accepted', 'bg-success-subtle text-success-emphasis border border-success-subtle', 'bi-check-circle-fill'],
        3 => ['Rejected', 'bg-danger-subtle text-danger-emphasis border border-danger-subtle', 'bi-x-circle-fill'],
        default => [$flight->status?->name ?? 'Unknown', 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle', 'bi-circle'],
    };
@endphp
<span {{ $attributes->merge(['class' => "badge rounded-pill $classes d-inline-flex align-items-center gap-1 fw-semibold"]) }}>
    <i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $label }}
</span>
