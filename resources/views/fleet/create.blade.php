@extends('layouts.app')
@section('title', 'YAAMS: Add Aircraft')
@section('content')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="display-5 fw-bold mb-1">Add New Aircraft</h1>
            <p class="text-muted mb-0">Register a new aircraft in your fleet with detailed engine variants, configurations, and weights.</p>
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

    <form action="{{ route('createaircraft') }}" method="POST">
        @csrf
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
                                    <input type="text" class="form-control text-uppercase font-monospace" id="registration" name="registration" value="{{ old('registration') }}" maxlength="9" placeholder="e.g. D-EXAM" required>
                                </div>
                                <div class="form-text fs-7">Tail number (e.g., D-EXAM, N172VA)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="current_loc" class="form-label fw-semibold">First Location <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" class="form-control text-uppercase font-monospace" id="current_loc" name="current_loc" value="{{ old('current_loc') }}" minlength="4" maxlength="4" placeholder="e.g. EDDL" required>
                                </div>
                                <div class="form-text fs-7">4-Letter ICAO code of initial hub</div>
                            </div>

                            <div class="col-md-6">
                                <label for="manufacturer" class="form-label fw-semibold">Manufacturer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" list="manufacturers_list" maxlength="100" placeholder="e.g. Boeing" required>
                                <datalist id="manufacturers_list">
                                    @foreach($manufacturers as $manufacturer)
                                        <option value="{{ $manufacturer }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-md-6">
                                <label for="model" class="form-label fw-semibold">Model Variant <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="model" name="model" value="{{ old('model') }}" list="models_list" maxlength="100" placeholder="e.g. 737-800" required>
                                <datalist id="models_list">
                                    @foreach($models as $model)
                                        <option value="{{ $model }}">
                                    @endforeach
                                </datalist>
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
                                <input type="text" class="form-control" id="engine_type" name="engine_type" value="{{ old('engine_type') }}" list="engines_list" maxlength="100" placeholder="e.g. CFM56-7B26" required>
                                <datalist id="engines_list">
                                    @foreach($engines as $engine)
                                        <option value="{{ $engine }}">
                                    @endforeach
                                </datalist>
                                <div class="form-text fs-7">Specific engine variant (Required)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="msn" class="form-label fw-semibold">MSN <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control font-monospace" id="msn" name="msn" value="{{ old('msn') }}" maxlength="6" pattern="[0-9]{1,6}" inputmode="numeric" placeholder="e.g. 29314">
                                <div class="form-text fs-7">Manufacturer Serial Number</div>
                            </div>

                            <div class="col-md-6">
                                <label for="selcal" class="form-label fw-semibold">SELCAL <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control text-uppercase font-monospace" id="selcal" name="selcal" value="{{ old('selcal') }}" maxlength="5" placeholder="e.g. AB-CD">
                                <div class="form-text fs-7">Selective calling code (e.g. AB-CD)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="hex_code" class="form-label fw-semibold">ICAO 24-bit Hex Code <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control text-uppercase font-monospace" id="hex_code" name="hex_code" value="{{ old('hex_code') }}" minlength="6" maxlength="6" placeholder="e.g. 4840D6">
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
                                    <input type="number" class="form-control" id="mtow" name="mtow" value="{{ old('mtow') }}" min="0" max="1000000" placeholder="e.g. 79010">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Takeoff Weight</div>
                            </div>

                            <div class="col-md-4">
                                <label for="mzfw" class="form-label fw-semibold">MZFW <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="mzfw" name="mzfw" value="{{ old('mzfw') }}" min="0" max="1000000" placeholder="e.g. 62730">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Zero Fuel Weight</div>
                            </div>

                            <div class="col-md-4">
                                <label for="mlw" class="form-label fw-semibold">MLW <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="mlw" name="mlw" value="{{ old('mlw') }}" min="0" max="1000000" placeholder="e.g. 66349">
                                    <span class="input-group-text bg-light text-muted">kg</span>
                                </div>
                                <div class="form-text fs-7">Maximum Landing Weight</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Visual Options, Remarks, Actions -->
            <div class="col-lg-4">
                <!-- Card 4: Equipment & Visual Options -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-eye text-muted me-2"></i> Equipment & Visual Options
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-2">
                            <label class="form-label fw-semibold d-block mb-3">Visual Options</label>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="satcom" name="satcom" value="1" {{ old('satcom') ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium ms-2" for="satcom">
                                    SATCOM Antenna
                                </label>
                                <div class="form-text ms-2 text-muted fs-7">Equipped with satellite dome</div>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="winglets" name="winglets" value="1" {{ old('winglets') ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium ms-2" for="winglets">
                                    Wingtip Devices / Winglets
                                </label>
                                <div class="form-text ms-2 text-muted fs-7">Sharklets or winglets installed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Remarks / Description -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
                        <i class="bi bi-chat-left-text text-muted me-2"></i> Remarks
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-0">
                            <label for="remarks" class="form-label fw-semibold">Notes / Description</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" maxlength="1000" placeholder="Optional notes, paint schemes, special features, etc...">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Register Aircraft
                        </button>
                        <a href="{{ route('fleetmanager') }}" class="btn btn-outline-secondary py-2.5 text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .fs-7 { font-size: 0.775rem !important; }
</style>
@endsection
