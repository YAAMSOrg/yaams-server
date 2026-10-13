{{-- Read-only notice for members of an airline frozen by an instance admin
     (see EnsureAirlineNotSuspended). The session airline is refreshed by the
     `airline` middleware on every airline-scoped request. --}}
@php($suspendedAirline = auth()->check() ? session('activeairline') : null)
@if($suspendedAirline?->isSuspended())
    <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
        <i class="bi bi-snow2 fs-5 me-2"></i>
        <div>
            <strong>Airline suspended.</strong>
            {{ $suspendedAirline->suspensionMessage() }}
            Filing, reviewing and management actions are disabled until it is reactivated.
        </div>
    </div>
@endif
