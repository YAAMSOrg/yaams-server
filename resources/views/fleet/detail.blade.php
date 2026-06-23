@extends('layouts.app')
@section('title', 'View Aircraft - ' . $aircraft->registration)
@section('content')

<div class="container py-4">
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                <strong>Error during request:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="display-5 fw-bold mb-1">
                {{ $aircraft->registration }}
                @if($aircraft->active == 1)
                    <span class="badge bg-success ms-2 fs-6 vertical-align-middle">Active</span>
                @else
                    <span class="badge bg-danger ms-2 fs-6 vertical-align-middle">Inactive</span>
                @endif
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-airplane-fill me-1"></i> {{ $aircraft->full_type }} &middot; Operated by <strong>{{ $aircraft->airline->name }}</strong>
            </p>
        </div>
        <div>
            @can('edit aircraft')
                <a href="{{ route('editaircraft', $aircraft->id) }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-pencil-square me-1"></i> Edit Aircraft
                </a>
            @endcan
            <a href="{{ route('fleetmanager') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Fleet
            </a>
        </div>
    </div>

    @if($aircraft->active == 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-5 me-3"></i>
            <div>
                This aircraft is currently inactive and cannot be assigned to flights.
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100 bg-light">
                        <div class="card-body d-flex align-items-center">
                            <div class="p-3 bg-white rounded-3 shadow-sm me-3 text-primary">
                                <i class="bi bi-hash fs-3 lh-1"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase fw-bold tracking-wider fs-7">Total Flights</small>
                                <span class="h4 fw-bold mb-0">{{ $aircraft->total_flights_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100 bg-light">
                        <div class="card-body d-flex align-items-center">
                            <div class="p-3 bg-white rounded-3 shadow-sm me-3 text-success">
                                <i class="bi bi-clock-history fs-3 lh-1"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase fw-bold tracking-wider fs-7">Flight Hours</small>
                                <span class="h4 fw-bold mb-0">{{ number_format($aircraft->total_flights_hours ?? 0, 1) }} hrs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <i class="bi bi-card-text text-muted me-2"></i> Technical Specifications & Configuration
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6 border-end">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="bg-light ps-4 py-2 text-secondary fw-bold fs-7 text-uppercase">General & Tech Codes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5" style="width: 45%;">Manufacturer MSN</td>
                                        <td class="fw-semibold pe-4">{{ $aircraft->msn ?? 'Not Specified' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">SELCAL Code</td>
                                        <td class="fw-semibold font-monospace text-primary pe-4">{{ $aircraft->selcal ?? 'Not Assigned' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">ICAO 24-bit Hex</td>
                                        <td class="fw-semibold font-monospace text-dark pe-4">{{ $aircraft->hex_code ?? 'Not Assigned' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">In Service Since</td>
                                        <td class="fw-medium pe-4">{{ \Carbon\Carbon::parse($aircraft->created_at)->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">Distance Flown</td>
                                        <td class="fw-medium pe-4">{{ number_format($aircraft->total_distance_flown) }} NM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="bg-light ps-4 py-2 text-secondary fw-bold fs-7 text-uppercase">Propulsion & Equipment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5" style="width: 45%;">Engine Variant</td>
                                        <td class="fw-bold text-success pe-4">{{ $aircraft->engine_type ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">SATCOM</td>
                                        <td class="pe-4">
                                            @if($aircraft->satcom)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                                    <i class="bi bi-wifi me-1"></i> Equipped
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2.5 py-1">None</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">Wingtip Devices</td>
                                        <td class="pe-4">
                                            @if($aircraft->winglets)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                                    <i class="bi bi-airplane-engines me-1"></i> Winglets
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2.5 py-1">None</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Weight Profile Section -->
                    <div class="border-top">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th colspan="6" class="bg-light ps-4 py-2 text-secondary fw-bold fs-7 text-uppercase">Performance Weights</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-muted ps-4 py-2.5" style="width: 20%;">MTOW</td>
                                    <td class="fw-semibold text-dark py-2.5" style="width: 13%;">
                                        {{ $aircraft->mtow ? number_format($aircraft->mtow) . ' kg' : 'N/A' }}
                                    </td>
                                    <td class="text-muted py-2.5" style="width: 20%;">MZFW</td>
                                    <td class="fw-semibold text-dark py-2.5" style="width: 13%;">
                                        {{ $aircraft->mzfw ? number_format($aircraft->mzfw) . ' kg' : 'N/A' }}
                                    </td>
                                    <td class="text-muted py-2.5" style="width: 20%;">MLW</td>
                                    <td class="fw-semibold text-dark pe-4 py-2.5" style="width: 14%;">
                                        {{ $aircraft->mlw ? number_format($aircraft->mlw) . ' kg' : 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Remarks Section -->
                    <div class="border-top p-4 bg-light bg-opacity-30">
                        <h6 class="fw-bold text-secondary fs-7 text-uppercase mb-2">Remarks / Configuration Details</h6>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.5;">
                            {{ $aircraft->remarks ? $aircraft->remarks : 'No additional remarks or custom configurations specified for this airframe.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <i class="bi bi-clock-history text-muted me-2"></i> Recent Flights
                </div>
                <div class="card-body p-0">
                    @if($lastFlights->isEmpty())
                        <div class="text-muted p-4 text-center">
                            <i class="bi bi-info-circle me-1"></i> No flights logged for this aircraft yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light border-bottom text-muted small text-uppercase">
                                    <tr>
                                        <th scope="col" class="ps-4">Flight</th>
                                        <th scope="col">Pilot</th>
                                        <th scope="col">Route</th>
                                        <th scope="col">Duration</th>
                                        <th scope="col" class="pe-4 text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lastFlights as $flight)
                                        <tr>
                                            <td class="ps-4 font-monospace fw-semibold">
                                                <a href="{{ route('viewflight', $flight->id) }}" class="text-decoration-none">
                                                    {{ $flight->full_flight_number }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $flight->pilot->name }}
                                            </td>
                                            <td class="font-monospace">
                                                <abbr title="{{ $flight->departure_airport->name }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-0.5 rounded border">{{ $flight->departure_icao }}</abbr>
                                                <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                <abbr title="{{ $flight->arrival_airport->name }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-0.5 rounded border">{{ $flight->arrival_icao }}</abbr>
                                            </td>
                                            <td class="text-muted">
                                                {{ $flight->flight_duration }}
                                            </td>
                                            <td class="pe-4 text-end text-secondary small">
                                                {{ $flight->flight_date }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light text-center py-5 border-2 border-dashed">
                <div class="card-body py-4">
                    <div class="text-muted mb-3">
                        <i class="bi bi-images fs-1 text-secondary opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-secondary">Aircraft Gallery</h5>
                    <p class="text-muted mx-auto style-muted small mb-0" style="max-width: 420px;">
                        Screenshot upload feature is coming soon. In the future, you will be able to see and share real flight simulator screenshots of this aircraft here.
                    </p>
                    <span class="badge bg-secondary opacity-70 mt-3 fs-7 uppercase tracking-wider">Feature coming soon</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i> Current Location Tracking
                </div>
                <div class="position-relative">
                    <x-maps-leaflet style="height: 320px; width: 100%;" :zoomLevel="11" :markers="[['lat' => $lat, 'long' => $lon]]" :centerPoint="['lat' => $lat, 'long' => $lon]"></x-maps-leaflet>
                </div>
                <div class="card-body bg-light border-top text-center py-3">
                    <div class="small text-muted uppercase fw-bold tracking-wide mb-1">Status</div>
                    <p class="mb-0 fw-medium text-dark">
                        <i class="bi bi-building me-1"></i> On the ground at 
                        <span class="badge bg-dark font-monospace fs-6 px-2 py-1 ms-1">{{ $aircraft->location->icao_code }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed {
        border-style: dashed !important;
        border-color: #dee2e6 !important;
    }
    .fs-7 { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>

@endsection