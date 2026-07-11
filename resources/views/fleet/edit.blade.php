@extends('layouts.app')
@section('title', 'Edit Aircraft ' . $aircraft->registration)
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

    <form action="{{ route('editaircraft', $aircraft->id) }}" method="POST">
        @csrf
        <input type="hidden" id="used_by" name="used_by" value="{{ session('activeairline')->id }}" required>

        <div class="row g-4">
            <!-- Left Column: Fields -->
            <div class="col-lg-8">
                <!-- Card 1: Core Data -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-airplane-fill text-muted me-2"></i> Core Aircraft Information
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="registration" class="form-label fw-semibold">Registration <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control text-uppercase font-monospace" id="registration" name="registration" value="{{ old('registration', $aircraft->registration) }}" maxlength="9" placeholder="e.g. D-EXAM" required>
                                </div>
                                <div class="form-text fs-7">Tail number (e.g., D-EXAM, N172VA)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="manufacturer" class="form-label fw-semibold">Manufacturer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer', $aircraft->manufacturer) }}" maxlength="100" placeholder="e.g. Boeing" required>
                            </div>

                            <div class="col-md-12">
                                <label for="model" class="form-label fw-semibold">Model Variant <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $aircraft->model) }}" maxlength="100" placeholder="e.g. 737-800" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Engine & Technical Codes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-cpu-fill text-muted me-2"></i> Engine & Technical Codes
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="engine_type" class="form-label fw-semibold">Engine Variant <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="engine_type" name="engine_type" value="{{ old('engine_type', $aircraft->engine_type) }}" maxlength="100" placeholder="e.g. CFM56-7B26" required>
                                <div class="form-text fs-7">Specific engine variant (Required)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="msn" class="form-label fw-semibold">MSN <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control font-monospace" id="msn" name="msn" value="{{ old('msn', $aircraft->msn) }}" maxlength="6" pattern="[0-9]{1,6}" inputmode="numeric" placeholder="e.g. 29314">
                                <div class="form-text fs-7">Manufacturer Serial Number</div>
                            </div>

                            <div class="col-md-6">
                                <label for="selcal" class="form-label fw-semibold">SELCAL <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control text-uppercase font-monospace" id="selcal" name="selcal" value="{{ old('selcal', $aircraft->selcal) }}" maxlength="5" placeholder="e.g. AB-CD">
                                <div class="form-text fs-7">Selective calling code (e.g. AB-CD)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="hex_code" class="form-label fw-semibold">ICAO 24-bit Hex Code <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control text-uppercase font-monospace" id="hex_code" name="hex_code" value="{{ old('hex_code', $aircraft->hex_code) }}" minlength="6" maxlength="6" placeholder="e.g. 4840D6">
                                <div class="form-text fs-7">Transponder mode-S address (6 hex characters)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Performance Weights -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-speedometer text-muted me-2"></i> Performance Weights
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="mtow" class="form-label fw-semibold">MTOW <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="mtow" name="mtow" value="{{ old('mtow', $aircraft->mtow) }}" min="0" max="1000000" placeholder="e.g. 79010">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Takeoff Weight</div>
                            </div>

                            <div class="col-md-4">
                                <label for="mzfw" class="form-label fw-semibold">MZFW <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="mzfw" name="mzfw" value="{{ old('mzfw', $aircraft->mzfw) }}" min="0" max="1000000" placeholder="e.g. 62730">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Zero Fuel Weight</div>
                            </div>

                            <div class="col-md-4">
                                <label for="mlw" class="form-label fw-semibold">MLW <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="mlw" name="mlw" value="{{ old('mlw', $aircraft->mlw) }}" min="0" max="1000000" placeholder="e.g. 66349">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Landing Weight</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Visual Options, Status, Remarks, Actions -->
            <div class="col-lg-4">
                <!-- Card 4: Equipment & Operational Status -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-eye text-muted me-2"></i> Equipment & Status
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-block mb-3">Operational Status</label>
                            <div class="form-check form-switch form-switch-md mb-4">
                                <input class="form-check-input" type="checkbox" name="active" role="switch" id="active" value="1"
                                    {{ old('active', $aircraft->active) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium ms-2" for="active" id="statusLabel">
                                    {{ old('active', $aircraft->active) == 1 ? 'Aircraft active & in service' : 'Aircraft inactive' }}
                                </label>
                            </div>
                        </div>

                        <hr class="text-muted">

                        <div class="mb-2">
                            <label class="form-label fw-semibold d-block mb-3">Visual Options</label>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="satcom" name="satcom" value="1" {{ old('satcom', $aircraft->satcom) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium ms-2" for="satcom">
                                    SATCOM Antenna
                                </label>
                                <div class="form-text ms-2 text-muted fs-7">Equipped with satellite dome</div>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="winglets" name="winglets" value="1" {{ old('winglets', $aircraft->winglets) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium ms-2" for="winglets">
                                    Wingtip Devices / Winglets
                                </label>
                                <div class="form-text ms-2 text-muted fs-7">Sharklets or winglets installed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Current Location (Read-Only) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-muted me-2"></i> Current Location
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill text-danger fs-4 me-3"></i>
                                <div>
                                    @if(is_null($aircraft->current_loc))
                                        <span class="badge bg-warning text-dark fs-6 px-2 py-1 mb-1">
                                            No Location
                                        </span>
                                        <small class="d-block text-muted">Awaiting first flight log</small>
                                    @else
                                        <span class="badge bg-dark font-monospace fs-6 px-2 py-1 mb-1" title="{{ $aircraft->location->name }}">
                                            {{ $aircraft->location->icao_code }}
                                        </span>
                                        <small class="d-block text-muted">{{ $aircraft->location->name }}</small>
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-8 uppercase tracking-wider px-2 py-1">
                                <i class="bi bi-lock-fill me-1"></i> Admin Only
                            </span>
                        </div>
                        <div class="form-text mt-2 fs-7 text-muted">Current location updates dynamically via pilots' flight logs.</div>
                    </div>
                </div>

                <!-- Card 6: Remarks / Description -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-chat-left-text text-muted me-2"></i> Remarks
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-0">
                            <label for="remarks" class="form-label fw-semibold">Notes / Description</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" maxlength="1000" placeholder="Optional notes, paint schemes, special features, etc...">{{ old('remarks', $aircraft->remarks) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Save Changes
                        </button>
                        <a href="{{ route('fleetmanager') }}" class="btn btn-outline-secondary py-2.5 text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone: permanent, irreversible retirement -->
    <div class="card border-danger shadow-sm mt-2">
        <div class="card-header bg-white py-3 fw-bold border-bottom text-danger d-flex align-items-center">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> Danger Zone
        </div>
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="fw-semibold">Retire this aircraft</div>
                <div class="text-muted fs-7 mb-0">
                    Permanently removes the aircraft from the fleet. It can no longer fly and
                    <strong>cannot be reactivated</strong>. Its flight history is preserved.
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#retireModal">
                <i class="bi bi-archive me-1"></i> Retire Aircraft
            </button>
        </div>
    </div>

    <!-- Retire confirmation modal -->
    <div class="modal fade" id="retireModal" tabindex="-1" aria-labelledby="retireModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('retireaircraft', $aircraft->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="retireModalLabel">Retire {{ $aircraft->registration }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>This action is <strong>permanent</strong> and cannot be undone.</div>
                    </div>
                    <div class="mb-0">
                        <label for="retired_reason" class="form-label fw-semibold">Reason for retirement</label>
                        <textarea class="form-control @error('retired_reason') is-invalid @enderror" id="retired_reason" name="retired_reason" rows="3" maxlength="255" required placeholder="e.g. Sold, scrapped, damaged beyond repair...">{{ old('retired_reason') }}</textarea>
                        @error('retired_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-archive me-1"></i> Retire Aircraft
                    </button>
                </div>
            </form>
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

    @error('retired_reason')
        // Re-open the retire modal so the validation error is visible.
        new bootstrap.Modal(document.getElementById('retireModal')).show();
    @enderror
</script>

<style>
    .form-switch-md .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }
    .fs-7 { font-size: 0.775rem !important; }
    .fs-8 { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>
@endsection