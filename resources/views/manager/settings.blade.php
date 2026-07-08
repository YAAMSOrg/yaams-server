@extends('layouts.app')

@section('title', 'Airline Dashboard — YAAMS')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-speedometer2 me-2"></i> Airline Dashboard</h1>
        <p class="text-muted mb-0">{{ $airline->name }} ({{ $airline->icao_callsign }})</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: airline settings sections --}}
    @include('manager._sidebar', ['active' => 'operations'])

    {{-- Main: operations form --}}
    <div class="col-12 col-lg-9">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('airline.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold mb-3"><i class="bi bi-sliders me-2 text-primary"></i> Operations</h6>

                    <div class="form-check form-switch mb-1">
                        <input type="hidden" name="require_pirep_review" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="require_pirep_review"
                               name="require_pirep_review" value="1" @checked($airline->require_pirep_review)>
                        <label class="form-check-label fw-semibold" for="require_pirep_review">
                            Require PIREP review
                        </label>
                    </div>
                    <p class="text-muted small mb-4">
                        When enabled, filed PIREPs stay pending until a Dispatcher or Manager accepts or rejects them.
                        When disabled, PIREPs are accepted automatically on filing and no review notifications are sent.
                    </p>

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
    </div>
</div>

@endsection
