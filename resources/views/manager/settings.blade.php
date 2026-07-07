@extends('layouts.app')

@section('title', 'Airline Settings — YAAMS')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Airline Settings</h2>
        <p class="text-muted mb-0 small">{{ $airline->name }} ({{ $airline->icao_callsign }})</p>
    </div>
</div>

<div class="card">
    <div class="card-header">Operations</div>
    <div class="card-body">
        <form action="{{ route('airline.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-check form-switch mb-1">
                <input type="hidden" name="location_continuity" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="location_continuity"
                       name="location_continuity" value="1" @checked($airline->location_continuity)>
                <label class="form-check-label fw-semibold" for="location_continuity">
                    Location continuity (realism mode)
                </label>
            </div>
            <p class="text-muted small mb-4">
                When enabled, pilots can only file flights departing from the airport where the aircraft
                currently is. Filing a flight moves the aircraft to the arrival airport; rejecting the PIREP
                moves it back. Managers can still relocate an aircraft manually via the fleet edit page.
            </p>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Settings
            </button>
        </form>
    </div>
</div>

@endsection
