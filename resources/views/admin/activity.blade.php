@extends('layouts.app')
@section('title', 'Activity Log')

@php
    $levelBadges = [
        'debug'   => 'bg-secondary-subtle text-secondary',
        'info'    => 'bg-info-subtle text-info',
        'warning' => 'bg-warning-subtle text-warning',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-journal-text me-2"></i> Activity Log</h1>
        <p class="text-muted mb-0">Instance-wide audit trail of user actions and model changes.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'activity'])

    {{-- Main: activity table --}}
    <div class="col-12 col-lg-9">
        {{-- Level filter --}}
        <form method="get" class="d-flex align-items-center gap-2 mb-3">
            <label for="level" class="form-label mb-0 text-muted small">Filter by level</label>
            <select name="level" id="level" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">All levels</option>
                @foreach ($levels as $lvl)
                    <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                @endforeach
            </select>
            @if ($user)
                <input type="hidden" name="user" value="{{ $user->id }}">
                <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-person"></i> {{ $user->name }}
                    <a href="{{ route('admin.activity.index', array_filter(['level' => $level, 'airline' => $airline?->id])) }}" class="text-primary" aria-label="Remove user filter"><i class="bi bi-x-lg"></i></a>
                </span>
            @endif
            @if ($airline)
                <input type="hidden" name="airline" value="{{ $airline->id }}">
                <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-buildings"></i> {{ $airline->name }}
                    <a href="{{ route('admin.activity.index', array_filter(['level' => $level, 'user' => $user?->id])) }}" class="text-primary" aria-label="Remove airline filter"><i class="bi bi-x-lg"></i></a>
                </span>
            @endif
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Level</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="text-nowrap small text-muted" title="{{ $activity->created_at }}">
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <span class="badge {{ $levelBadges[$activity->level] ?? 'bg-secondary-subtle text-secondary' }}">
                                        {{ ucfirst($activity->level) }}
                                    </span>
                                </td>
                                <td class="small">
                                    {{ $activity->causer?->name ?? '—' }}
                                </td>
                                <td>
                                    {{ $activity->description }}
                                    @if ($activity->event)
                                        <span class="badge bg-light text-muted border ms-1">{{ $activity->event }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if ($activity->subject_type)
                                        {{ \App\Support\ActivityLabel::for($activity->subject) ?? class_basename($activity->subject_type) . ' #' . $activity->subject_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox me-1"></i> No activity recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
