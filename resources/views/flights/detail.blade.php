@extends('layouts.app')
@section('title', 'Flight ' . $flight->full_flight_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12">

        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            @php
                // Deep links (e.g. from a notification mail) have no useful previous page
                $backUrl = url()->previous(route('flightlist'));
                if ($backUrl === url()->current()) {
                    $backUrl = route('flightlist');
                }
            @endphp
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
            </a>

            @php
                $canReview = auth()->user()->can('review', $flight);
            @endphp
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <p class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i> Error during request</p>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif


        <div class="card border-0 shadow-sm yaams-flight-hero overflow-hidden mb-4 position-relative">
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                    
                    <div>
                        <h1 class="yaams-flight-hero__title">
                            <span class="yaams-flight-hero__airline" title="{{ $flight->airline->name }}">{{ $flight->airline->icao_callsign }}</span>
                            <span class="font-monospace">{{ $flight->full_flight_number }}</span>
                            <span class="yaams-flight-hero__callsign">Callsign <span class="font-monospace">{{ $flight->full_icao_callsign }}</span></span>
                        </h1>
                        
                        <div class="d-flex align-items-center gap-2 gap-sm-3 gap-md-4 my-3">
                            <div class="text-center yaams-flight-hero__endpoint">
                                <span class="d-block yaams-flight-hero__icao fw-bold font-monospace lh-1">{{ $flight->departure_icao }}</span>
                                <span class="small text-white-50 text-truncate d-block mt-1" title="{{ $flight->departure_airport?->name }}">{{ $flight->departure_airport?->name ?? 'Unknown airport' }}</span>
                            </div>

                            <div class="flex-grow-1 px-1 text-center yaams-flight-hero__leg">
                                <div class="d-flex flex-wrap justify-content-center column-gap-3 mb-2 small text-white-50 font-monospace">
                                    <span class="text-nowrap"><i class="bi bi-clock me-1" aria-hidden="true"></i>{{ $flight->flight_duration }}</span>
                                    <span class="text-nowrap"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $flight->raw_distance !== null ? number_format($flight->raw_distance) : '—' }} nm</span>
                                </div>
                                <span class="visually-hidden">to</span>
                                <div class="route-line position-relative">
                                    <span class="route-plane"><i class="bi bi-airplane-fill" aria-hidden="true"></i></span>
                                </div>
                            </div>

                            <div class="text-center yaams-flight-hero__endpoint">
                                <span class="d-block yaams-flight-hero__icao fw-bold font-monospace lh-1">{{ $flight->arrival_icao }}</span>
                                <span class="small text-white-50 text-truncate d-block mt-1" title="{{ $flight->arrival_airport?->name }}">{{ $flight->arrival_airport?->name ?? 'Unknown airport' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-md-end d-flex flex-row flex-md-column justify-content-between align-items-center align-items-md-end gap-2 p-3 rounded yaams-flight-hero__meta">
                        <div>
                            <span class="text-white-50 small d-block">PIREP ID</span>
                            <span class="font-monospace fw-bold fs-5 text-white">#{{ $flight->id }}</span>
                        </div>
                        <div class="mt-md-2">
                            <x-flight-status :flight="$flight" class="px-3 py-2" />
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if($flight->status_id == 3)
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4" role="status">
                <i class="bi bi-x-octagon-fill fs-4" aria-hidden="true"></i>
                <div>
                    <h2 class="alert-heading h6 fw-bold mb-1">This PIREP was rejected</h2>
                    @if($flight->rejection_remarks)
                        <p class="small mb-0" style="white-space: pre-line;">{{ $flight->rejection_remarks }}</p>
                    @else
                        <p class="small mb-0">No reason was given. Ask your airline's dispatchers or managers if you're unsure why.</p>
                    @endif
                    <p class="small mb-0 mt-1">Its block time doesn't count towards logged hours.</p>
                </div>
            </div>
        @elseif($flight->status_id == 1 && $canReview)
            @php
                $reviewMinutes = (int) $flight->flight_duration_minutes;
                $reviewHours = $reviewMinutes / 60;
                $reviewFuelUnit = $flight->airline->unit_is_lbs ? 'lbs' : 'kg';
            @endphp
            <section class="card shadow-sm mb-4 yaams-review-panel" aria-labelledby="review-heading">
                <div class="card-body d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
                    <div class="flex-grow-1">
                        <h2 id="review-heading" class="h6 fw-bold mb-1">Review this PIREP</h2>
                        <p class="small text-secondary mb-3">
                            Filed {{ $flight->created_at?->utc()->format('Y-m-d H:i') }}Z by {{ $flight->pilot_name }}
                        </p>
                        <dl class="yaams-flight-facts yaams-review-panel__facts">
                            <div>
                                <dt>Block time</dt>
                                <dd>{{ intdiv($reviewMinutes, 60) }}:{{ sprintf('%02d', $reviewMinutes % 60) }}</dd>
                            </div>
                            <div>
                                <dt title="Distance divided by block time">Groundspeed</dt>
                                <dd>{{ $reviewHours > 0 && $flight->raw_distance !== null ? number_format($flight->raw_distance / $reviewHours) . ' kt' : '—' }}</dd>
                            </div>
                            <div>
                                <dt>Fuel / hour</dt>
                                <dd>{{ $reviewHours > 0 ? number_format($flight->burned_fuel / $reviewHours) . ' ' . $reviewFuelUnit . '/h' : '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <form class="flex-fill" action="{{ route('flightreviewaccept', $flight->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-2 text-nowrap">
                                <i class="bi bi-check-lg" aria-hidden="true"></i> Accept PIREP
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger flex-fill d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-lg" aria-hidden="true"></i> Reject…
                        </button>
                    </div>
                </div>
            </section>
        @elseif($flight->status_id == 1)
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 mb-4" role="status">
                <i class="bi bi-hourglass-split fs-4" aria-hidden="true"></i>
                <div>
                    <h2 class="alert-heading h6 fw-bold mb-1">Awaiting review</h2>
                    <p class="small mb-0">A dispatcher or manager of {{ $flight->airline->name }} will review this PIREP. Its block time counts towards logged hours once accepted.</p>
                </div>
            </div>
        @endif

        @php
            $fuelUnit = session('activeairline')->unit_is_lbs ? 'lbs' : 'kg';
            $blockOff = \Illuminate\Support\Carbon::parse($flight->blockoff);
            $blockOn = \Illuminate\Support\Carbon::parse($flight->blockon);
            $blockMinutes = (int) $flight->flight_duration_minutes;
        @endphp
        <section class="card border-0 shadow-sm mb-4" aria-labelledby="flight-record-heading">
            <div class="card-body">
                <h2 id="flight-record-heading" class="visually-hidden">Flight record</h2>
                <dl class="yaams-flight-facts">
                    <div>
                        <dt>Date</dt>
                        <dd>{{ $blockOff->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt>Block off</dt>
                        <dd>{{ $blockOff->format('H:i') }}Z</dd>
                    </div>
                    <div>
                        <dt>Block on</dt>
                        <dd>{{ $blockOn->format('H:i') }}Z</dd>
                    </div>
                    <div>
                        <dt>Block time</dt>
                        <dd>{{ intdiv($blockMinutes, 60) }}:{{ sprintf('%02d', $blockMinutes % 60) }}</dd>
                    </div>
                    <div>
                        <dt>Distance</dt>
                        <dd>{{ $flight->raw_distance !== null ? number_format($flight->raw_distance) . ' nm' : '—' }}</dd>
                    </div>
                    <div>
                        <dt>Fuel burned</dt>
                        <dd>{{ number_format($flight->burned_fuel) }} {{ $fuelUnit }}</dd>
                    </div>
                    <div>
                        <dt>Cruise</dt>
                        <dd title="{{ number_format($flight->crzalt) }} ft">FL{{ str_pad((string) round($flight->crzalt / 100), 3, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div>
                        <dt>Network</dt>
                        <dd>{{ $flight->online_network?->networkname ?? 'Offline' }}</dd>
                    </div>
                </dl>

                <div class="yaams-flight-crew">
                    <div>
                        <i class="bi bi-person-badge" aria-hidden="true"></i>
                        <span>
                            <span class="fw-semibold">{{ $flight->pilot_name }}</span>
                            @if($flight->pilot)
                                <span class="text-secondary">· {{ $flight->pilot->logged_flights($flight->airline) }} accepted flights</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <i class="bi bi-airplane" aria-hidden="true"></i>
                        <span>
                            <a class="fw-semibold font-monospace" href="{{ route('viewaircraft', $flight->aircraft->id) }}">{{ $flight->aircraft->registration }}</a>
                            <span class="text-secondary">· {{ $flight->aircraft->full_type }}</span>
                        </span>
                    </div>
                    @if($flight->reviewed_at && in_array((int) $flight->status_id, [2, 3], true))
                        <div>
                            <i class="bi {{ $flight->status_id == 2 ? 'bi-check-circle' : 'bi-x-circle' }}" aria-hidden="true"></i>
                            <span>
                                <span class="fw-semibold">{{ $flight->status_id == 2 ? 'Accepted' : 'Rejected' }} by {{ $flight->reviewer?->name ?? 'a deleted user' }}</span>
                                <span class="text-secondary">· {{ $flight->reviewed_at->utc()->format('Y-m-d H:i') }}Z</span>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-map text-primary" aria-hidden="true"></i>
                            <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase">ATC Route & Remarks</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <h3 class="text-muted small fw-bold text-uppercase mb-1">Filed ATC Route</h3>
                            <div class="p-3 bg-light font-monospace rounded border text-dark fs-6 text-uppercase" style="letter-spacing: 0.5px; line-height: 1.6; overflow-wrap: anywhere;">{{ $flight->route ?: 'No route filed' }}</div>
                        </div>

                        @if($flight->remarks)
                            <div>
                                <h3 class="text-muted small fw-bold text-uppercase mb-1">Pilot Remarks</h3>
                                <div class="p-3 bg-light-subtle rounded border text-body small" style="white-space: pre-line; overflow-wrap: anywhere;">{{ $flight->remarks }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div> </div>
</div>

@if($flight->status_id == 1 && $canReview)
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content border-0 shadow" action="{{ route('flightreviewreject', $flight->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-bold" id="rejectModalLabel">Reject PIREP {{ $flight->full_flight_number }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="rejection_remarks" class="form-label fw-semibold">Reason for {{ $flight->pilot_name }}</label>
                    <textarea class="form-control @error('rejection_remarks') is-invalid @enderror" id="rejection_remarks" name="rejection_remarks" rows="4" maxlength="1000" required aria-describedby="rejection_remarks_help">{{ old('rejection_remarks') }}</textarea>
                    @error('rejection_remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="rejection_remarks_help" class="form-text">The pilot sees this reason. The flight's block time won't count towards their hours.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-semibold">Reject PIREP</button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->has('rejection_remarks'))
        @push('scripts')
            <script>
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectModal')).show();
            </script>
        @endpush
    @endif
@endif
@endsection
