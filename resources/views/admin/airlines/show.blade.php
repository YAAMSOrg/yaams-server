@extends('layouts.app')
@section('title', $airline->name)

@section('content')
<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'airlines'])

    <div class="col-12 col-lg-9">
        <div class="card yaams-airline-header mb-4">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
                <div class="yaams-airline-header__tile" aria-hidden="true">
                    <span class="font-monospace">{{ $airline->icao_callsign }}</span>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <a href="{{ route('admin.airlines.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> All airlines</a>
                    <h1 class="h3 mb-1 text-break">{{ $airline->name }}</h1>
                    <div class="d-flex flex-wrap gap-1">
                        @include('admin.airlines._badges')
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.activity.index', ['airline' => $airline->id]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-journal-text me-1"></i> Activity
                    </a>
                    @if ($airline->isSuspended())
                        <form method="POST" action="{{ route('admin.airlines.reactivate', $airline) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-play-circle me-1"></i> Reactivate</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-snow2 me-1"></i> Suspend
                        </button>
                    @endif
                </div>
            </div>
            <dl class="yaams-airline-header__facts row row-cols-2 row-cols-md-3 row-cols-xl-6 g-0 mb-0">
                <div class="col">
                    <dt>ATC callsign</dt>
                    <dd>{{ $airline->atc_callsign }}</dd>
                </div>
                <div class="col">
                    <dt>IATA prefix</dt>
                    <dd class="font-monospace">{{ $airline->prefix }}</dd>
                </div>
                <div class="col">
                    <dt>Hub</dt>
                    <dd class="font-monospace">{{ $airline->hub ?? '-' }}</dd>
                </div>
                <div class="col">
                    <dt>Country</dt>
                    <dd>{{ $airline->country }}</dd>
                </div>
                <div class="col">
                    <dt>Founded</dt>
                    <dd>{{ $airline->founded_at?->toDateString() ?? '-' }}</dd>
                </div>
                <div class="col">
                    <dt>Owner</dt>
                    <dd class="text-truncate">
                        @if ($airline->owner)
                            <a href="{{ route('admin.users.show', $airline->owner) }}" class="text-decoration-none">{{ $airline->owner->name }}</a>
                        @else
                            <span class="text-warning-emphasis">None</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

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

        @if ($airline->isSuspended())
            <div class="alert alert-danger">
                <i class="bi bi-snow2 me-2"></i>
                Suspended {{ $airline->suspended_at->diffForHumans() }}
                <span class="text-muted small">({{ $airline->suspended_at->toDateTimeString() }} UTC)</span>.
                Members can view the airline, but every write is refused.
                @if ($airline->suspension_reason)
                    <div class="mt-1"><span class="fw-semibold">Reason:</span> {{ $airline->suspension_reason }}</div>
                @endif
            </div>
        @endif

        @if (! $airline->owner_user_id)
            <div class="alert alert-warning">
                <i class="bi bi-person-exclamation me-2"></i>
                This airline has no owner (the owner's account was deleted). Assign a new owner below.
            </div>
        @endif

        {{-- Key facts --}}
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $members->count() }}</div>
                    <div class="text-muted small">Members</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['accepted'] }}</div>
                    <div class="text-muted small">Accepted flights</div>
                    <div class="small text-muted">{{ $stats['pending'] }} pending &middot; {{ $stats['rejected'] }} rejected</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['aircraftActive'] }}</div>
                    <div class="text-muted small">Active aircraft</div>
                    <div class="small text-muted">{{ $stats['aircraftInactive'] }} inactive &middot; {{ $stats['aircraftRetired'] }} retired</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body text-center">
                    <div class="fs-3 fw-bold">{{ $stats['notams'] }}</div>
                    <div class="text-muted small">Active NOTAMs</div>
                    <div class="small text-muted">{{ $stats['inviteCodes'] }} open invites &middot; {{ $stats['pendingImages'] }} shots pending</div>
                </div></div>
            </div>
        </div>

        {{-- Profile + settings --}}
        <div class="card mt-4">
            <div class="card-header"><i class="bi bi-building-gear me-2"></i> Profile &amp; settings</div>
            <form method="POST" action="{{ route('admin.airlines.update', $airline) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @include('admin.airlines._form', ['airline' => $airline, 'bag' => 'profile'])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Save airline</button>
                </div>
            </form>
        </div>

        {{-- Members --}}
        <div class="card mt-4">
            <div class="card-header"><i class="bi bi-people me-2"></i> Members</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            @php($isOwner = $airline->isOwnedBy($member))
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $member) }}" class="fw-semibold text-decoration-none">{{ $member->name }}</a>
                                    @if ($isOwner)
                                        <span class="badge bg-info-subtle text-info ms-1">Owner</span>
                                    @endif
                                    @if ($member->isSuspended())
                                        <span class="badge bg-danger-subtle text-danger ms-1">Suspended</span>
                                    @endif
                                    <div class="small text-muted">{{ $member->email }}</div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.airlines.members.update', [$airline, $member]) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-select form-select-sm w-auto" @disabled($isOwner) aria-label="Role of {{ $member->name }}">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}" @selected($member->pivot->role === $role)>{{ $role }}</option>
                                            @endforeach
                                        </select>
                                        @unless ($isOwner)
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Save role"><i class="bi bi-check-lg"></i></button>
                                        @endunless
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @unless ($isOwner)
                                            <form method="POST" action="{{ route('admin.airlines.owner', $airline) }}"
                                                  onsubmit="return confirm(@js('Make '.$member->name.' the owner of '.$airline->name.'?'))">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap" title="Make owner">
                                                    <i class="bi bi-key"></i> <span class="d-none d-xl-inline">Make owner</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.airlines.members.destroy', [$airline, $member]) }}"
                                                  onsubmit="return confirm(@js('Remove '.$member->name.' from '.$airline->name.'?'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove member"><i class="bi bi-person-dash"></i></button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No members.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <form method="POST" action="{{ route('admin.airlines.members.store', $airline) }}" class="row g-2 align-items-start">
                    @csrf
                    <div class="col-12 col-md-6">
                        <input type="email" name="email" required maxlength="255" placeholder="User e-mail address" aria-label="User e-mail address"
                               class="form-control form-control-sm @error('email', 'addMember') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email', 'addMember')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="role" class="form-select form-select-sm" aria-label="Role">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', 'Pilot') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-person-plus me-1"></i> Add member</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i> Recent activity</span>
                <a href="{{ route('admin.activity.index', ['airline' => $airline->id]) }}" class="btn btn-sm btn-outline-primary">View all</a>
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
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">Delete airline</div>
                        <div class="small text-muted">Permanently deletes the airline with all its flights, fleet, screenshots, memberships, invite codes and NOTAMs.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#deleteAirlineModal">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- Suspend confirmation --}}
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.airlines.suspend', $airline) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="suspendModalLabel"><i class="bi bi-snow2 text-danger me-2"></i> Suspend {{ $airline->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        The airline becomes read-only for all members (web and API) and is hidden from the
                        landing page. Nothing is deleted, and you can reactivate it at any time.
                    </p>
                    <label for="reason" class="form-label">Reason <span class="text-muted small">(optional, shown to members)</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-snow2 me-2"></i> Suspend</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirmation --}}
<div class="modal fade" id="deleteAirlineModal" tabindex="-1" aria-labelledby="deleteAirlineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.airlines.destroy', $airline) }}">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAirlineModalLabel"><i class="bi bi-exclamation-triangle text-danger me-2"></i> Delete airline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        This permanently deletes {{ $airline->name }} including {{ $stats['accepted'] + $stats['pending'] + $stats['rejected'] }} flights,
                        {{ $stats['aircraftActive'] + $stats['aircraftInactive'] + $stats['aircraftRetired'] }} aircraft and {{ $members->count() }} memberships.
                        This cannot be undone. Type <code>{{ $airline->icao_callsign }}</code> to confirm.
                    </p>
                    <input type="text" name="confirm_icao" autocomplete="off" required
                           class="form-control font-monospace @error('confirm_icao', 'deleteAirline') is-invalid @enderror">
                    @error('confirm_icao', 'deleteAirline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i> Delete airline</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->deleteAirline->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('deleteAirlineModal')).show();
    });
</script>
@endif
@endpush
