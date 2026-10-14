@extends('layouts.app')
@section('title', 'Privacy Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'privacy'])

    {{-- Main: privacy & personal data --}}
    <div class="col-12 col-lg-9">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-download me-2 text-primary"></i> Download your data</h6>
                <p class="text-muted">
                    Get a copy of the personal data stored about you in a machine-readable JSON file: your profile,
                    airline memberships, filed flights, uploaded screenshots, notifications, API tokens (names only)
                    and your activity log entries. Passwords and other secrets are never included.
                </p>
                <a href="{{ route('settings.export') }}" class="btn btn-primary">
                    <i class="bi bi-filetype-json me-2"></i> Download my data
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-shield-lock me-2 text-info"></i> Your privacy</h6>
                <ul class="mb-0 ps-3">
                    <li>Read how this instance processes your data in the <a href="{{ route('legal.privacy') }}">privacy policy</a>.</li>
                    <li>Correct your name or e-mail address in your <a href="{{ route('settings.profile') }}">profile</a>.</li>
                    <li>Delete your account and personal data in the <a href="{{ route('settings.danger') }}">danger zone</a>.</li>
                    @if (auth()->user()->legal_accepted_at)
                        <li>You accepted the privacy policy on {{ auth()->user()->legal_accepted_at->toDateString() }}.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
