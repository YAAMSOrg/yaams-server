{{-- Status badges for a user row / header. Expects $user (roles loaded). --}}
@if ($user->isSuspended())
    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-slash-circle me-1"></i>Suspended</span>
@endif
@if (! $user->email_verified_at)
    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-envelope-exclamation me-1"></i>Unverified</span>
@endif
@if ($user->hasRole('Super-Admin'))
    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-shield-lock me-1"></i>Super-Admin</span>
@endif
@if ($user->hasTwoFactorEnabled())
    <span class="badge bg-success-subtle text-success"><i class="bi bi-shield-check me-1"></i>2FA</span>
@endif
