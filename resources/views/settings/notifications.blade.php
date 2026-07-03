@extends('layouts.app')
@section('title', 'YAAMS: Notification Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'notifications'])

    {{-- Main: notification preferences --}}
    <div class="col-12 col-lg-9">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('settings.notifications.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold mb-3"><i class="bi bi-bell me-2 text-primary"></i> Email notifications</h6>

                    <input type="hidden" name="email_notifications" value="0">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="email_notifications" name="email_notifications" value="1"
                               {{ old('email_notifications', auth()->user()->email_notifications) ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_notifications">
                            Send me email notifications
                        </label>
                    </div>
                    <div class="form-text mb-4">
                        Controls emails for PIREP activity (filed, accepted, rejected). In-app
                        notifications are always kept and are not affected by this setting.
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-2"></i> Save preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
