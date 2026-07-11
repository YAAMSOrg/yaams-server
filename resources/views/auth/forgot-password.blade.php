@extends('layouts.loginlayout')
@section('title', 'Forgot Password')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-3 fw-bold">Forgot your password?</h1>
            <p class="text-muted small mb-0">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <form action="{{ route('password.email') }}" method="post">
            @csrf

            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4">
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

            <button class="btn btn-primary w-100 mb-3" type="submit">
                <i class="bi bi-envelope-paper me-2"></i> Email password reset link
            </button>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-0 text-muted small">Remembered it? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Back to sign in</a></p>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

@endsection
