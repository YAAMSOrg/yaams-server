@extends('layouts.landing')
@section('title', 'Welcome to YAAMS')

@section('content')

{{-- Hero --}}
<div class="py-5 mb-5">
    <div class="d-flex align-items-center gap-3 mb-3">
        <span style="font-size: 3.5rem; line-height: 1; color: #0d6efd;">
            <i class="bi bi-airplane-fill" style="display: inline-block; transform: rotate(45deg);"></i>
        </span>
        <div>
            <h1 class="display-3 fw-bold mb-0" style="letter-spacing: -0.03em;">YAAMS</h1>
            <p class="text-muted mb-0 fs-6 fw-semibold text-uppercase" style="letter-spacing: 0.08em;">Yet Another Airline Management System</p>
        </div>
    </div>
    <p class="lead text-muted mb-4" style="max-width: 560px;">
        An open-source virtual airline management platform. Track PIREPs, manage fleets,
        and build thriving virtual airline communities — all in one place.
    </p>
    @guest
    <div class="d-flex gap-2 flex-wrap">
        @if(\App\Models\Setting::get('allow_registration', '1') === '1')
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">
            <i class="bi bi-person-plus me-2"></i>Create an Account
        </a>
        @endif
        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </a>
    </div>
    @endguest
    @auth
    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4">
        <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
    </a>
    @endauth
</div>

{{-- Platform Stats --}}
<div class="row g-3 mb-5">
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center py-3">
            <div class="card-body py-2">
                <div class="fs-1 fw-bold text-primary mb-1">{{ $stats['airlines'] }}</div>
                <div class="text-muted small fw-semibold text-uppercase">
                    <i class="bi bi-building me-1"></i>Airlines
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center py-3">
            <div class="card-body py-2">
                <div class="fs-1 fw-bold text-success mb-1">{{ $stats['pilots'] }}</div>
                <div class="text-muted small fw-semibold text-uppercase">
                    <i class="bi bi-people me-1"></i>Pilots
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center py-3">
            <div class="card-body py-2">
                <div class="fs-1 fw-bold text-warning mb-1">{{ number_format($stats['flights']) }}</div>
                <div class="text-muted small fw-semibold text-uppercase">
                    <i class="bi bi-journal-check me-1"></i>Flights Logged
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center py-3">
            <div class="card-body py-2">
                <div class="fs-1 fw-bold text-info mb-1">{{ number_format($stats['hours']) }}</div>
                <div class="text-muted small fw-semibold text-uppercase">
                    <i class="bi bi-clock me-1"></i>Hours Flown
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Airlines Section --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h4 fw-bold mb-0">
        <i class="bi bi-globe-americas text-primary me-2"></i>Virtual Airlines on this Instance
    </h2>
    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
        {{ $stats['airlines'] }} {{ Str::plural('airline', $stats['airlines']) }}
    </span>
</div>

@if($airlines->isEmpty())
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="bi bi-airplane text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="text-muted mt-3 mb-0">No airlines have been set up yet.</p>
            <small class="text-muted">
                Get started by reading the
                <a href="https://github.com/flymia/YAAMS" target="_blank" class="text-decoration-none">YAAMS documentation</a>.
            </small>
        </div>
    </div>
@else
    <div class="row g-3 mb-5">
        @foreach($airlines as $airline)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <span class="badge bg-dark text-white font-monospace fs-6 px-3 py-2 me-2">
                            {{ $airline->icao_callsign }}
                        </span>
                        @if($airline->active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>Active
                            </span>
                        @endif
                    </div>

                    <h5 class="fw-bold mb-1">{{ $airline->name }}</h5>

                    @if($airline->description)
                        <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $airline->description }}
                        </p>
                    @else
                        <p class="text-muted small mb-3 fst-italic">No description provided.</p>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($airline->hub)
                        <span class="badge bg-light text-dark border small">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $airline->hub }}
                        </span>
                        @endif
                        @if($airline->country)
                        <span class="badge bg-light text-dark border small">
                            <i class="bi bi-flag me-1 text-secondary"></i>{{ $airline->country }}
                        </span>
                        @endif
                        @if($airline->atc_callsign)
                        <span class="badge bg-light text-dark border small font-monospace">
                            {{ $airline->atc_callsign }}
                        </span>
                        @endif
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between text-muted small">
                        <span>
                            <i class="bi bi-people me-1"></i>
                            <strong class="text-dark">{{ $airline->users_count }}</strong>
                            {{ Str::plural('pilot', $airline->users_count) }}
                        </span>
                        <span>
                            <i class="bi bi-journal-check me-1"></i>
                            <strong class="text-dark">{{ number_format($airline->flights_count) }}</strong>
                            {{ Str::plural('flight', $airline->flights_count) }}
                        </span>
                        @if($airline->founded_at)
                        <span>
                            <i class="bi bi-calendar3 me-1"></i>Est. {{ $airline->founded_at->format('Y') }}
                        </span>
                        @endif
                    </div>
                </div>

                @if($airline->website)
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                    <a href="{{ $airline->website }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-globe me-1"></i>Visit Website
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- CTA Footer Band --}}
@guest
<div class="card border-0 mb-4" style="background: linear-gradient(135deg, #0d1b2a 0%, #1a3a5c 100%); color: white; border-radius: 1rem;">
    <div class="card-body py-4 px-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <div>
            <h5 class="fw-bold mb-1">Ready to join the virtual skies?</h5>
            <p class="mb-0 text-white-50 small">Create a free account and start filing PIREPs today.</p>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            @if(\App\Models\Setting::get('allow_registration', '1') === '1')
            <a href="{{ route('register') }}" class="btn btn-primary px-4">
                <i class="bi bi-person-plus me-2"></i>Sign Up Free
            </a>
            @endif
            <a href="{{ route('login') }}" class="btn btn-outline-light px-4">Sign In</a>
        </div>
    </div>
</div>
@endguest

@endsection
