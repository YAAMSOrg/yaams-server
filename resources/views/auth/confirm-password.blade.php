@extends('layouts.loginlayout')
@section('title', 'Confirm Password')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-2 fw-bold">Confirm your password</h1>
            <p class="text-muted small mb-0">
                This is a secure area. Please re-enter your password to continue.
            </p>
        </div>

        <form action="{{ route('password.confirm.store') }}" method="post">
            @csrf

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="current-password"
                           autofocus
                           required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-shield-check me-2"></i> Confirm
            </button>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

@endsection
