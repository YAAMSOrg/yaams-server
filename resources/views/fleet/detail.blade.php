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
         and what is waiting on a moderator. Same graphite/amber hero band as the
         PIREP page, so aircraft and flight records read as one family. --}}
    <header class="card border-0 shadow-sm yaams-flight-hero yaams-aircraft-hero overflow-hidden mb-4">
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row gap-4">
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <h1 class="yaams-flight-hero__title">
                            <span class="yaams-flight-hero__airline" title="{{ $aircraft->airline->name }}">{{ $aircraft->airline->icao_callsign }}</span>
                            <span class="yaams-aircraft-hero__reg font-monospace text-break">{{ $aircraft->registration }}</span>
                            @if($aircraft->status === \App\Models\Aircraft::STATUS_ACTIVE)
                                <span class="badge bg-success-subtle text-success-emphasis fs-7 align-self-center">Active</span>
                            @elseif($aircraft->status === \App\Models\Aircraft::STATUS_RETIRED)
                                <span class="badge bg-light text-dark fs-7 align-self-center">Retired</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis fs-7 align-self-center">Inactive</span>
                            @endif
                        </h1>
                        <p class="yaams-flight-hero__callsign font-monospace mt-2 mb-0">{{ $aircraft->full_type }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @can('update', $aircraft)
                            <a href="{{ route('editaircraft', $aircraft->id) }}" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-pencil-square me-1" aria-hidden="true"></i> Edit Aircraft
                            </a>
                        @endcan
                        <a href="{{ route('fleetmanager') }}" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to Fleet
                        </a>
                    </div>
                </div>

                <ul class="yaams-aircraft-hero__status list-unstyled mt-4 mb-0">
                    <li>
                        @if($aircraft->isRetired())
                            <i class="bi bi-archive" aria-hidden="true"></i>
                            <span>
                                Last seen at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) <span class="text-white-50">&middot; {{ $airportName }}</span>@endif
                                <span class="d-block text-white-50 small">Retired {{ $aircraft->retired_at?->format('Y-m-d') }}@if($aircraft->retired_reason) &middot; <span class="text-break" style="white-space: pre-line;">{{ $aircraft->retired_reason }}</span>@endif. It cannot fly again; its history is kept.</span>
                            </span>
                        @elseif($aircraft->status === \App\Models\Aircraft::STATUS_INACTIVE)
                            <i class="bi bi-pause-circle" aria-hidden="true"></i>
                            <span>
                                Grounded at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) <span class="text-white-50">&middot; {{ $airportName }}</span>@endif
                                <span class="d-block text-white-50 small">Inactive, so it cannot be assigned to flights.</span>
                            </span>
                        @else
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>On the ground at <strong class="font-monospace">{{ $aircraft->current_loc }}</strong>@if($airportName) <span class="text-white-50">&middot; {{ $airportName }}</span>@endif</span>
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
                                <span class="text-white-50">&middot;</span> <a href="{{ route('viewflight', $lastFlight->id) }}" class="font-monospace fw-semibold">{{ $lastFlight->full_flight_number }}</a>
                                <span class="font-monospace">{{ $lastFlight->departure_icao }}</span><i class="bi bi-arrow-right text-white-50 mx-1" aria-hidden="true"></i><span class="visually-hidden">to</span><span class="font-monospace">{{ $lastFlight->arrival_icao }}</span>
                            </span>
                        @else
                            <span class="text-white-50">Not flown yet</span>
                        @endif
                    </li>
                    @if($pendingCount > 0)
                        <li class="is-attention">
                            <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                            <a href="#pending-review-{{ $aircraft->id }}" class="fw-semibold">
                                {{ $pendingCount }} {{ \Illuminate\Support\Str::plural('screenshot', $pendingCount) }} awaiting review
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <figure class="aircraft-livery aircraft-gallery mb-0 flex-shrink-0 align-self-md-center">
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
                        <i class="bi bi-airplane fs-2" aria-hidden="true"></i>
                        <span>No livery shot yet</span>
                        @if($canUpload)
                            <button type="button" class="btn btn-link btn-sm p-0" data-open-upload="upload-tab-btn-{{ $aircraft->id }}">Upload one</button>
                        @endif
                    </div>
                @endif
            </figure>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-lg-8">
            @include('fleet._gallery', [
                'aircraft' => $aircraft,
                'canModerate' => $canModerate,
                'canUpload' => $canUpload,
            ])

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-card-text text-muted me-2" aria-hidden="true"></i> Specifications</h2>
                </div>
                <div class="card-body p-0 yaams-aircraft-specs">
                    <section class="px-4 py-3">
                        <h3 class="yaams-aircraft-specs__label">Identity</h3>
                        <dl class="yaams-flight-facts">
                            <div>
                                <dt>MSN</dt>
                                <dd>@if(filled($aircraft->msn)){{ $aircraft->msn }}@else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>SELCAL</dt>
                                <dd>@if(filled($aircraft->selcal)){{ $aircraft->selcal }}@else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>ICAO hex</dt>
                                <dd>@if(filled($aircraft->hex_code)){{ $aircraft->hex_code }}@else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>In fleet since</dt>
                                <dd>{{ $aircraft->in_service_since }}</dd>
                            </div>
                        </dl>
                    </section>
                    <section class="px-4 py-3 border-top">
                        <h3 class="yaams-aircraft-specs__label">Utilisation</h3>
                        <dl class="yaams-flight-facts">
                            <div>
                                <dt>Accepted flights</dt>
                                <dd>{{ number_format($aircraft->total_flights_count) }}</dd>
                            </div>
                            <div>
                                <dt>Block time</dt>
                                <dd>{{ $aircraft->total_block_time }}</dd>
                            </div>
                            <div>
                                <dt>Distance flown</dt>
                                <dd>{{ number_format($aircraft->total_distance_flown) }} NM</dd>
                            </div>
                        </dl>
                    </section>
                    <section class="px-4 py-3 border-top">
                        <h3 class="yaams-aircraft-specs__label">Equipment</h3>
                        <dl class="yaams-flight-facts">
                            <div>
                                <dt>Engines</dt>
                                <dd>@if(filled($aircraft->engine_type)){{ $aircraft->engine_type }}@else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>SATCOM</dt>
                                <dd>@if($aircraft->satcom)<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-wifi me-1" aria-hidden="true"></i>Equipped</span>@else<span class="text-muted fw-normal">None</span>@endif</dd>
                            </div>
                            <div>
                                <dt>Wingtips</dt>
                                <dd>@if($aircraft->winglets)<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-airplane-engines me-1" aria-hidden="true"></i>Winglets</span>@else<span class="text-muted fw-normal">None</span>@endif</dd>
                            </div>
                        </dl>
                    </section>
                    <section class="px-4 py-3 border-top">
                        <h3 class="yaams-aircraft-specs__label">Weights &amp; performance</h3>
                        <dl class="yaams-flight-facts">
                            <div>
                                <dt>MTOW</dt>
                                <dd>@if($aircraft->mtow){{ number_format($aircraft->mtow) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>MZFW</dt>
                                <dd>@if($aircraft->mzfw){{ number_format($aircraft->mzfw) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>MLW</dt>
                                <dd>@if($aircraft->mlw){{ number_format($aircraft->mlw) }} kg @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                            <div>
                                <dt>Service ceiling</dt>
                                <dd>@if($aircraft->service_ceiling){{ number_format($aircraft->service_ceiling) }} ft @else<span class="text-muted fw-normal">Not specified</span>@endif</dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Remarks Section -->
                    <section class="border-top px-4 py-3">
                        <h3 class="yaams-aircraft-specs__label">Remarks</h3>
                        <p class="mb-0 text-muted text-break" style="font-size: 0.9rem; line-height: 1.5; white-space: pre-line;">{{-- pre-line keeps the remark's own line breaks, so no whitespace around it --}}{{ $aircraft->remarks ?: 'No remarks for this airframe.' }}</p>
                    </section>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 d-flex align-items-center"><i class="bi bi-journal-text text-muted me-2" aria-hidden="true"></i> Recent Flights</h2>
                </div>
                @if($lastFlights->isEmpty())
                    <div class="text-muted p-4 text-center">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i> No accepted flights for this aircraft yet.
                    </div>
                @else
                    <table class="table align-middle mb-0 yaams-logbook">
                        <caption class="visually-hidden">Last {{ $lastFlights->count() }} accepted flights of {{ $aircraft->registration }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">Date (UTC)</th>
                                <th scope="col">Flight</th>
                                <th scope="col">Route</th>
                                <th scope="col">Pilot</th>
                                <th scope="col" class="text-end">Block</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lastFlights as $flight)
                                <tr class="yaams-logbook__entry">
                                    <td class="yaams-logbook__date">
                                        <span class="d-block">{{ substr($flight->blockon, 0, 10) }}</span>
                                        <span class="yaams-logbook__sub">{{ substr($flight->blockoff, 11, 5) }}-{{ substr($flight->blockon, 11, 5) }}Z</span>
                                    </td>
                                    <td class="yaams-logbook__flight">
                                        <a href="{{ route('viewflight', $flight->id) }}" class="stretched-link">{{ $flight->full_flight_number }}</a>
                                        <span class="yaams-logbook__sub">{{ $flight->full_icao_callsign }}</span>
                                    </td>
                                    <td class="yaams-logbook__route">
                                        <span class="yaams-logbook__legs">
                                            <abbr title="{{ $flight->departure_airport?->name ?? $flight->departure_icao }}">{{ $flight->departure_icao }}</abbr><i class="bi bi-arrow-right" aria-hidden="true"></i><span class="visually-hidden"> to </span><abbr title="{{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}">{{ $flight->arrival_icao }}</abbr>
                                        </span>
                                    </td>
                                    <td class="yaams-logbook__aircraft yaams-logbook__pilot">
                                        <span class="d-block">{{ $flight->pilot_name }}</span>
                                    </td>
                                    <td class="yaams-logbook__block text-end">
                                        @if(is_int($flight->flight_duration_minutes))
                                            <span class="visually-hidden">Block time </span>{{ intdiv($flight->flight_duration_minutes, 60) }}:{{ sprintf('%02d', $flight->flight_duration_minutes % 60) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
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
    .yaams-aircraft-hero .min-w-0 { min-width: 0; }
    .yaams-aircraft-hero__reg { font-size: clamp(2rem, 5vw, 3rem); letter-spacing: -.01em; line-height: 1; }
    .yaams-aircraft-hero__status { display: grid; gap: .6rem; color: #fff; }
    .yaams-aircraft-hero__status li { display: flex; gap: .6rem; align-items: baseline; }
    .yaams-aircraft-hero__status li > .bi { flex: none; width: 1.1rem; color: var(--yaams-accent); }
    .yaams-aircraft-hero__status a { color: #fff; text-underline-offset: .2em; }
    .yaams-aircraft-hero__status a:hover { color: var(--yaams-accent); }
    .yaams-aircraft-hero__status .is-attention a { color: var(--yaams-accent); }
    .aircraft-livery { width: 100%; }
    @media (min-width: 768px) { .aircraft-livery { width: 300px; } }
    .aircraft-livery .gallery-thumb { box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .35); }
    .aircraft-livery__empty {
        aspect-ratio: 16 / 9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .25rem;
        border: 1px dashed rgba(255, 255, 255, .25);
        border-radius: .5rem;
        color: rgba(255, 255, 255, .72);
        font-size: .9rem;
    }
    .aircraft-livery__empty .btn-link { color: var(--yaams-accent); }
    .yaams-aircraft-specs__label {
        margin-bottom: .75rem;
        font-size: .775rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
    }
    /* The specs live in the narrower main column: never more than 4 across. */
    .yaams-aircraft-specs .yaams-flight-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 576px) {
        .yaams-aircraft-specs .yaams-flight-facts { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    /* The logbook sets its aircraft column in monospace; here it holds a pilot name. */
    .yaams-logbook__pilot > span:first-child { font-family: var(--bs-body-font-family); }
    .yaams-aircraft-specs dd .badge { font-family: var(--bs-body-font-family); }
    .yaams-aircraft-specs dd > .text-muted { font-family: var(--bs-body-font-family); }
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