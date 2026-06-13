@extends('layouts.app')
@section('title', 'Edit Aircraft - ' . $aircraft->registration)
@section('content')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="display-5 fw-bold mb-1">Edit Aircraft</h1>
            <p class="text-muted mb-0">Updating details for aircraft <strong class="text-dark">{{ $aircraft->registration }}</strong>. Please fill out all required fields.</p>
        </div>
        <div>
            <a href="{{ route('fleetmanager') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Fleet
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                <strong>Error during request:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-8 col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                    <i class="bi bi-pencil-square text-muted me-2"></i> Aircraft Core Data
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('editaircraft', $aircraft->id) }}" method="POST" class="row g-4">
                        @csrf
                        <input type="hidden" id="used_by" name="used_by" value="{{ session('activeairline')->id }}" required>

                        <div class="col-md-4">
                            <label for="registration" class="form-label fw-semibold">Registration <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-hash"></i></span>
                                <input type="text" class="form-control text-uppercase" id="registration" name="registration" value="{{ old('registration', $aircraft->registration) }}" maxlength="6" placeholder="e.g. D-EAAA" required>
                            </div>
                            <div class="form-text fs-7">Tail number (max. 6 characters)</div>
                        </div>

                        <div class="col-md-4">
                            <label for="manufacturer" class="form-label fw-semibold">Manufacturer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer', $aircraft->manufacturer) }}" maxlength="100" placeholder="e.g. Boeing" required>
                        </div>

                        <div class="col-md-4">
                            <label for="model" class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $aircraft->model) }}" maxlength="100" placeholder="e.g. 737-800" required>
                        </div>

                        <div class="col-12">
                            <hr class="text-muted my-2">
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div>
                                <label for="active" class="form-label fw-semibold d-block mb-2">Operational Status</label>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" name="active" role="switch" id="active" value="1"
                                        {{ old('active', $aircraft->active) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium ms-2" for="active" id="statusLabel">
                                        {{ old('active', $aircraft->active) == 1 ? 'Aircraft active & in service' : 'Aircraft inactive' }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1 text-muted">Current Location</label>
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt-fill text-danger fs-4 me-3"></i>
                                    <div>
                                        <span class="badge bg-dark font-monospace fs-6 px-2 py-1 mb-1" title="{{ $aircraft->location->name }}">
                                            {{ $aircraft->location->icao_code }}
                                        </span>
                                        <small class="d-block text-muted">{{ $aircraft->location->name }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-8 uppercase tracking-wider px-2 py-1">
                                    <i class="bi bi-lock-fill me-1"></i> Admin Only
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="remarks" class="form-label fw-semibold">Remarks / Description</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="Optional notes, log entries, or configurations for this hull...">{{ old('remarks', $aircraft->remarks) }}</textarea>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('fleetmanager') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('active').addEventListener('change', function() {
        const label = document.getElementById('statusLabel');
        if(this.checked) {
            label.textContent = 'Aircraft active & in service';
        } else {
            label.textContent = 'Aircraft inactive';
        }
    });
</script>

<style>
    .form-switch-md .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }
    .fs-7 { font-size: 0.8rem; }
    .fs-8 { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>

@endsection