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
                    <span><i class="bi bi-buildings me-2"></i> Airlines</span>
                    <span class="badge bg-secondary-subtle text-secondary">Soon</span>
                </span>
                <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    <span><i class="bi bi-gear me-2"></i> Instance</span>
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
                        <i class="bi bi-buildings fs-2 text-success"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['airlines'] }}</div>
                        <div class="text-muted small">Airlines</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-journal-check fs-2 text-info"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['flights'] }}</div>
                        <div class="text-muted small">Flights</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-airplane fs-2 text-secondary"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['aircraft'] }}</div>
                        <div class="text-muted small">Aircraft</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-person-plus me-2"></i> Recently registered users
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    {{ $user->created_at->format('Y-m-d H:i') }}
                                    <span class="text-muted small">({{ $user->created_at->diffForHumans() }})</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No users registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
