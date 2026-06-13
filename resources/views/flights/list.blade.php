@extends('layouts.app')
@section('title', 'YAAMS: Flight list')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold text-dark">My Flights</h1>
                <p class="text-muted mb-0">
                    Showing filed flights for your active airline: 
                    <span class="badge bg-secondary-subtle text-secondary fw-semibold border border-secondary-secondary font-monospace">{{ session('activeairline')->name }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('flightadd') }}" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i> File PIREP
                </a>
            </div>
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

        @if ($flights->isEmpty())
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="text-warning mb-3">
                        <i class="bi bi-journal-x fs-1"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark">No flights logged yet</h3>
                    <p class="text-muted mx-auto" style="max-width: 400px;">You haven't submitted any PIREPs for this airline. Ready to log your first route?</p>
                    <a href="{{ route('flightadd') }}" class="btn btn-outline-primary btn-sm px-4 mt-2">File a PIREP now</a>
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h2 class="h6 mb-0 fw-bold text-secondary text-uppercase tracking-wider">Flight Log History</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom text-muted small text-uppercase">
                            <tr>
                                <th scope="col" class="ps-4">PIREP ID</th>
                                <th scope="col">Flight Number</th>
                                <th scope="col">ATC Callsign</th>
                                <th scope="col">Route</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Aircraft</th>
                                <th scope="col">Date</th>
                                <th scope="col" class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flights as $flight)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('viewflight', $flight->id) }}" class="fw-bold text-decoration-none font-monospace">
                                        #{{ $flight->id }}
                                    </a>
                                </td>
                                
                                <td class="fw-semibold text-dark font-monospace">
                                    {{ $flight->full_flight_number }}
                                </td>
                                
                                <td class="text-secondary font-monospace">
                                    {{ $flight->full_icao_callsign }}
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center gap-2 font-monospace fs-6">
                                        <abbr title="{{ $flight->departure_airport->name }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-1 rounded border">{{ $flight->departure_airport->icao_code }}</abbr>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <abbr title="{{ $flight->arrival_airport->name }}" class="text-decoration-none text-dark fw-bold bg-light px-2 py-1 rounded border">{{ $flight->arrival_icao }}</abbr>
                                    </div>
                                </td>
                                
                                <td class="text-muted">
                                    <i class="bi bi-clock me-1 small"></i>{{ $flight->flight_duration }}
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace py-1.5 px-2" data-bs-toggle="tooltip" title="{{ $flight->aircraft->full_type }}">
                                        <i class="bi bi-airplane small me-1 text-secondary"></i>{{ $flight->aircraft->registration }}
                                    </span>
                                </td>
                                
                                <td class="text-secondary small">
                                    {{ $flight->flight_date }}
                                </td>
                                
                                <td class="pe-4 text-end">
                                    @php
                                        // Case-insensitive check for different states
                                        $statusName = strtolower($flight->status->name);
                                        $badgeClass = 'bg-secondary';
                                        $icon = 'bi-circle';

                                        if (str_contains($statusName, 'accept') || str_contains($statusName, 'approv')) {
                                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            $icon = 'bi-check-circle-fill';
                                        } elseif (str_contains($statusName, 'pend') || str_contains($statusName, 'review')) {
                                            $badgeClass = 'bg-warning-subtle text-warning-heading border border-warning-subtle';
                                            $icon = 'bi-hourglass-split';
                                        } elseif (str_contains($statusName, 'reject') || str_contains($statusName, 'deni')) {
                                            $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            $icon = 'bi-x-circle-fill';
                                        }
                                    @endphp
                                    <span class="badge rounded-pill {{ $badgeClass }} px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold small">
                                        <i class="bi {{ $icon }}"></i> {{ $flight->status->name }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($maxPages > 1)
                <nav aria-label="Flight list pagination">
                    <ul class="pagination justify-content-center shadow-sm d-inline-flex border rounded-3 overflow-hidden">
                        
                        <li class="page-item @if($currentPage <= 1) disabled @endif">
                            <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', ['page' => $currentPage - 1], false) }}" aria-label="Previous">
                                <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                            </a>
                        </li>

                        @for($page = 1; $page <= $maxPages; $page++)
                            <li class="page-item @if($page === $currentPage) active @endif">
                                <a class="page-link border-0 px-3 py-2 fw-semibold @if($page === $currentPage) text-white bg-primary @else text-secondary @endif" href="{{ route('flightlist', ['page' => $page], false) }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endfor

                        <li class="page-item @if($currentPage >= $maxPages) disabled @endif">
                            <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', ['page' => $currentPage + 1], false) }}" aria-label="Next">
                                <span aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            @endif
        @endif
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection