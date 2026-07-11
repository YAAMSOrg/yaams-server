@extends('layouts.app')
@section('title', 'Review Flights')
@section('content')

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold text-dark mb-1">Review Flights</h1>
            <p class="text-muted lead mb-0">Here is a list of flights from <span class="fw-semibold text-primary">{{ session('activeairline')->name }}</span> awaiting your validation.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger shadow-sm border-0 mb-4">
        <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span>Error during request:</span>
        </div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success shadow-sm border-0 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger shadow-sm border-0 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    @if ($flights->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-journal-check text-muted display-1 mb-3 d-block"></i>
                <h3 class="h5 fw-bold text-secondary">All caught up!</h3>
                <p class="text-muted mb-0">No open PIREPs require validation for this airline at the moment.</p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold text-secondary"><i class="bi bi-list-task me-2"></i>Pending PIREPs</h2>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">{{ $flights->count() }} open</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th scope="col" class="ps-4">PIREP ID</th>
                            <th scope="col">Flight Number</th>
                            <th scope="col">ATC Callsign</th>
                            <th scope="col">From / To</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Aircraft</th>
                            <th scope="col">Date</th>
                            <th scope="col">Pilot</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flights as $flight)
                        <tr>
                            <td class="ps-4 font-monospace fw-bold">
                                <a href="{{ route('viewflight', $flight->id) }}" class="text-decoration-none">
                                    #{{ $flight->id }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border font-monospace">{{ $flight->full_flight_number }}</span></td>
                            <td class="font-monospace fw-semibold text-secondary">{{ $flight->full_icao_callsign }}</td>
                            <td>
                                <span class="fw-bold" title="{{ $flight->departure_airport->name }}">{{ $flight->departure_airport->icao_code }}</span>
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                <span class="fw-bold" title="{{ $flight->arrival_airport->name }}">{{ $flight->arrival_icao }}</span>
                            </td>
                            <td><i class="bi bi-clock me-1 text-muted"></i>{{ $flight->flight_duration }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" title="{{ $flight->aircraft->full_type }}">
                                    {{ $flight->aircraft->registration }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $flight->flight_date }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person text-secondary"></i>
                                    <span class="fw-medium text-dark">{{ $flight->pilot_name }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    <form action="{{ route('flightreviewaccept', $flight->id) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 shadow-sm px-2.5" title="Approve PIREP">
                                            <i class="bi bi-check-lg"></i> <span class="d-none d-xl-inline">Approve</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('flightreviewreject', $flight->id) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 shadow-sm px-2.5" title="Reject PIREP" onclick="return confirm('Are you sure you want to reject this PIREP?');">
                                            <i class="bi bi-x-lg"></i> <span class="d-none d-xl-inline">Reject</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
