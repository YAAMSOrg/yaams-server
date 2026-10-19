@extends('layouts.app')
@section('title', 'Aircraft ' . $aircraft->registration)
@section('content')

<div>
    {{-- Upload errors render inline in the gallery's Upload tab, not here. --}}
    @php
        $pageErrors = collect($errors->getMessages())->except('screenshot')->flatten();
    @endphp
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

    {{-- A suspended airline is read-only (every POST is refused), so it
         offers neither moderation nor uploads. --}}
    @php
        $airlineWritable = ! $aircraft->airline->isSuspended();
        $canModerate = $airlineWritable && auth()->user()->can('moderateImages', $aircraft);
        $canUpload = $airlineWritable && auth()->user()->can('uploadImage', $aircraft);
        $lastFlight = $lastFlights->first();
        $livery = $aircraft->approvedImages->firstWhere('is_primary', true);
        $pendingCount = $canModerate ? $aircraft->pendingImages->count() : 0;
        $airportName = $aircraft->location?->name;
    @endphp

    {{-- Status first: where it is and whether it can fly, when it last flew,
         and what is waiting on a moderator. The livery shot sits beside it. --}}
    <header class="aircraft-header d-flex flex-column flex-md-row gap-4 mb-4 pb-4 border-bottom">
        <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
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
                    <p class="font-monospace text-muted mb-0">{{ $aircraft->full_type }}</p>
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

            <ul class="aircraft-status list-unstyled mt-4 mb-0">
                <li>
                    @if($aircraft->isRetired())
                        <i class="bi bi-archive" aria-hidden="true"></i>
                        <span>
                            Last seen at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) &middot; {{ $airportName }}@endif
                            <span class="d-block text-muted small">Retired {{ $aircraft->retired_at?->format('Y-m-d') }}@if($aircraft->retired_reason) &middot; <span class="text-break" style="white-space: pre-line;">{{ $aircraft->retired_reason }}</span>@endif. It cannot fly again; its history is kept.</span>
                        </span>
                    @elseif($aircraft->status === \App\Models\Aircraft::STATUS_INACTIVE)
                        <i class="bi bi-pause-circle" aria-hidden="true"></i>
                        <span>
                            Grounded at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) &middot; {{ $airportName }}@endif
                            <span class="d-block text-muted small">Inactive, so it cannot be assigned to flights.</span>
                        </span>
                    @else
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <span>On the ground at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) &middot; {{ $airportName }}@endif</span>
                    @endif
                </li>
                <li>
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    @if($lastFlight)
                        @php
                            $blockOn = \Carbon\Carbon::parse($lastFlight->blockon, 'UTC');
                        @endphp
                        <span>
                            Last flown <time datetime="{{ $blockOn->toIso8601ZuluString() }}" title="{{ $blockOn->format('Y-m-d H:i') }} UTC">{{ $blockOn->diffForHumans() }}</time>
                            &middot; <a href="{{ route('viewflight', $lastFlight->id) }}" class="font-monospace fw-semibold">{{ $lastFlight->full_flight_number }}</a>
                            <span class="font-monospace">{{ $lastFlight->departure_icao }}</span><i class="bi bi-arrow-right text-muted mx-1" aria-hidden="true"></i><span class="visually-hidden">to</span><span class="font-monospace">{{ $lastFlight->arrival_icao }}</span>
                        </span>
                    @else
                        <span class="text-muted">Not flown yet</span>
                    @endif
                </li>
                @if($pendingCount > 0)
                    <li>
                        <i class="bi bi-hourglass-split text-warning-emphasis" aria-hidden="true"></i>
                        <a href="#pending-review-{{ $aircraft->id }}" class="link-warning-emphasis fw-semibold">
                            {{ $pendingCount }} {{ \Illuminate\Support\Str::plural('screenshot', $pendingCount) }} awaiting review
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <figure class="aircraft-livery aircraft-gallery mb-0 flex-shrink-0">
            @if($livery)
                <div class="gallery-group d-block">
                    <button type="button"
                            class="gallery-thumb"
                            data-gallery-full="{{ route('aircraft.images.show', [$aircraft->id, $livery->id]) }}"
                            data-gallery-caption="{{ $aircraft->registration }} · Main livery shot"
                            aria-label="Open main livery shot of {{ $aircraft->registration }}">
                        <img src="{{ route('aircraft.images.show', [$aircraft->id, $livery->id]) }}"
                             alt="Main livery shot of {{ $aircraft->registration }}">
                        <span class="gallery-thumb-overlay"><i class="bi bi-arrows-fullscreen" aria-hidden="true"></i></span>
                    </button>
                </div>
            @else
                <div class="aircraft-livery__empty">
                    <i class="bi bi-airplane fs-2 opacity-50" aria-hidden="true"></i>
                    <span>No livery shot yet</span>
                    @if($canUpload)
                        <button type="button" class="btn btn-link btn-sm p-0" data-open-upload="upload-tab-btn-{{ $aircraft->id }}">Upload one</button>
                    @endif
                </div>
            @endif
        </figure>
    </header>

    <div class="row g-4">
        <div class="col-lg-8">
            @include('fleet._gallery', [
                'aircraft' => $aircraft,
                'canModerate' => $canModerate,
                'canUpload' => $canUpload,
            ])

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-card-text text-muted me-2" aria-hidden="true"></i> Technical Specifications & Configuration</h2>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6 border-end">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="bg-light ps-4 py-2 text-secondary fw-bold fs-7 text-uppercase">General & Tech Codes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-4" style="width: 45%;">Manufacturer MSN</td>
                                        <td class="fw-semibold pe-4">@if(filled($aircraft->msn)){{ $aircraft->msn }}@else<span class="text-muted fw-normal">Not specified</span>@endif</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">SELCAL Code</td>
                                        <td class="fw-semibold font-monospace pe-4">@if(filled($aircraft->selcal)){{ $aircraft->selcal }}@else<span class="text-muted fw-normal">Not specified</span>@endif</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">ICAO 24-bit Hex</td>
                                        <td class="fw-semibold font-monospace pe-4">@if(filled($aircraft->hex_code)){{ $aircraft->hex_code }}@else<span class="text-muted fw-normal">Not specified</span>@endif</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">In Fleet Since</td>
                                        <td class="fw-medium pe-4">{{ $aircraft->in_service_since }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">Accepted Flights</td>
                                        <td class="fw-medium pe-4">{{ number_format($aircraft->total_flights_count) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">Block Time</td>
                                        <td class="fw-medium pe-4 font-monospace">{{ $aircraft->total_block_time }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">Distance Flown</td>
                                        <td class="fw-medium pe-4">{{ number_format($aircraft->total_distance_flown) }} NM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="bg-light ps-4 py-2 text-secondary fw-bold fs-7 text-uppercase">Propulsion & Equipment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-4" style="width: 45%;">Engine Variant</td>
                                        <td class="fw-semibold pe-4">@if(filled($aircraft->engine_type)){{ $aircraft->engine_type }}@else<span class="text-muted fw-normal">Not specified</span>@endif</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">SATCOM</td>
                                        <td class="pe-4">
                                            @if($aircraft->satcom)
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle py-1">
                                                    <i class="bi bi-wifi me-1" aria-hidden="true"></i> Equipped
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border py-1">None</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-4">Wingtip Devices</td>
                                        <td class="pe-4">
                                            @if($aircraft->winglets)
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle py-1">
                                                    <i class="bi bi-airplane-engines me-1" aria-hidden="true"></i> Winglets
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border py-1">None</span>
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
                                <dd class="fw-semibold mb-0">@if($aircraft->mtow){{ number_format($aircraft->mtow) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">MZFW</dt>
                                <dd class="fw-semibold mb-0">@if($aircraft->mzfw){{ number_format($aircraft->mzfw) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">MLW</dt>
                                <dd class="fw-semibold mb-0">@if($aircraft->mlw){{ number_format($aircraft->mlw) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div class="col-6 col-md-3">
                                <dt class="text-muted fw-normal small">Service Ceiling</dt>
                                <dd class="fw-semibold mb-0">@if($aircraft->service_ceiling){{ number_format($aircraft->service_ceiling) }} ft @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Remarks Section -->
                    <div class="border-top p-4 bg-light">
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
                                                <abbr title="{{ $flight->departure_airport?->name ?? $flight->departure_icao }}" class="text-decoration-none text-dark fw-bold bg-light px-2 rounded border">{{ $flight->departure_icao }}</abbr>
                                                <i class="bi bi-arrow-right text-muted mx-1" aria-hidden="true"></i>
                                                <abbr title="{{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}" class="text-decoration-none text-dark fw-bold bg-light px-2 rounded border">{{ $flight->arrival_icao }}</abbr>
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
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-geo-alt-fill text-danger me-2" aria-hidden="true"></i> Location</h2>
                </div>
                <div class="position-relative" role="region" aria-label="Map of {{ $aircraft->registration }} at {{ $aircraft->current_loc }}">
                    @if ($lat !== null && $lon !== null)
                        <x-maps-leaflet style="height: 320px; width: 100%;" :zoomLevel="11" :markers="[['lat' => $lat, 'long' => $lon]]" :centerPoint="['lat' => $lat, 'long' => $lon]"></x-maps-leaflet>
                    @endif
                </div>
                <div class="card-body bg-light border-top text-center py-3">
                    <div class="small text-muted text-uppercase fw-bold tracking-wider mb-1">Status</div>
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
    .aircraft-status { display: grid; gap: .6rem; }
    .aircraft-status li { display: flex; gap: .6rem; align-items: baseline; }
    .aircraft-status li > .bi { flex: none; width: 1.1rem; color: var(--bs-secondary-color); }
    .aircraft-livery { width: 100%; }
    @media (min-width: 768px) { .aircraft-livery { width: 280px; } }
    .aircraft-livery__empty {
        aspect-ratio: 16 / 9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .25rem;
        border: 1px dashed var(--bs-border-color);
        border-radius: .5rem;
        color: var(--bs-secondary-color);
        background: var(--bs-tertiary-bg);
        font-size: .9rem;
    }
</style>

<script>
    // "Upload one" in the empty livery slot: switch to the Upload tab and bring it into view.
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-open-upload]');
        if (!trigger) return;
        const tab = document.getElementById(trigger.dataset.openUpload);
        if (!tab) return;
        bootstrap.Tab.getOrCreateInstance(tab).show();
        tab.scrollIntoView({ block: 'center' });
        tab.focus({ preventScroll: true });
    });
</script>

@endsection