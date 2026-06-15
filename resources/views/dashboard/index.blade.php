@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i>
                <span>Pilot Profile</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h3 class="h4 mb-1 text-dark">{{ Auth::user()->name }}</h3>
                    <p class="text-muted small mb-4">Member since: {{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</p>
                    
                    <hr class="text-black-50 opacity-25">

                    <div class="mb-3">
                        <span class="text-muted d-block small uppercase tracking-wider text-uppercase fw-semibold" style="font-size: 0.75rem;">Current Airline</span>
                        <span class="fw-bold text-dark">{{ session('activeairline')->name }}</span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace ms-1">{{ session('activeairline')->icao_callsign }}</span>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <span class="d-block display-6 fw-bold text-primary">{{ $flight_count }}</span>
                                <span class="text-muted small">Flights</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <span class="d-block display-6 fw-bold text-success">{{ $flight_hours }}</span>
                                <span class="text-muted small">Hours</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($flight_count > 0 && isset($flights) && $flights->count() > 0)
                    <div class="mt-4 pt-3 border-top border-light">
                        <span class="text-muted d-block small text-uppercase fw-semibold mb-2" style="font-size: 0.75rem;">Last Duty</span>
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded small">
                            <span class="font-monospace fw-bold">{{ $flights->first()->full_icao_callsign }}</span>
                            <span>
                                <span class="fw-semibold">{{ $flights->first()->departure_airport->icao_code }}</span> 
                                <i class="bi bi-arrow-right text-muted mx-1"></i> 
                                <span class="fw-semibold">{{ $flights->first()->arrival_icao }}</span>
                            </span>
                        </div>
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
                    <p class="small mb-0 text-secondary">Please verify your email address to unlock all functions of this YAAMS instance.</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="bi bi-shield-slash-fill fs-4 text-danger"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">An error occurred</h6>
                    <p class="small mb-0 text-secondary">Something went sideways! Please try again or contact an admin.</p>
                </div>
            </div>
        @endif

        <div class="card">
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
                <span class="badge bg-light text-secondary border fw-normal">Last 5 Flights</span>
            </div>
            <div class="card-body p-0">
                @if($flights->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-airplane text-secondary fs-3 d-block mb-2"></i>
                        No flights recorded yet for {{ session('activeairline')->name }}.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary uppercase tracking-wider small">
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
                                        {{ $flight->full_flight_number }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">{{ $flight->full_icao_callsign }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <abbr title="{{ $flight->departure_airport->name }}" class="text-decoration-none fw-bold text-dark font-monospace">{{ $flight->departure_airport->icao_code }}</abbr>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <abbr title="{{ $flight->arrival_airport->name }}" class="text-decoration-none fw-bold text-dark font-monospace">{{ $flight->arrival_icao }}</abbr>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="fw-medium text-dark">{{ $flight->aircraft->registration }}</span>
                                            <span class="text-muted d-block" style="font-size: 0.8rem;">{{ $flight->aircraft->full_type }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 fw-semibold text-dark">
                                        {{ $flight->pilot->name }}
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