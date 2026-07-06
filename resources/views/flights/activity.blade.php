@extends('layouts.app')
@section('title', 'YAAMS: Crew Activity')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11 col-lg-12">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="display-5 fw-bold mb-1">Crew Activity</h1>
                <p class="text-secondary mb-0 fs-6">See what your crew at <span class="fw-semibold">{{ $activeAirline->name }}</span> has been flying</p>
            </div>
            <a href="{{ route('flightadd') }}" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> File a PIREP
            </a>
        </div>

        {{-- Community stat strip --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <div class="display-6 fw-bold text-primary">{{ $crewSize }}</div>
                        <div class="text-secondary small text-uppercase">Crew Members</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <div class="display-6 fw-bold text-success">{{ $activePilots }}</div>
                        <div class="text-secondary small text-uppercase">Active <span class="text-nowrap">(30 days)</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <div class="display-6 fw-bold text-info">{{ $flightsThisWeek }}</div>
                        <div class="text-secondary small text-uppercase">Flights This Week</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <div class="display-6 fw-bold text-warning">{{ $airlineHours }}<span class="fs-5">h</span></div>
                        <div class="text-secondary small text-uppercase">Total Airline Hours</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Main column: the activity feed --}}
            <div class="col-lg-8">
                @if($feed->isEmpty())
                    <div class="card border-0 shadow-sm my-3">
                        <div class="card-body text-center p-5">
                            <i class="bi bi-airplane-engines fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                            <h2 class="h5 fw-bold mb-2">No crew activity yet</h2>
                            <p class="text-secondary mb-3">No accepted flights for {{ $activeAirline->name }} so far. Be the first to take to the skies!</p>
                            <a href="{{ route('flightadd') }}" class="btn btn-primary px-3"><i class="bi bi-plus-lg me-1"></i> File a PIREP</a>
                        </div>
                    </div>
                @else
                    @foreach($feed as $flight)
                        @php $isMine = $flight->pilot_id === auth()->id(); @endphp
                        <div class="card border-0 shadow-sm mb-3 {{ $isMine ? 'border-start border-primary border-4' : '' }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <x-pilot-avatar :name="$flight->pilot?->name ?? 'Unknown'" :size="48" />
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-semibold">{{ $flight->pilot?->name ?? 'Unknown pilot' }}</span>
                                        @if($isMine)
                                            <span class="badge bg-primary-subtle text-primary">You</span>
                                        @endif
                                        <span class="text-secondary small">&middot; {{ $flight->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-1">
                                        flew
                                        <abbr title="{{ $flight->departure_airport?->name }}" class="text-decoration-none fw-bold font-monospace">{{ $flight->departure_icao }}</abbr>
                                        <i class="bi bi-arrow-right text-secondary mx-1"></i>
                                        <abbr title="{{ $flight->arrival_airport?->name }}" class="text-decoration-none fw-bold font-monospace">{{ $flight->arrival_icao }}</abbr>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 mt-2 small text-secondary">
                                        <span class="badge bg-light text-dark border font-monospace">{{ $flight->full_flight_number }}</span>
                                        <span><i class="bi bi-airplane me-1"></i>{{ $flight->aircraft?->registration }}</span>
                                        <span><i class="bi bi-clock me-1"></i>{{ $flight->flight_duration }}</span>
                                        <span><i class="bi bi-rulers me-1"></i>{{ number_format($flight->raw_distance) }} nm</span>
                                    </div>
                                </div>
                                <a href="{{ route('viewflight', $flight) }}" class="btn btn-sm btn-outline-secondary flex-shrink-0" title="View PIREP">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @if($maxPages > 1)
                        <nav aria-label="Crew activity pages" class="mt-4">
                            <ul class="pagination mb-0 gap-1 justify-content-center">
                                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('crewactivity', ['page' => $currentPage - 1]) }}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                                </li>
                                @for($p = 1; $p <= $maxPages; $p++)
                                    <li class="page-item {{ $p == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ route('crewactivity', ['page' => $p]) }}">{{ $p }}</a>
                                    </li>
                                @endfor
                                <li class="page-item {{ $currentPage >= $maxPages ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('crewactivity', ['page' => $currentPage + 1]) }}" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                @endif
            </div>

            {{-- Sidebar: leaderboard + roster --}}
            <div class="col-lg-4">
                {{-- Leaderboard --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h2 class="h6 fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-1"></i> Top Pilots This Month</h2>
                    </div>
                    <div class="card-body pt-2">
                        @forelse($leaderboard as $index => $entry)
                            @php $topIsMine = $entry->pilot_id === auth()->id(); @endphp
                            <div class="d-flex align-items-center gap-3 py-2 rounded {{ !$loop->last ? 'border-bottom' : '' }} {{ $topIsMine ? 'bg-primary-subtle px-2' : '' }}">
                                <span class="fw-bold text-secondary text-center" style="width: 1.5rem;">{{ $index + 1 }}</span>
                                <x-pilot-avatar :name="$entry->pilot?->name ?? 'Unknown'" :size="36" />
                                <div class="flex-grow-1 fw-semibold text-truncate" style="min-width: 0;">{{ $entry->pilot?->name ?? 'Unknown' }}</div>
                                <span class="badge bg-primary rounded-pill">{{ $entry->flights_count }}</span>
                            </div>
                        @empty
                            <p class="text-secondary small mb-0">No flights logged yet this month.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Top aircraft --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h2 class="h6 fw-bold mb-0"><i class="bi bi-airplane-fill text-warning me-1"></i> Top Aircraft This Month</h2>
                    </div>
                    <div class="card-body pt-2">
                        @forelse($aircraftLeaderboard as $index => $entry)
                            <div class="d-flex align-items-center gap-3 py-2 rounded {{ !$loop->last ? 'border-bottom' : '' }}">
                                <span class="fw-bold text-secondary text-center" style="width: 1.5rem;">{{ $index + 1 }}</span>
                                <div class="flex-grow-1 fw-semibold text-truncate font-monospace" style="min-width: 0;">{{ $entry->aircraft?->registration ?? 'Unknown' }}</div>
                                <span class="badge bg-primary rounded-pill">{{ $entry->flights_count }}</span>
                            </div>
                        @empty
                            <p class="text-secondary small mb-0">No flights logged yet this month.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Crew roster --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h2 class="h6 fw-bold mb-0"><i class="bi bi-people-fill text-primary me-1"></i> Crew Roster</h2>
                    </div>
                    <div class="card-body pt-2">
                        @foreach($roster as $member)
                            <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <x-pilot-avatar :name="$member['user']->name" :size="36" />
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="fw-semibold text-truncate">
                                        {{ $member['user']->name }}
                                        @if($member['user']->id === auth()->id())
                                            <span class="badge bg-primary-subtle text-primary ms-1">You</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $member['role'] }}</span>
                                </div>
                                <div class="text-end small flex-shrink-0">
                                    <div class="fw-bold">{{ $member['flights'] }} <span class="fw-normal text-secondary">flts</span></div>
                                    <div class="text-secondary">{{ $member['hours'] }}h</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
