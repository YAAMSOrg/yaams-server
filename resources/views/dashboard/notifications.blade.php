@extends('layouts.app')
@section('title', 'YAAMS: Notifications')
@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark mb-1">Notifications</h1>
                <p class="text-muted lead mb-0">Stay updated on your airline's activity.</p>
            </div>
            @if (!$notifications->isEmpty())
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                        {{ $notifications->count() }} New
                    </span>
                    <form action="{{ route('notificationsacknowledgeall') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
                            <i class="bi bi-check2-all me-1"></i> Clear all
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm border-0 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger shadow-sm border-0 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if ($notifications->isEmpty())
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-bell-slash text-muted display-1 mb-3 d-block opacity-25"></i>
                    <h3 class="h5 fw-bold text-secondary">No new notifications</h3>
                    <p class="text-muted mb-0">You're all caught up! We'll let you know when something important happens.</p>
                </div>
            </div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach ($notifications as $notification)
                    <div class="card border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 flex-shrink-0" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-info-circle-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $notification->data['title'] }}</h5>
                                        <p class="text-secondary mb-2">{{ $notification->data['message'] }}</p>

                                        @if(!empty($notification->data['url']))
                                            <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary px-3 mb-2 rounded-pill">
                                                <i class="bi bi-eye me-1"></i> View Details
                                            </a>
                                        @endif

                                        <div class="d-block mt-1">
                                            <span class="text-muted small">
                                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <form action="{{ route('notificationsacknowledge', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-muted p-0 border-0 hover-text-danger transition-all" title="Dismiss notification">
                                            <i class="bi bi-x-lg fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .hover-text-danger:hover {
        color: #dc3545 !important;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>

@endsection
