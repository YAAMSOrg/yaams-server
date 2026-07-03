@extends('layouts.app')
@section('title', 'YAAMS: Danger Zone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'danger'])

    {{-- Main: danger zone --}}
    <div class="col-12 col-lg-9">
        <div class="card border-danger">
            <div class="card-header bg-danger-subtle text-danger fw-semibold">
                <i class="bi bi-exclamation-triangle me-2"></i> Danger zone
            </div>
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-1">Delete account</h6>
                <p class="text-muted mb-3">
                    Permanently delete your account and all associated data. This action cannot be
                    undone.
                </p>
                <button type="button" class="btn btn-outline-danger" disabled>
                    <i class="bi bi-trash me-2"></i> Delete account
                </button>
                <div class="form-text">Coming soon - account deletion is not yet available.</div>
            </div>
        </div>
    </div>
</div>
@endsection
