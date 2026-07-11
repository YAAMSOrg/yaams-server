@extends('layouts.app')
@section('title', 'Danger Zone')

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
                    undone. Flights you have filed stay on record for your airline(s) but are
                    anonymized - your name is replaced with "Deleted pilot".
                </p>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash me-2"></i> Delete account
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete account confirmation --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('settings.destroy') }}">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i> Delete account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        This permanently deletes your account and cannot be undone. Enter your password
                        to confirm.
                    </p>
                    <div class="mb-2">
                        <label for="delete-account-password" class="form-label">Password</label>
                        <input type="password" name="password"
                               class="form-control @error('password', 'deleteAccount') is-invalid @enderror"
                               id="delete-account-password" required autocomplete="current-password">
                        @error('password', 'deleteAccount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Delete my account
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->deleteAccount->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
    });
</script>
@endif
@endpush
