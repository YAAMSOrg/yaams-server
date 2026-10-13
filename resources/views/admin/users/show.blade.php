@extends('layouts.app')
@section('title', 'User Details')

@section('content')
<x-admin.page-header icon="person-circle" title="User details"
                     subtitle="Profile, access, memberships and activity of an account."
                     :crumbs="['Users' => route('admin.users.index')]" />

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'users'])

    <div class="col-12 col-lg-9">
        <x-admin.entity-header :name="$user->name" :subtitle="$user->email">
            <x-slot:tile>
                <x-pilot-avatar :name="$user->name" size="72" />
            </x-slot:tile>
            <x-slot:badges>
                @include('admin.users._badges')
            </x-slot:badges>
            <x-slot:actions>
                <a href="{{ route('admin.activity.index', ['user' => $user->id]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-journal-text me-1"></i> Activity
                </a>
                @if ($user->isSuspended())
                    <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-play-circle me-1"></i> Reactivate</button>
                    </form>
                @else
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendModal"
                            @disabled($isSelf) @if ($isSelf) title="You cannot suspend yourself" @endif>
                        <i class="bi bi-slash-circle me-1"></i> Suspend
                    </button>
                @endif
            </x-slot:actions>
            <x-slot:facts>
                <x-admin.fact label="Registered">{{ $user->created_at?->toDateString() ?? '-' }}</x-admin.fact>
                <x-admin.fact label="Last online">{{ $user->last_online_at?->diffForHumans() ?? '-' }}</x-admin.fact>
                <x-admin.fact label="E-mail">{{ $user->email_verified_at ? 'Verified' : 'Unverified' }}</x-admin.fact>
                <x-admin.fact label="Two-factor">{{ $user->hasTwoFactorEnabled() ? 'Enabled' : 'Off' }}</x-admin.fact>
                <x-admin.fact label="Notifications">{{ $user->email_notifications ? 'In-app + e-mail' : 'In-app only' }}</x-admin.fact>
                <x-admin.fact label="API tokens">{{ $user->tokens->count() }}</x-admin.fact>
            </x-slot:facts>
        </x-admin.entity-header>

        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif
        @error('action')
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $message }}
            </div>
        @enderror

        @if ($user->isSuspended())
            <div class="alert alert-danger">
                <i class="bi bi-slash-circle me-2"></i>
                Suspended {{ $user->suspended_at->diffForHumans() }}
                <span class="text-muted small">({{ $user->suspended_at->toDateTimeString() }} UTC)</span>.
                @if ($user->suspension_reason)
                    <div class="mt-1"><span class="fw-semibold">Reason:</span> {{ $user->suspension_reason }}</div>
                @endif
            </div>
        @endif

        {{-- Key facts --}}
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $airlines->count() }}</div>
                    <div class="text-muted small">Airlines</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['flights'] }}</div>
                    <div class="text-muted small">Flights filed</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['accepted'] }}</div>
                    <div class="text-muted small">Accepted</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['pending'] }}</div>
                    <div class="text-muted small">Pending</div>
                </div></div>
            </div>
        </div>

        <div class="row g-4 mt-0">
            {{-- Profile --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-person me-2"></i> Profile</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" required maxlength="255"
                                       class="form-control @error('name', 'profile') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}">
                                @error('name', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail address</label>
                                <input type="email" name="email" id="email" required maxlength="255"
                                       class="form-control @error('email', 'profile') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}">
                                @error('email', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="email_verified" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" name="email_verified" value="1" id="email_verified"
                                       @checked(old('email_verified', $user->email_verified_at ? '1' : '0') === '1')>
                                <label class="form-check-label" for="email_verified">
                                    E-mail verified
                                    @if ($user->email_verified_at)
                                        <span class="text-muted small">({{ $user->email_verified_at->toDateString() }})</span>
                                    @endif
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Save profile</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Account actions --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-sliders me-2"></i> Account actions</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">Super-Admin</div>
                                <div class="small text-muted">Full control over this instance.</div>
                            </div>
                            <form method="POST" action="{{ route('admin.users.role', $user) }}"
                                  onsubmit="return confirm('{{ $user->hasRole('Super-Admin') ? 'Revoke' : 'Grant' }} the Super-Admin role?')">
                                @csrf
                                @method('PUT')
                                @if ($user->hasRole('Super-Admin'))
                                    <input type="hidden" name="super_admin" value="0">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap" @disabled($isSelf)>
                                        <i class="bi bi-shield-x me-1"></i> Revoke
                                    </button>
                                @else
                                    <input type="hidden" name="super_admin" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">
                                        <i class="bi bi-shield-plus me-1"></i> Grant
                                    </button>
                                @endif
                            </form>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">Two-factor authentication</div>
                                <div class="small text-muted">
                                    @if ($user->hasTwoFactorEnabled())
                                        Enabled since {{ $user->two_factor_confirmed_at->toDateString() }}.
                                    @elseif ($hasTwoFactor)
                                        Setup started but not confirmed.
                                    @else
                                        Not set up.
                                    @endif
                                </div>
                            </div>
                            @if ($hasTwoFactor)
                                <form method="POST" action="{{ route('admin.users.2fa.reset', $user) }}"
                                      onsubmit="return confirm('Remove two-factor authentication from this account? The user will be informed by e-mail.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-warning text-nowrap"><i class="bi bi-shield-slash me-1"></i> Reset 2FA</button>
                                </form>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">Password</div>
                                <div class="small text-muted">E-mail the user a link to set a new password.</div>
                            </div>
                            <form method="POST" action="{{ route('admin.users.password-reset', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-envelope me-1"></i> Send reset link</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Airline memberships --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-buildings me-2"></i> Airline memberships</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Airline</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($airlines as $airline)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.airlines.show', $airline) }}" class="text-decoration-none">{{ $airline->name }}</a>
                                            <span class="text-muted small">{{ $airline->icao_callsign }}</span>
                                        </td>
                                        <td>
                                            {{ $airline->pivot->role }}
                                            @if ($user->isOwnerOf($airline))
                                                <span class="badge bg-info-subtle text-info ms-1">Owner</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Not a member of any airline.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- API tokens --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-key me-2"></i> API tokens</span>
                        @if ($user->tokens->isNotEmpty())
                            <form method="POST" action="{{ route('admin.users.tokens.revoke', $user) }}"
                                  onsubmit="return confirm('Revoke all API tokens of this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i> Revoke all</button>
                            </form>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Last used</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($user->tokens as $token)
                                    <tr>
                                        <td>{{ $token->name }}</td>
                                        <td class="small text-muted">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                        <td class="small text-muted">{{ $token->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No API tokens.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i> Recent activity</span>
                <a href="{{ route('admin.activity.index', ['user' => $user->id]) }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>By</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="text-nowrap small text-muted" title="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</td>
                                <td class="small">{{ $activity->causer?->name ?? '-' }}</td>
                                <td>
                                    {{ $activity->description }}
                                    @if ($activity->event)
                                        <span class="badge bg-light text-muted border ms-1">{{ $activity->event }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No activity recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="card border-danger mt-4">
            <div class="card-header bg-danger-subtle text-danger fw-semibold">
                <i class="bi bi-exclamation-triangle me-2"></i> Danger zone
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-1">Delete account</h6>
                <p class="text-muted mb-3">
                    Permanently deletes the account. Filed flights stay on record but are anonymized.
                </p>
                @if ($isSelf)
                    <p class="small text-muted mb-2">Delete your own account from your <a href="{{ route('settings.danger') }}">account settings</a>.</p>
                @elseif ($blockingAirlines->isNotEmpty())
                    <p class="small text-danger mb-2">
                        Owns or is the only manager of
                        @foreach ($blockingAirlines as $blocking)<a href="{{ route('admin.airlines.show', $blocking) }}">{{ $blocking->name }}</a>{{ $loop->last ? '' : ', ' }}@endforeach.
                        Ownership must be transferred or another manager appointed first.
                    </p>
                @endif
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                        @disabled($isSelf || $blockingAirlines->isNotEmpty())>
                    <i class="bi bi-trash me-2"></i> Delete account
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Suspend confirmation --}}
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="suspendModalLabel"><i class="bi bi-slash-circle text-danger me-2"></i> Suspend {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        The user is signed out on their next request and can no longer log in or use
                        their API tokens. Nothing is deleted, and you can reactivate the account at any time.
                    </p>
                    <label for="reason" class="form-label">Reason <span class="text-muted small">(optional, internal)</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-slash-circle me-2"></i> Suspend</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirmation --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel"><i class="bi bi-exclamation-triangle text-danger me-2"></i> Delete account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        This permanently deletes the account of {{ $user->name }} and cannot be undone.
                        Type <code>{{ $user->email }}</code> to confirm.
                    </p>
                    <input type="text" name="confirm_email" autocomplete="off" required
                           class="form-control @error('confirm_email', 'deleteUser') is-invalid @enderror">
                    @error('confirm_email', 'deleteUser')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i> Delete account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->deleteUser->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    });
</script>
@endif
@endpush
