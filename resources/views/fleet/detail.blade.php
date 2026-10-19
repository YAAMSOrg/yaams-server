@extends('layouts.app')
@section('title', 'Aircraft ' . $aircraft->registration)
@section('content')

<div class="container py-4">
    {{-- Upload errors render inline in the gallery's Upload tab, not here. --}}
    @php($pageErrors = collect($errors->getMessages())->except('screenshot')->flatten())
    @if($pageErrors->isNotEmpty())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2" aria-hidden="true"></i>
                <strong>Error during request:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach($pageErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="display-5 fw-bold mb-1 text-break">
                {{ $aircraft->registration }}
                @if($aircraft->status === \App\Models\Aircraft::STATUS_ACTIVE)
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle ms-2 fs-6 align-middle">Active</span>
                @elseif($aircraft->status === \App\Models\Aircraft::STATUS_RETIRED)
                    <span class="badge bg-dark ms-2 fs-6 align-middle">Retired</span>
                @else
                    <span class="badge bg-secondary ms-2 fs-6 align-middle">Inactive</span>
                @endif
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-airplane-fill me-1" aria-hidden="true"></i> {{ $aircraft->full_type }} &middot; Operated by <strong>{{ $aircraft->airline->name }}</strong>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('update', $aircraft)
                <a href="{{ route('editaircraft', $aircraft->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i> Edit Aircraft
                </a>
            @endcan
            <a href="{{ route('fleetmanager') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to Fleet
            </a>
        </div>
    </div>

    @if($aircraft->isRetired())
        <div class="alert alert-dark border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-archive-fill fs-5 me-3" aria-hidden="true"></i>
            <div>
                This aircraft was retired on <strong>{{ $aircraft->retired_at?->format('Y-m-d') }}</strong>@if($aircraft->retired_reason) &middot; {{ $aircraft->retired_reason }}@endif.
                It can no longer fly and cannot be reactivated. Its flight history is preserved.
            </div>
        </div>
    @elseif($aircraft->status === \App\Models\Aircraft::STATUS_INACTIVE)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-5 me-3" aria-hidden="true"></i>
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
                                <i class="bi bi-hash fs-3 lh-1" aria-hidden="true"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase fw-bold tracking-wider fs-7">Accepted Flights</small>
                                <span class="h4 fw-bold mb-0">{{ $aircraft->total_flights_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100 bg-light">
                        <div class="card-body d-flex align-items-center">
                            <div class="p-3 bg-white rounded-3 shadow-sm me-3 text-success">
                                <i class="bi bi-clock-history fs-3 lh-1" aria-hidden="true"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase fw-bold tracking-wider fs-7">Block Time</small>
                                <span class="h4 fw-bold mb-0 font-monospace">{{ $aircraft->total_block_time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- A suspended airline is read-only (every POST is refused), so it
                 offers neither moderation nor uploads. --}}
            @php($airlineWritable = ! $aircraft->airline->isSuspended())
            @include('fleet._gallery', [
                'aircraft' => $aircraft,
                // Managers moderate even a retired airframe's queue.
                'canModerate' => $airlineWritable && auth()->user()->can('moderateImages', $aircraft),
                // Any member may contribute, unless the airframe is retired.
                'canUpload' => $airlineWritable && auth()->user()->can('uploadImage', $aircraft),
            ])

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-card-text text-muted me-2" aria-hidden="true"></i> Technical Specifications & Configuration</h2>
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
                                        <td class="text-muted ps-4 py-2.5">In Fleet Since</td>
                                        <td class="fw-medium pe-4">{{ $aircraft->in_service_since }}</td>
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
                                        <td class="fw-semibold pe-4">{{ $aircraft->engine_type ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4 py-2.5">SATCOM</td>
                                        <td class="pe-4">
                                            @if($aircraft->satcom)
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1">
                                                    <i class="bi bi-wifi me-1" aria-hidden="true"></i> Equipped
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
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1">
                                                    <i class="bi bi-airplane-engines me-1" aria-hidden="true"></i> Winglets
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
                        <h3 class="bg-light ps-4 py-2 mb-0 text-secondary fw-bold fs-7 text-uppercase">Performance Weights</h3>
                        <dl class="row g-3 mb-0 px-4 py-3">
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">MTOW</dt>
                                <dd class="fw-semibold mb-0">{{ $aircraft->mtow ? number_format($aircraft->mtow) . ' kg' : 'N/A' }}</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">MZFW</dt>
                                <dd class="fw-semibold mb-0">{{ $aircraft->mzfw ? number_format($aircraft->mzfw) . ' kg' : 'N/A' }}</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">MLW</dt>
                                <dd class="fw-semibold mb-0">{{ $aircraft->mlw ? number_format($aircraft->mlw) . ' kg' : 'N/A' }}</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">Service Ceiling</dt>
                                <dd class="fw-semibold mb-0">{{ $aircraft->service_ceiling ? number_format($aircraft->service_ceiling) . ' ft' : 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Remarks Section -->
                    <div class="border-top p-4 bg-light bg-opacity-30">
                        <h3 class="fw-bold text-secondary fs-7 text-uppercase mb-2">Remarks / Configuration Details</h3>
                        <p class="mb-0 text-muted text-break" style="font-size: 0.9rem; line-height: 1.5; white-space: pre-line;">{{-- pre-line keeps the remark's own line breaks, so no whitespace around it --}}{{ $aircraft->remarks ?: 'No additional remarks or custom configurations specified for this airframe.' }}</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-clock-history text-muted me-2" aria-hidden="true"></i> Recent Flights</h2>
                </div>
                <div class="card-body p-0">
                    @if($lastFlights->isEmpty())
                        <div class="text-muted p-4 text-center">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i> No flights logged for this aircraft yet.
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
                                        <th scope="col" class="pe-4 text-end">Date (UTC)</th>
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
                                                {{ $flight->pilot_name }}
                                            </td>
                                            <td class="font-monospace">
                                                <abbr title="{{ $flight->departure_airport?->name ?? $flight->departure_icao }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-0.5 rounded border">{{ $flight->departure_icao }}</abbr>
                                                <i class="bi bi-arrow-right text-muted mx-1" aria-hidden="true"></i>
                                                <abbr title="{{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-0.5 rounded border">{{ $flight->arrival_icao }}</abbr>
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
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-geo-alt-fill text-danger me-2" aria-hidden="true"></i> Current Location Tracking</h2>
                </div>
                <div class="position-relative" role="region" aria-label="Map of {{ $aircraft->registration }} at {{ $aircraft->current_loc }}">
                    @if ($lat !== null && $lon !== null)
                        <x-maps-leaflet style="height: 320px; width: 100%;" :zoomLevel="11" :markers="[['lat' => $lat, 'long' => $lon]]" :centerPoint="['lat' => $lat, 'long' => $lon]"></x-maps-leaflet>
                    @endif
                </div>
                <div class="card-body bg-light border-top text-center py-3">
                    <div class="small text-muted uppercase fw-bold tracking-wide mb-1">Status</div>
                    <p class="mb-0 fw-medium text-dark">
                        <i class="bi bi-building me-1" aria-hidden="true"></i> {{ $aircraft->isRetired() ? 'Last seen at' : 'On the ground at' }}
                        <span class="badge bg-dark font-monospace fs-6 px-2 py-1 ms-1" title="{{ $aircraft->location?->name }}">{{ $aircraft->current_loc }}</span>
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