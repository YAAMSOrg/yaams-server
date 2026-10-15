@extends('layouts.app')
@section('title', 'Pilot Dashboard')

@section('content')
@php
    $airline = session('activeairline');
    $lastFlight = $my_flights->first();
    $zulu = fn ($datetime) => \Illuminate\Support\Carbon::parse($datetime)->format('d M Y, H:i').'Z';
@endphp
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i>
                <span>Pilot Profile</span>
            </div>
            <div class="card-body">
                <div>
                    <h3 class="h4 mb-1 text-dark text-break">{{ Auth::user()->name }}</h3>
                    <p class="text-muted small mb-4">Member since: {{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</p>
                    
                    <hr class="text-black-50 opacity-25">

                    <div class="mb-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold" style="font-size: 0.75rem;">Current Airline</span>
                        <span class="fw-bold text-dark text-break">{{ $airline->name }}</span>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle font-monospace ms-1">{{ $airline->icao_callsign }}</span>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <span class="d-block display-6 fw-bold text-primary">{{ number_format($flight_count) }}</span>
                                <span class="text-muted small">Accepted flights</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <span class="d-block display-6 fw-bold text-success" style="font-variant-numeric: tabular-nums;" title="{{ number_format($flight_minutes) }} minutes">{{ number_format(intdiv($flight_minutes, 60)) }}:{{ sprintf('%02d', $flight_minutes % 60) }}</span>
                                <span class="text-muted small">Block hours</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($lastFlight)
                    <div class="mt-4 pt-3 border-top border-light">
                        <span class="text-muted d-block small text-uppercase fw-semibold mb-2" style="font-size: 0.75rem;">Last Duty</span>
                        <a href="{{ route('viewflight', $lastFlight->id) }}" class="d-block bg-light p-2 rounded small text-decoration-none text-body">
                            <span class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="font-monospace fw-bold">{{ $lastFlight->full_icao_callsign }}</span>
                                <span class="font-monospace fw-semibold text-nowrap">
                                    {{ $lastFlight->departure_icao }}
                                    <i class="bi bi-arrow-right text-muted mx-1" aria-hidden="true"></i><span class="visually-hidden">to</span>
                                    {{ $lastFlight->arrival_icao }}
                                </span>
                            </span>
                            <span class="d-flex justify-content-between align-items-center gap-2 flex-wrap mt-1">
                                <span class="text-muted">{{ $zulu($lastFlight->blockon) }}</span>
                                <x-flight-status :flight="$lastFlight" />
                            </span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        @if(is_null(Auth::user()->email_verified_at))
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Verify your Email</h6>
                    <p class="small mb-2 text-secondary">Please verify your email address to unlock all functions of {{ $instanceName }}.</p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="small mb-0 text-success-emphasis fw-semibold" role="status">A new verification link has been sent to {{ Auth::user()->email }}.</p>
                    @else
                        <form method="POST" action="{{ route('verification.send') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Resend verification email</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="bi bi-shield-slash-fill fs-4 text-danger"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">An error occurred</h6>
                    <p class="small mb-0 text-secondary">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(isset($notams) && $notams->isNotEmpty())
            <div class="card mb-4 border-primary border-opacity-25">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-megaphone text-primary"></i>
                    <span>Airline NOTAMs</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto">{{ $notams->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @foreach($notams as $notam)
                        <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <h6 class="fw-semibold mb-1">{{ $notam->title }}</h6>
                            <p class="mb-2 text-body text-break" style="white-space: pre-line;">{{ $notam->body }}</p>
                            <p class="text-muted small mb-0">
                                {{ $notam->author->name ?? '—' }} · {{ $notam->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-check text-primary" aria-hidden="true"></i>
                    <span>My Recent PIREPs</span>
                </div>
                @if($my_flights->isNotEmpty())
                    <a href="{{ route('flightlist') }}" class="small text-decoration-none">View all</a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($my_flights->isEmpty())
                    <div class="p-4 text-center">
                        <p class="text-muted mb-3">You have not filed a PIREP for {{ $airline->name }} yet.</p>
                        @if(! $airline->isSuspended())
                            @can('fileFlights', $airline)
                                <a href="{{ route('flightadd') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>File a PIREP
                                </a>
                            @endcan
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="ps-4">Callsign</th>
                                    <th class="d-none d-md-table-cell">Route</th>
                                    <th class="d-none d-md-table-cell">Block on (UTC)</th>
                                    <th class="d-none d-md-table-cell">Block time</th>
                                    <th class="pe-4 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($my_flights as $flight)
                                <tr>
                                    <td class="ps-4 text-nowrap">
                                        <a href="{{ route('viewflight', $flight->id) }}" class="font-monospace fw-semibold text-decoration-none">{{ $flight->full_icao_callsign }}</a>
                                        <span class="d-block d-md-none small text-muted">
                                            <span class="font-monospace">{{ $flight->departure_icao }}</span><span class="visually-hidden"> to </span><i class="bi bi-arrow-right mx-1" aria-hidden="true"></i><span class="font-monospace">{{ $flight->arrival_icao }}</span>
                                            · {{ $zulu($flight->blockon) }}
                                        </span>
                                    </td>
                                    <td class="font-monospace fw-semibold text-dark text-nowrap d-none d-md-table-cell">
                                        {{ $flight->departure_icao }}
                                        <i class="bi bi-arrow-right text-muted small mx-1" aria-hidden="true"></i><span class="visually-hidden">to</span>
                                        {{ $flight->arrival_icao }}
                                    </td>
                                    <td class="small text-nowrap d-none d-md-table-cell">{{ $zulu($flight->blockon) }}</td>
                                    <td class="small font-monospace text-nowrap d-none d-md-table-cell">{{ $flight->flight_duration }}</td>
                                    <td class="pe-4 text-end"><x-flight-status :flight="$flight" /></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-globe-americas text-primary"></i>
                <span>Live Flights</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5 bg-white" style="min-height: 250px;">
                <div class="text-muted mb-2">
                    <i class="bi bi-cone-striped fs-1 text-secondary opacity-50"></i>
                </div>
                <h5 class="fw-semibold text-dark mb-1">Live Map coming soon</h5>
                <p class="text-muted small mb-0">The real-time tracking system is currently under heavy maintenance.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-album text-primary"></i>
                    <span>Recent Airline Flights</span>
                </div>
                <span class="badge bg-light text-secondary-emphasis border fw-normal">Last 5 accepted</span>
            </div>
            <div class="card-body p-0">
                @if($flights->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-airplane text-secondary fs-3 d-block mb-2"></i>
                        No accepted flights yet for {{ $airline->name }}.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="ps-4">Flight</th>
                                    <th>Callsign</th>
                                    <th>Route</th>
                                    <th>Aircraft</th>
                                    <th class="pe-4">Pilot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($flights as $flight)
                                <tr>

                                    <td class="ps-4 fw-semibold text-dark">
                                        <a href="{{ route('viewflight', $flight->id) }}" class="text-decoration-none">
                                                        {{ $flight->full_flight_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">{{ $flight->full_icao_callsign }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <abbr title="{{ $flight->departure_airport?->name ?? $flight->departure_icao }}" class="text-decoration-none fw-bold text-dark font-monospace">{{ $flight->departure_icao }}</abbr>
                                            <i class="bi bi-arrow-right text-muted small" aria-hidden="true"></i><span class="visually-hidden">to</span>
                                            <abbr title="{{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}" class="text-decoration-none fw-bold text-dark font-monospace">{{ $flight->arrival_icao }}</abbr>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="fw-medium text-dark text-nowrap">{{ $flight->aircraft?->registration ?? '—' }}</span>
                                            <span class="text-muted d-block" style="font-size: 0.8rem;">{{ $flight->aircraft?->full_type }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 fw-semibold text-dark">
                                        {{ $flight->pilot_name }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection