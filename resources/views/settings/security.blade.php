@extends('layouts.app')
@section('title', 'YAAMS: Security Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'security'])

    {{-- Main: password form --}}
    <div class="col-12 col-lg-9">
        @if (session('status') === 'password-updated')
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> Your password has been updated.
            </div>
        @endif

        @if ($errors->updatePassword->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->updatePassword->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('user-password.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i> Change password</h6>

                    <div class="mb-4">
                        <label for="current_password" class="form-label">Current password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key text-secondary"></i></span>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">New password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-control"
                                   autocomplete="new-password"
                                   required>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-2"></i> Update password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
