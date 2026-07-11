@extends('layouts.app')

@section('title', 'Found a New Airline')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="text-center mb-5">
            <i class="bi bi-building-fill-add display-3 text-primary opacity-75"></i>
            <h2 class="fw-bold mt-3">Found a New Airline</h2>
            <p class="text-muted">You will automatically become the Manager of the new airline.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('airline.found.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="airline_name" class="form-label">Airline name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-building text-secondary"></i></span>
                            <input type="text"
                                   id="airline_name"
                                   name="airline_name"
                                   class="form-control @error('airline_name') is-invalid @enderror"
                                   placeholder="e.g. My Virtual Airline"
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
                                       placeholder="VA"
                                       maxlength="2"
                                       oninput="this.value = this.value.toUpperCase()"
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
                                       placeholder="MVA"
                                       maxlength="3"
                                       oninput="this.value = this.value.toUpperCase()"
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
                                       placeholder="VIRTUAL"
                                       maxlength="10"
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

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="airline_hub" class="form-label">Main hub <span class="text-muted fw-normal">(ICAO)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt text-secondary"></i></span>
                                <input type="text"
                                       id="airline_hub"
                                       name="airline_hub"
                                       class="form-control text-uppercase @error('airline_hub') is-invalid @enderror"
                                       placeholder="EDDF"
                                       maxlength="4"
                                       oninput="this.value = this.value.toUpperCase()"
                                       value="{{ old('airline_hub') }}"
                                       required>
                            </div>
                            @error('airline_hub')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="airline_country" class="form-label">Country <span class="text-muted fw-normal">(ISO 3166-1)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-flag text-secondary"></i></span>
                                <input type="text"
                                       id="airline_country"
                                       name="airline_country"
                                       class="form-control text-uppercase @error('airline_country') is-invalid @enderror"
                                       placeholder="DE"
                                       maxlength="2"
                                       oninput="this.value = this.value.toUpperCase()"
                                       value="{{ old('airline_country') }}"
                                       required>
                            </div>
                            @error('airline_country')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="airline_founded" class="form-label">Founded <span class="text-muted fw-normal">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar text-secondary"></i></span>
                            <input type="date"
                                   id="airline_founded"
                                   name="airline_founded"
                                   class="form-control @error('airline_founded') is-invalid @enderror"
                                   value="{{ old('airline_founded', now()->toDateString()) }}">
                        </div>
                        @error('airline_founded')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="airline_website" class="form-label">Website <span class="text-muted fw-normal">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg text-secondary"></i></span>
                            <input type="url"
                                   id="airline_website"
                                   name="airline_website"
                                   class="form-control @error('airline_website') is-invalid @enderror"
                                   placeholder="https://my-va.example.com"
                                   value="{{ old('airline_website') }}">
                        </div>
                        @error('airline_website')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="airline_desc" class="form-label">Description <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="airline_desc"
                                  name="airline_desc"
                                  class="form-control @error('airline_desc') is-invalid @enderror"
                                  rows="3"
                                  placeholder="A short description of your virtual airline…">{{ old('airline_desc') }}</textarea>
                        @error('airline_desc')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
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

                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-building-fill-add me-2"></i> Found Airline
                    </button>
                </form>

            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('portal') }}" class="text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Back to Portal
            </a>
        </div>

    </div>
</div>

<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el)
    })
</script>
@endsection
