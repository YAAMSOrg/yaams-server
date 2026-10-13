@extends('layouts.app')
@section('title', 'Airlines')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-buildings me-2"></i> Airlines</h1>
        <p class="text-muted mb-0">
            {{ $counts['total'] }} {{ Str::plural('airline', $counts['total']) }}
            &middot; {{ $counts['suspended'] }} suspended
            &middot; {{ $counts['orphaned'] }} without owner
        </p>
    </div>
    <a href="{{ route('admin.airlines.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create airline
    </a>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'airlines'])

    {{-- Main: airline table --}}
    <div class="col-12 col-lg-9">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="get" class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label for="q" class="form-label small text-muted mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" id="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Name, ICAO, prefix or callsign">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="status" class="form-label small text-muted mb-1">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active</option>
                            <option value="suspended" @selected($filters['status'] === 'suspended')>Suspended</option>
                            <option value="orphaned" @selected($filters['status'] === 'orphaned')>No owner</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="review" class="form-label small text-muted mb-1">PIREP review</label>
                        <select name="review" id="review" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="on" @selected($filters['review'] === 'on')>Required</option>
                            <option value="off" @selected($filters['review'] === 'off')>Off</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="sort" class="form-label small text-muted mb-1">Sort</label>
                        <select name="sort" id="sort" class="form-select form-select-sm">
                            <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                            <option value="name" @selected($filters['sort'] === 'name')>Name</option>
                            <option value="icao" @selected($filters['sort'] === 'icao')>ICAO code</option>
                            <option value="members" @selected($filters['sort'] === 'members')>Most members</option>
                            <option value="flights" @selected($filters['sort'] === 'flights')>Most flights</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Apply</button>
                        <a href="{{ route('admin.airlines.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Airline</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th class="text-end">Members</th>
                            <th class="text-end">Fleet</th>
                            <th class="text-end">Flights</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($airlines as $airline)
                            <tr>
                                <td>
                                    <span class="badge bg-dark font-monospace me-1">{{ $airline->icao_callsign }}</span>
                                    <a href="{{ route('admin.airlines.show', $airline) }}" class="fw-semibold text-decoration-none">{{ $airline->name }}</a>
                                    <div class="small text-muted">{{ $airline->atc_callsign }} &middot; {{ $airline->prefix }} &middot; {{ $airline->country }}</div>
                                </td>
                                <td class="small">
                                    @if ($airline->owner)
                                        <a href="{{ route('admin.users.show', $airline->owner) }}" class="text-decoration-none">{{ $airline->owner->name }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>@include('admin.airlines._badges')</td>
                                <td class="text-end">{{ $airline->users_count }}</td>
                                <td class="text-end">{{ $airline->aircraft_count }}</td>
                                <td class="text-end">
                                    {{ $airline->flights_count }}
                                    @if ($airline->pending_flights_count)
                                        <div class="small text-warning">{{ $airline->pending_flights_count }} pending</div>
                                    @endif
                                </td>
                                <td class="small text-muted text-nowrap" title="{{ $airline->created_at }}">
                                    {{ $airline->created_at?->diffForHumans() ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox me-1"></i> No airlines match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $airlines->links() }}
        </div>
    </div>
</div>
@endsection
