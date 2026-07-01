@extends('layouts.loginlayout')
@section('title', 'Set a new password')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-3 fw-bold">Set a new password</h1>
        </div>

        <form action="{{ route('password.update') }}" method="post">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="name@example.com" required autofocus value="{{ old('email', $request->email) }}">
                </div>
                @error('email')
                    <div class="invalid-feedback d-block mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
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
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repeat password" class="form-control" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 mb-3" type="submit">
                <i class="bi bi-check2-circle me-2"></i> Reset password
            </button>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-0 text-muted small"><a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Back to sign in</a></p>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

@endsection
