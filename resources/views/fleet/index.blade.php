@extends('layouts.app')
@section('title', 'Fleet Overview')

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
    <div class="col-xl-11 col-lg-12">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark tracking-tight mb-1">Fleet Overview</h1>
                <p class="text-muted lead mb-0 fs-6">Monitor all operational aircraft and their current hubs based on recent flight logs.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['show_retired' => $showRetired ? null : 1, 'page' => 1]) }}"
                   class="btn btn-outline-secondary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-archive"></i> {{ $showRetired ? 'Hide retired' : 'Show retired' }}
                </a>
                @if(session('activeairline') && auth()->user()->isManagerOf(session('activeairline')))
                    <a href="{{ route('createaircraft') }}" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-plus-circle"></i> Add Aircraft
                    </a>
                @endif
            </div>
        </div>

        <!-- Fleet Locations Map (collapsed by default to save vertical space) -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center justify-content-between"
                 role="button" data-bs-toggle="collapse" data-bs-target="#fleetMapCollapse"
                 aria-expanded="false" aria-controls="fleetMapCollapse" style="cursor: pointer;">
                <span><i class="bi bi-geo-alt-fill text-danger me-2"></i> Fleet Locations</span>
                <i class="bi bi-chevron-down fleet-map-chevron text-muted"></i>
            </div>
            <div class="collapse" id="fleetMapCollapse">
                @if(count($mapMarkers) === 0)
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-map fs-1 d-block mb-2 opacity-50"></i>
                        No aircraft with a known location yet.
                    </div>
                @else
                    <x-maps-leaflet id="fleetMap"
                        style="height: 380px; width: 100%;"
                        :markers="$mapMarkers"
                        :centerPoint="$mapCenter"
                        :zoomLevel="$mapZoom"></x-maps-leaflet>
                    @if($aircraftWithoutLocation > 0)
                        <div class="card-footer bg-light border-top text-muted small py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ $aircraftWithoutLocation }} {{ \Illuminate\Support\Str::plural('aircraft', $aircraftWithoutLocation) }} {{ $aircraftWithoutLocation === 1 ? 'has' : 'have' }} no known location and {{ $aircraftWithoutLocation === 1 ? 'is' : 'are' }} not shown.
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <h4 class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Error during request</h4>
            <ul class="mb-0 small">
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
                <form action="{{ route('fleetmanager') }}" method="GET" class="m-0">
                    @if(request()->has('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request()->has('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif
                    @if($showRetired)
                        <input type="hidden" name="show_retired" value="1">
                    @endif
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 fs-6" placeholder="Search aircraft by registration, manufacturer, variant, or current hub..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('fleetmanager', request()->except(['search', 'page'])) }}" class="btn btn-outline-secondary border-start-0 border-end-0 d-flex align-items-center bg-white text-muted">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                        <button class="btn btn-primary px-4 fw-semibold" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        @if($fleet->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light border-bottom text-secondary small text-uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="py-3 ps-4">
                                        <a href="{!! sortUrl('registration') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                            Tail Number {!! sortIcon('registration') !!}
                                        </a>
                                    </th>
                                    <th scope="col" class="py-3">
                                        <a href="{!! sortUrl('type') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center">
                                            Type {!! sortIcon('type') !!}
                                        </a>
                                    </th>
                                    <th scope="col" class="py-3 text-center">
                                        <a href="{!! sortUrl('current_loc') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-center w-100">
                                            Current Location {!! sortIcon('current_loc') !!}
                                        </a>
                                    </th>
                                    <th scope="col" class="py-3 text-end">
                                        <a href="{!! sortUrl('logged_hours') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-end w-100">
                                            Logged Hours {!! sortIcon('logged_hours') !!}
                                        </a>
                                    </th>
                                    <th scope="col" class="py-3 text-center">
                                        <a href="{!! sortUrl('status') !!}" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-center w-100">
                                            Status {!! sortIcon('status') !!}
                                        </a>
                                    </th>
                                    @if(session('activeairline') && auth()->user()->isManagerOf(session('activeairline')))
                                    <th scope="col" class="py-3 text-end pe-4">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="fs-6">
                                @foreach($fleet as $aircraft)
                                    <tr class="{{ $aircraft->active == 0 ? 'bg-light bg-opacity-50 text-muted' : '' }}">
                                        <td class="py-3 ps-4 fw-bold font-monospace">
                                            <a href="{{ route('viewaircraft', $aircraft->id) }}" class="{{ $aircraft->active == 0 ? 'link-secondary text-decoration-line-through' : 'link-primary text-decoration-none' }}">
                                                {{ $aircraft->registration }}
                                            </a>
                                        </td>
                                        
                                        <td class="py-3">
                                            <span class="fw-semibold text-dark">{{ $aircraft->full_type }}</span>
                                        </td>
                                        
                                        <td class="py-3 text-center">
                                            @if(is_null($aircraft->current_loc))
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small" data-bs-toggle="tooltip" title="Aircraft just got initialized and has no registered flights.">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> No Location
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 font-monospace" data-bs-toggle="tooltip" title="{{ $aircraft->location->name }}">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $aircraft->location->icao_code }}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        <td class="py-3 text-end font-monospace fw-semibold text-dark">
                                            {{ $aircraft->total_flights_hours }} hrs
                                        </td>
 
                                        <td class="py-3 text-center">
                                            @if($aircraft->status === \App\Models\Aircraft::STATUS_ACTIVE)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Active</span>
                                            @elseif($aircraft->isRetired())
                                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1 small">Retired</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small">Inactive</span>
                                            @endif
                                        </td>
                                        
                                        @if(session('activeairline') && auth()->user()->isManagerOf(session('activeairline')))
                                            <td class="py-3 text-end pe-4">
                                                @if($aircraft->isRetired())
                                                    <a href="{{ route('viewaircraft', $aircraft->id) }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1 shadow-xs fs-7">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                @else
                                                    <a href="{{ route('editaircraft', $aircraft->id) }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1 shadow-xs fs-7">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>                    
                        </table>
                    </div>
                </div>

                @if($maxPages > 1)
                <div class="card-footer bg-white border-top-0 py-3">
                    <nav aria-label="Fleet navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                            <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                <a class="page-link rounded" href="{{ route('fleetmanager', array_merge(request()->query(), ['page' => $currentPage - 1]), false) }}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
            
                            @for($page = 1; $page <= $maxPages; $page++)
                                <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                    <a class="page-link rounded" href="{{ route('fleetmanager', array_merge(request()->query(), ['page' => $page]), false) }}">{{ $page }}</a>
                                </li>
                            @endfor
                          
                            <li class="page-item {{ $currentPage >= $maxPages ? 'disabled' : '' }}">
                                <a class="page-link rounded" href="{{ route('fleetmanager', array_merge(request()->query(), ['page' => $currentPage + 1]), false) }}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        @else
            <div class="card border-0 shadow-sm my-5">
                <div class="card-body p-5 text-center">
                    <div class="text-muted mb-3">
                        <i class="bi bi-search fs-1 text-secondary opacity-50"></i>
                    </div>
                    @if(request('search'))
                        <h5 class="fw-bold text-dark mb-1">No Matching Aircraft</h5>
                        <p class="text-secondary small max-w-md mx-auto mb-3">We couldn't find any aircraft matching "{{ request('search') }}". Try adjusting your search query or clear the search.</p>
                        <a href="{{ route('fleetmanager', request()->except(['search', 'page'])) }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm">
                            Clear Search
                        </a>
                    @else
                        <h5 class="fw-bold text-dark mb-1">No Aircraft Found</h5>
                        <p class="text-secondary small max-w-md mx-auto mb-3">Your airline fleet is currently empty. Get started by registering your first airframe.</p>
                        @if(session('activeairline') && auth()->user()->isManagerOf(session('activeairline')))
                            <a href="{{ route('createaircraft') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                                Add First Aircraft
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>



<style>
    /* Zusätzlicher nützlicher Hilfsstyle für kleinere Controls */
    .fs-7 { font-size: 0.775rem !important; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .max-w-md { max-width: 450px; }
    .mx-auto { margin-left: auto; margin-right: auto; }
    .fleet-map-chevron { transition: transform 0.2s ease; }
    [data-bs-target="#fleetMapCollapse"][aria-expanded="true"] .fleet-map-chevron { transform: rotate(180deg); }
</style>

<script>
    // Tooltips initialisieren, falls Bootstrap Tooltips genutzt werden
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // The fleet map initialises inside a collapsed (hidden) container, so
        // Leaflet lays it out at zero size. Nudge it to recalculate on expand -
        // Leaflet auto-fixes its size on a window resize event.
        var fleetMapCollapse = document.getElementById('fleetMapCollapse');
        if (fleetMapCollapse) {
            fleetMapCollapse.addEventListener('shown.bs.collapse', function () {
                window.dispatchEvent(new Event('resize'));
            });
        }
    });
</script>
@endsection