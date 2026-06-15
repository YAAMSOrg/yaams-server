@extends('layouts.app')
@section('title', 'YAAMS: Flight list')

@section('content')
@php
    if (!function_exists('sortUrl')) {
        function sortUrl($column) {
            $currentSort = request('sort_by');
            $currentOrder = request('sort_order', 'asc');
            
            $newOrder = 'asc';
            if ($currentSort === $column) {
                $newOrder = $currentOrder === 'asc' ? 'desc' : 'asc';
            }
            
            return route(request()->route()->getName(), array_merge(request()->query(), [
                'sort_by' => $column,
                'sort_order' => $newOrder,
                'page' => 1
            ]), false);
        }
    }
    
    if (!function_exists('sortIcon')) {
        function sortIcon($column) {
            $currentSort = request('sort_by');
            $currentOrder = request('sort_order', 'asc');
            
            if ($currentSort !== $column) {
                return '<i class="bi bi-arrow-down-up ms-1 text-muted opacity-50 small"></i>';
            }
            
            return $currentOrder === 'asc' 
                ? '<i class="bi bi-arrow-up ms-1 text-primary small"></i>' 
                : '<i class="bi bi-arrow-down ms-1 text-primary small"></i>';
        }
    }
@endphp

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

        <!-- Search Bar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('flightlist') }}" method="GET" class="m-0">
                    @if(request()->has('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request()->has('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 fs-6" placeholder="Search flights by callsign, flight number, airport ICAO, or aircraft registration..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('flightlist', request()->except(['search', 'page'])) }}" class="btn btn-outline-secondary border-start-0 border-end-0 d-flex align-items-center bg-white text-muted">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                        <button class="btn btn-primary px-4 fw-semibold" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($flights->isEmpty())
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="text-warning mb-3">
                        <i class="bi bi-journal-x fs-1"></i>
                    </div>
                    @if(request('search'))
                        <h3 class="h5 fw-bold text-dark">No matching flights</h3>
                        <p class="text-muted mx-auto mb-3" style="max-width: 400px;">We couldn't find any flights matching "{{ request('search') }}". Try adjusting your keywords or clearing the search.</p>
                        <a href="{{ route('flightlist', request()->except(['search', 'page'])) }}" class="btn btn-outline-secondary btn-sm px-4">Clear Search</a>
                    @else
                        <h3 class="h5 fw-bold text-dark">No flights logged yet</h3>
                        <p class="text-muted mx-auto" style="max-width: 400px;">You haven't submitted any PIREPs for this airline. Ready to log your first route?</p>
                        <a href="{{ route('flightadd') }}" class="btn btn-outline-primary btn-sm px-4 mt-2">File a PIREP now</a>
                    @endif
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
                                <th scope="col" class="ps-4">
                                    <a href="{!! sortUrl('id') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        PIREP ID {!! sortIcon('id') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('flightnumber') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        Flight Number {!! sortIcon('flightnumber') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('callsign') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        ATC Callsign {!! sortIcon('callsign') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('route') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        Route {!! sortIcon('route') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('duration') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        Duration {!! sortIcon('duration') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('aircraft') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        Aircraft {!! sortIcon('aircraft') !!}
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{!! sortUrl('date') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                        Date {!! sortIcon('date') !!}
                                    </a>
                                </th>
                                <th scope="col" class="pe-4 text-end">
                                    <a href="{!! sortUrl('status') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-end w-100">
                                        Status {!! sortIcon('status') !!}
                                    </a>
                                </th>
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
                            <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $currentPage - 1]), false) }}" aria-label="Previous">
                                <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                            </a>
                        </li>

                        @for($page = 1; $page <= $maxPages; $page++)
                            <li class="page-item @if($page === $currentPage) active @endif">
                                <a class="page-link border-0 px-3 py-2 fw-semibold @if($page === $currentPage) text-white bg-primary @else text-secondary @endif" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $page]), false) }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endfor

                        <li class="page-item @if($currentPage >= $maxPages) disabled @endif">
                            <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $currentPage + 1]), false) }}" aria-label="Next">
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