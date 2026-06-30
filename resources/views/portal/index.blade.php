@extends('layouts.app')

@section('title', 'Airline Portal — YAAMS')

@section('content')
@php($emailVerified = auth()->user()->hasVerifiedEmail())
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Verification link resent confirmation --}}
        @if(session('status') === 'verification-link-sent')
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-envelope-check me-2"></i>A new verification link has been sent to your email address.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Email not verified — portal actions are blocked until confirmed --}}
        @unless($emailVerified)
            <div class="alert alert-warning mb-4" role="alert">
                <h5 class="alert-heading d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-exclamation"></i> Please verify your email address
                </h5>
                <p class="mb-2">
                    We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
                    You need to confirm your email before you can join or found an airline.
                </p>
                <p class="mb-3 small text-muted">
                    Didn't get the email? Check your spam folder, or resend it below. If it still
                    doesn't arrive, please contact the administrator of this instance.
                </p>
                <form action="{{ route('verification.send') }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Resend verification email
                    </button>
                </form>
            </div>
        @endunless

        {{-- Header --}}
        <div class="text-center mb-5">
            <i class="bi bi-building-fill-check display-3 text-primary opacity-75"></i>
            <h2 class="fw-bold mt-3">Airline Portal</h2>
            @if($airlines->isEmpty())
                <p class="text-muted">You are not a member of any airline yet. Enter an invite code to join one.</p>
            @else
                <p class="text-muted">Your airline memberships and invite code redemption.</p>
            @endif
        </div>

        {{-- Current memberships --}}
        @unless($airlines->isEmpty())
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-buildings text-secondary"></i>
                    Your Airlines
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($airlines as $airline)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="fw-semibold">{{ $airline->name }}</span>
                                <span class="text-muted ms-1 small">/ {{ $airline->icao_callsign }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    {{ $airline->pivot->role ?? 'Pilot' }}
                                </span>
                                <form action="{{ route('changeactiveairline') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="airline_id" value="{{ $airline->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary" @disabled(!$emailVerified)>
                                        <i class="bi bi-box-arrow-in-right"></i> Switch
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endunless

        {{-- Found a new airline --}}
        @if($emailVerified && (auth()->user()->hasRole('Super-Admin') || \App\Models\Setting::get('allow_user_airline_creation') === '1'))
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-building-fill-add text-secondary"></i>
                    Found a New Airline
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Create your own airline and become its first Manager.</p>
                    <a href="{{ route('airline.found') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-building-fill-add me-1"></i> Found an Airline
                    </a>
                </div>
            </div>
        @endif

        {{-- Invite code form (hidden until email is verified) --}}
        @if($emailVerified)
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-ticket-perforated text-secondary"></i>
                Join an Airline
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">Enter the invite code you received from an airline manager.</p>

                <form action="{{ route('portal.redeem') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Invite Code</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-control font-monospace text-uppercase @error('code') is-invalid @enderror"
                            placeholder="e.g. DLH-4918"
                            value="{{ old('code') }}"
                            autocomplete="off"
                            maxlength="20"
                        >
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Redeem Code
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
