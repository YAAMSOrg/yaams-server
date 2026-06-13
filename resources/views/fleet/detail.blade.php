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
                    <i class="bi bi-card-text text-muted me-2"></i> Technical Specifications & Details
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-4 py-3" style="width: 35%;">In Service Since</td>
                                <td class="fw-medium pe-4">{{ \Carbon\Carbon::parse($aircraft->created_at)->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-4 py-3">Remarks / Description</td>
                                <td class="pe-4 italic text-muted">
                                    {{ $aircraft->remarks ? $aircraft->remarks : 'No remarks specified for this aircraft.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
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