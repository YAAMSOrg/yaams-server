@extends('layouts.loginlayout')
@section('title', 'Register as pilot')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-3 fw-bold">Register as a new pilot</h1>
        </div>

        <form action="{{ route('register') }}" method="post">
            @csrf

            @if(session('status'))
                <div class="alert alert-danger" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="John Doe" required value="{{ old('name') }}">
                </div>
                @error('name')
                    <div class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="name@example.com" required value="{{ old('email') }}">
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
                    <input type="password" name="password" id="password" placeholder="Create password" class="form-control @error('password') is-invalid @enderror" required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repeat password" class="form-control" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 mb-3" type="submit">
                <i class="bi bi-person-plus-fill me-2"></i> Register
            </button>
            <button type="reset" class="btn btn-outline-secondary w-100 btn-sm">
                Clear form
            </button>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-0 text-muted small">Already a pilot? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign in here</a></p>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

@endsection
