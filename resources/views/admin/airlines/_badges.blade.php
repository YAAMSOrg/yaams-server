{{-- Status badges for an airline row / header. Expects $airline. --}}
@if ($airline->isSuspended())
    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-snow2 me-1"></i>Suspended</span>
@endif
@if (! $airline->owner_user_id)
    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-person-exclamation me-1"></i>No owner</span>
@endif
@if ($airline->require_pirep_review)
    <span class="badge bg-info-subtle text-info"><i class="bi bi-clipboard-check me-1"></i>Review</span>
@endif
@if ($airline->location_continuity)
    <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-geo-alt me-1"></i>Continuity</span>
@endif
