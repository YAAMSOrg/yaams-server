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
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            @php
                $canReview = auth()->user()->can('review', $flight);
            @endphp
            @if($flight->status_id == 1 && $canReview)
                <div class="d-flex gap-2">
                    <form action="{{ route('flightreviewaccept', $flight->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                            <i class="bi bi-check-lg"></i> Approve PIREP
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg"></i> Reject PIREP
                    </button>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold" id="rejectModalLabel">Reject PIREP #{{ $flight->id }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('flightreviewreject', $flight->id) }}" method="POST">
                                @csrf
                                <div class="modal-body text-dark">
                                    <p>Are you sure you want to reject this flight? You can optionally provide a reason below.</p>
                                    <div class="mb-3">
                                        <label for="rejection_remarks" class="form-label small fw-bold text-uppercase">Reason (optional)</label>
                                        <textarea class="form-control" name="rejection_remarks" id="rejection_remarks" rows="3" placeholder="e.g. Invalid fuel consumption, wrong aircraft type..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <h4 class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Error during request</h4>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

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
        @elseif($flight->status_id == 1)
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 mb-4" role="status">
                <i class="bi bi-hourglass-split fs-4" aria-hidden="true"></i>
                <div>
                    <h2 class="alert-heading h6 fw-bold mb-1">Awaiting review</h2>
                    <p class="small mb-0">
                        @if($canReview)
                            Approve or reject this PIREP to complete the review.
                        @else
                            A dispatcher or manager of {{ $flight->airline->name }} will review this PIREP. Its block time counts towards logged hours once accepted.
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm bg-dark text-white overflow-hidden mb-4 position-relative" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                    
                    <div>
                        <span class="text-primary-emphasis text-uppercase fw-bold tracking-wider small d-block mb-1 font-monospace">
                            Flight {{ $flight->full_flight_number }} // ATC: {{ $flight->full_icao_callsign }}
                        </span>
                        
                        <div class="d-flex align-items-center gap-3 gap-md-4 my-3">
                            <div class="text-center flex-shrink-0">
                                <h1 class="display-6 mb-0 fw-bold tracking-tight font-monospace lh-1">{{ $flight->departure_icao }}</h1>
                                <span class="small text-white-50 text-truncate d-block mt-1" style="max-width: 150px;" title="{{ $flight->departure_airport?->name }}">{{ $flight->departure_airport?->name ?? 'Unknown airport' }}</span>
                            </div>

                            <div class="flex-grow-1 px-1 text-center" style="min-width: 80px;">
                                <div class="d-flex justify-content-center gap-3 mb-2 small text-white-50 font-monospace">
                                    <span><i class="bi bi-clock me-1"></i>{{ $flight->flight_duration }}</span>
                                    <span><i class="bi bi-geo-alt me-1"></i>{{ $flight->raw_distance !== null ? number_format($flight->raw_distance) : '—' }} nm</span>
                                </div>
                                <div class="route-line position-relative">
                                    <span class="route-plane"><i class="bi bi-airplane-fill text-primary"></i></span>
                                </div>
                            </div>

                            <div class="text-center flex-shrink-0">
                                <h1 class="display-6 mb-0 fw-bold tracking-tight font-monospace lh-1">{{ $flight->arrival_icao }}</h1>
                                <span class="small text-white-50 text-truncate d-block mt-1" style="max-width: 150px;" title="{{ $flight->arrival_airport?->name }}">{{ $flight->arrival_airport?->name ?? 'Unknown airport' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-md-end d-flex flex-row flex-md-column justify-content-between align-items-center align-items-md-end gap-2 bg-black bg-opacity-20 p-3 rounded border border-white border-opacity-5">
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

        <div class="row g-3 mb-4">
            @php
                $fuelUnit = session('activeairline')->unit_is_lbs ? 'lbs' : 'kg';
                $stats = [
                    ['icon' => 'stopwatch',   'label' => 'Duration',     'value' => $flight->flight_duration],
                    ['icon' => 'geo-alt',     'label' => 'Distance',     'value' => ($flight->raw_distance !== null ? number_format($flight->raw_distance) : '—') . ' nm'],
                    ['icon' => 'fuel-pump',   'label' => 'Fuel Burned',  'value' => number_format($flight->burned_fuel) . ' ' . $fuelUnit],
                    ['icon' => 'arrows-expand', 'label' => 'Cruise Alt', 'value' => 'FL' . round($flight->crzalt / 100)],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bi bi-{{ $stat['icon'] }} fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted small text-uppercase tracking-wider d-block">{{ $stat['label'] }}</span>
                                <span class="fw-bold text-dark font-monospace text-truncate d-block">{{ $stat['value'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">

            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-primary"></i>
                        <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase tracking-wider">Flight Log Metrics</h2>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tbody class="fs-6">
                                    <tr class="border-bottom">
                                        <td class="py-2.5 text-muted w-40"><i class="bi bi-calendar3 me-2"></i>Flight Date</td>
                                        <td class="py-2.5 fw-semibold text-dark">{{ $flight->flight_date }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="py-2.5 text-muted"><i class="bi bi-alt me-2"></i>Cruise Altitude</td>
                                        <td class="py-2.5 font-monospace fw-semibold text-dark">{{ number_format($flight->crzalt) }} ft (FL{{ round($flight->crzalt / 100) }})</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="py-2.5 text-muted"><i class="bi bi-fuel-pump me-2"></i>Fuel Burned</td>
                                        <td class="py-2.5 font-monospace fw-semibold text-dark">{{ number_format($flight->burned_fuel) }} {{ session('activeairline')->unit_is_lbs ? 'lbs' : 'kg' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="py-2.5 text-muted"><i class="bi bi-box-arrow-right me-2"></i>Block Off</td>
                                        <td class="py-2.5 font-monospace text-secondary">{{ $flight->blockoff }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2.5 text-muted"><i class="bi bi-box-arrow-in-right me-2"></i>Block On</td>
                                        <td class="py-2.5 font-monospace text-secondary">{{ $flight->blockon }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-primary"></i>
                        <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase tracking-wider">Pilot Information</h2>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0 small">
                                <tbody>
                                    <tr>
                                        <td class="py-2 text-muted">Name</td>
                                        <td class="py-2 fw-semibold text-dark text-end">{{ $flight->pilot_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-muted">Accepted flights</td>
                                        <td class="py-2 fw-semibold text-dark text-end">{{ $flight->pilot ? $flight->pilot->logged_flights($flight->airline) : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-muted">Member since</td>
                                        <td class="py-2 fw-semibold text-dark text-end">{{ $flight->pilot ? $flight->pilot->created_at->format('M j, Y') : '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center gap-2">
                        <i class="bi bi-airplane-fill text-primary"></i>
                        <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase tracking-wider">Aircraft Information</h2>
                    </div>
                    <div class="card-body pt-0">
                        <div class="p-3 bg-light rounded border border-dashed mb-3">
                            <span class="text-muted small d-block mb-1">Registration & Type</span>
                            <h3 class="h5 font-monospace fw-bold text-dark mb-1"><a class="text-decoration-none" href="{{ route('viewaircraft', $flight->aircraft->id) }}">{{ $flight->aircraft->registration }}</a></h3>
                            <p class="text-secondary small mb-0">{{ $flight->aircraft->full_type }}</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0 small">
                                <tbody>
                                    <tr>
                                        <td class="py-2 text-muted">In service since</td>
                                        <td class="py-2 fw-semibold text-dark text-end">{{ $flight->aircraft->in_service_since ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-map text-primary"></i>
                            <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase tracking-wider">ATC Route & Remarks</h2>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                                <i class="bi bi-globe me-1" aria-hidden="true"></i>{{ $flight->online_network?->networkname ?? 'Offline' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase tracking-wider mb-1">Filed ATC Route</label>
                            <div class="p-3 bg-light font-monospace rounded border text-dark fs-6 text-uppercase" style="letter-spacing: 0.5px; line-height: 1.6; overflow-wrap: anywhere;">{{ $flight->route ?: 'No route filed' }}</div>
                        </div>

                        @if($flight->remarks)
                            <div>
                                <label class="form-label text-muted small fw-bold text-uppercase tracking-wider mb-1">Pilot Remarks</label>
                                <div class="p-3 bg-light-subtle rounded border text-body small" style="white-space: pre-line; overflow-wrap: anywhere;">{{ $flight->remarks }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div> </div>
</div>

<style>
    /* Ein kleiner, feiner CSS-Dashed-Border-Effekt für die Route */
    .border-dashed {
        border-style: dashed !important;
    }
    .w-40 {
        width: 40%;
    }

    /* Boarding-pass style route connector in the hero */
    .route-line {
        height: 2px;
        background-image: repeating-linear-gradient(90deg, rgba(255, 255, 255, .45) 0 6px, transparent 6px 13px);
    }
    .route-line::before,
    .route-line::after {
        content: "";
        position: absolute;
        top: 50%;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--bs-primary);
        transform: translateY(-50%);
    }
    .route-line::before { left: 0; }
    .route-line::after  { right: 0; }
    .route-plane {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 0 .6rem;
        background: #1e293b;
        line-height: 1;
    }
</style>
@endsection
