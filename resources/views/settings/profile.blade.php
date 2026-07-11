@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'profile'])

    {{-- Main: profile form --}}
    <div class="col-12 col-lg-9">
        @if (session('status') === 'profile-information-updated')
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> Your profile has been updated.
            </div>
        @endif

        @if ($errors->updateProfileInformation->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->updateProfileInformation->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('user-profile-information.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold mb-3"><i class="bi bi-person me-2 text-primary"></i> Profile</h6>

                    <div class="mb-4">
                        <label for="name" class="form-label">Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person text-secondary"></i></span>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   required>
                        </div>
                        <div class="form-text">Shown across the app and to your fellow crew members.</div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   required>
                        </div>
                        <div class="form-text">
                            Changing your email requires re-verification - we'll send a confirmation link to the new address.
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-2"></i> Save profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
