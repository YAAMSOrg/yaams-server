@extends('layouts.loginlayout')
@section('title', 'YAAMS Login')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-3 fw-bold">Sign in to YAAMS</h1>
        </div>

        <form action="{{ route('login') }}" method="post">
            @csrf

            @if(session('status'))
                <div class="alert alert-danger" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="name@example.com" required autofocus value="{{ old('email') }}">
                </div>
                @error('email')
                    <div class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password" name="password" id="password" placeholder="Enter password" class="form-control @error('password') is-invalid @enderror" required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label class="form-check-label text-muted small" for="remember">
                    Remember me on this device
                </label>
            </div>

            <button class="btn btn-primary w-100 mb-3" type="submit">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign in
            </button>
            
            <a href="#" class="btn btn-outline-secondary w-100 btn-sm">
                Forgot password?
            </a>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-0 text-muted small">New pilot? <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Register here</a></p>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

@endsection
