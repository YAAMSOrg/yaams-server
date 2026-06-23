@extends('layouts.setuplayout')
@section('title', 'YAAMS Setup Wizard')
@section('content')

<div class="text-center mb-4">
    <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="YAAMS Logo" height="60" class="mb-3">
    <h1 class="h4 fw-bold mb-1">Welcome to YAAMS</h1>
    <p class="text-muted small">Complete the setup below to get started.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('setup.store') }}" method="post">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Section 1: Instance --}}
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-globe2"></i>
                </span>
                <h6 class="mb-0 fw-semibold">Your YAAMS Instance</h6>
            </div>

            <div class="mb-3">
                <label for="app_name" class="form-label">Instance name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag text-secondary"></i></span>
                    <input type="text"
                           id="app_name"
                           name="app_name"
                           class="form-control @error('app_name') is-invalid @enderror"
                           placeholder="e.g. FlySimWorld Virtual Airlines"
                           value="{{ old('app_name') }}"
                           required>
                </div>
                @error('app_name')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
                <div class="form-text">Shown in the page title and header across the app.</div>
            </div>

            {{-- Section 2: Airline --}}
            <div class="step-divider">
                <span class="section-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-airplane-fill"></i>
                </span>
                Your First Airline
            </div>

            <div class="mb-3">
                <label for="airline_name" class="form-label">Airline name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building text-secondary"></i></span>
                    <input type="text"
                           id="airline_name"
                           name="airline_name"
                           class="form-control @error('airline_name') is-invalid @enderror"
                           placeholder="e.g. FlySimWorld Airlines"
                           value="{{ old('airline_name') }}"
                           required>
                </div>
                @error('airline_name')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <label for="airline_prefix" class="form-label">IATA prefix <span class="text-muted fw-normal">(2 letters)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-hash text-secondary"></i></span>
                        <input type="text"
                               id="airline_prefix"
                               name="airline_prefix"
                               class="form-control text-uppercase @error('airline_prefix') is-invalid @enderror"
                               placeholder="FW"
                               maxlength="2"
                               value="{{ old('airline_prefix') }}"
                               required>
                    </div>
                    @error('airline_prefix')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-sm-4">
                    <label for="airline_icao" class="form-label">ICAO callsign <span class="text-muted fw-normal">(3 letters)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-broadcast text-secondary"></i></span>
                        <input type="text"
                               id="airline_icao"
                               name="airline_icao"
                               class="form-control text-uppercase @error('airline_icao') is-invalid @enderror"
                               placeholder="FSW"
                               maxlength="3"
                               value="{{ old('airline_icao') }}"
                               required>
                    </div>
                    @error('airline_icao')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-sm-4">
                    <label for="airline_callsign" class="form-label">ATC callsign</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-mic text-secondary"></i></span>
                        <input type="text"
                               id="airline_callsign"
                               name="airline_callsign"
                               class="form-control text-uppercase @error('airline_callsign') is-invalid @enderror"
                               placeholder="FLYSIM"
                               maxlength="10"
                               style="text-transform: uppercase"
                               oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '')"
                               value="{{ old('airline_callsign') }}"
                               data-bs-toggle="tooltip"
                               data-bs-trigger="focus"
                               data-bs-placement="top"
                               title="Letters only, 2–10 chars (e.g. SPEEDBIRD)"
                               required>
                    </div>
                    @error('airline_callsign')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Weight unit</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="unit_is_lbs" id="unit_kg" value="0"
                           {{ old('unit_is_lbs', '0') === '0' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary" for="unit_kg">
                        <i class="bi bi-thermometer-half me-1"></i> Kilograms (kg)
                    </label>

                    <input type="radio" class="btn-check" name="unit_is_lbs" id="unit_lbs" value="1"
                           {{ old('unit_is_lbs') === '1' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary" for="unit_lbs">
                        <i class="bi bi-thermometer-half me-1"></i> Pounds (lbs)
                    </label>
                </div>
            </div>

            {{-- Section 3: Admin account --}}
            <div class="step-divider">
                <span class="section-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-person-badge-fill"></i>
                </span>
                Admin Account
            </div>

            <div class="mb-3">
                <label for="admin_name" class="form-label">Full name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text"
                           id="admin_name"
                           name="admin_name"
                           class="form-control @error('admin_name') is-invalid @enderror"
                           placeholder="John Doe"
                           value="{{ old('admin_name') }}"
                           required>
                </div>
                @error('admin_name')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="admin_email" class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email"
                           id="admin_email"
                           name="admin_email"
                           class="form-control @error('admin_email') is-invalid @enderror"
                           placeholder="admin@example.com"
                           value="{{ old('admin_email') }}"
                           required>
                </div>
                @error('admin_email')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="admin_password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password"
                           id="admin_password"
                           name="admin_password"
                           class="form-control @error('admin_password') is-invalid @enderror"
                           placeholder="Min. 8 characters"
                           required>
                </div>
                @error('admin_password')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="admin_password_confirmation" class="form-label">Confirm password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                    <input type="password"
                           id="admin_password_confirmation"
                           name="admin_password_confirmation"
                           class="form-control"
                           placeholder="Repeat password"
                           required>
                </div>
            </div>

            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-check2-circle me-2"></i> Complete Setup
            </button>
        </form>

    </div>
</div>

<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el)
    })
</script>

@endsection
