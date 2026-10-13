@extends('layouts.app')
@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-people me-2"></i> Users</h1>
        <p class="text-muted mb-0">
            {{ $counts['total'] }} {{ Str::plural('account', $counts['total']) }}
            &middot; {{ $counts['suspended'] }} suspended
            &middot; {{ $counts['unverified'] }} unverified
        </p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'users'])

    {{-- Main: user table --}}
    <div class="col-12 col-lg-9">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="get" class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="q" class="form-label small text-muted mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" id="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Name or e-mail">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="status" class="form-label small text-muted mb-1">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active</option>
                            <option value="suspended" @selected($filters['status'] === 'suspended')>Suspended</option>
                            <option value="unverified" @selected($filters['status'] === 'unverified')>Unverified</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="role" class="form-label small text-muted mb-1">Role</label>
                        <select name="role" id="role" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="super-admin" @selected($filters['role'] === 'super-admin')>Super-Admin</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="tfa" class="form-label small text-muted mb-1">2FA</label>
                        <select name="tfa" id="tfa" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="enabled" @selected($filters['tfa'] === 'enabled')>Enabled</option>
                            <option value="disabled" @selected($filters['tfa'] === 'disabled')>Disabled</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="sort" class="form-label small text-muted mb-1">Sort</label>
                        <select name="sort" id="sort" class="form-select form-select-sm">
                            <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                            <option value="name" @selected($filters['sort'] === 'name')>Name</option>
                            <option value="last_online" @selected($filters['sort'] === 'last_online')>Last online</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Apply</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th class="text-end">Airlines</th>
                            <th class="text-end">Flights</th>
                            <th>Last online</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="fw-semibold text-decoration-none">{{ $user->name }}</a>
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </td>
                                <td>@include('admin.users._badges')</td>
                                <td class="text-end">{{ $user->airlines_count }}</td>
                                <td class="text-end">{{ $user->flights_count }}</td>
                                <td class="small text-muted text-nowrap" title="{{ $user->last_online_at }}">
                                    {{ $user->last_online_at?->diffForHumans() ?? '-' }}
                                </td>
                                <td class="small text-muted text-nowrap" title="{{ $user->created_at }}">
                                    {{ $user->created_at?->diffForHumans() ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox me-1"></i> No users match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
