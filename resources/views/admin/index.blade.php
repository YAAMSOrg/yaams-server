@extends('layouts.app')
@section('title', 'YAAMS: Administration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-shield-lock me-2"></i> Instance Administration</h1>
        <p class="text-muted mb-0">Manage everything across this YAAMS instance.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections (foundation — sections fill in over time) --}}
    <div class="col-12 col-lg-3">
        <div class="card">
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action active">
                    <i class="bi bi-speedometer2 me-2"></i> Overview
                </a>
                <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    <span><i class="bi bi-people me-2"></i> Users</span>
                    <span class="badge bg-secondary-subtle text-secondary">Soon</span>
                </span>
                <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    <span><i class="bi bi-envelope me-2"></i> Emails</span>
                    <span class="badge bg-secondary-subtle text-secondary">Soon</span>
                </span>
                <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    <span><i class="bi bi-gear me-2"></i> Instance Settings</span>
                    <span class="badge bg-secondary-subtle text-secondary">Soon</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Main: overview --}}
    <div class="col-12 col-lg-9">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-2 text-primary"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['users'] }}</div>
                        <div class="text-muted small">Users</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-envelope-exclamation fs-2 text-warning"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['unverified'] }}</div>
                        <div class="text-muted small">Unverified emails</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-buildings fs-2 text-success"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['airlines'] }}</div>
                        <div class="text-muted small">Airlines</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-sliders fs-2 text-secondary"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['settings'] }}</div>
                        <div class="text-muted small">Settings</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-header">Welcome</div>
            <div class="card-body">
                <p class="mb-0 text-muted">
                    This is the administration area. From here you'll be able to view and manage
                    every user, every email and all instance-wide settings. More sections are on the way.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
