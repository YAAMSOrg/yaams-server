@extends('layouts.app')
@section('title', 'Administration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-shield-lock me-2"></i> Instance Administration</h1>
        <p class="text-muted mb-0">Manage everything across this YAAMS instance.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'overview'])

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
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['pendingPireps'] }}</div>
                        <div class="text-muted small">Pending PIREPs</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check2-circle fs-2 text-success"></i>
                        <div class="fs-3 fw-bold mt-2">{{ $stats['acceptedFlights'] }}</div>
                        <div class="text-muted small">Accepted Flights</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instance info summary --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-gear me-2"></i> Instance configuration</span>
                <a href="{{ route('admin.settings.edit') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i> Edit settings
                </a>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">Instance name</span>
                    <span class="fw-semibold">{{ $instance['app_name'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">Public registration</span>
                    @if($instance['allow_registration'])
                        <span class="badge bg-success-subtle text-success">Open</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger">Closed</span>
                    @endif
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">Who can found airlines</span>
                    <span class="fw-semibold">{{ $instance['allow_user_airline_creation'] ? 'Any registered user' : 'Admins only' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">Support email</span>
                    <span class="fw-semibold">{{ $instance['support_email'] ?: '—' }}</span>
                </li>
            </ul>
        </div>

        <div class="row g-4 mt-0">
            {{-- Recently registered users --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
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
                                            <span class="text-muted small">{{ $user->created_at->diffForHumans() }}</span>
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

            {{-- Recently founded airlines --}}
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-buildings me-2"></i> Recently founded airlines
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>ICAO</th>
                                    <th>Founded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentAirlines as $airline)
                                    <tr>
                                        <td>{{ $airline->name }}</td>
                                        <td>{{ $airline->icao_callsign }}</td>
                                        <td>
                                            <span class="text-muted small">{{ $airline->created_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No airlines yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
