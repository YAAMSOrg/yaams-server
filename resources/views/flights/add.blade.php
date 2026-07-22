@extends('layouts.app')
@section('title', 'File PIREP')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="bg-primary text-white p-3 rounded-3 shadow-sm">
                <i class="bi bi-airplane-engines fs-3"></i>
            </div>
            <div>
                <h1 class="h3 mb-0 fw-bold">File a PIREP</h1>
                <p class="text-muted mb-0">Manually submit your flight report below.</p>
            </div>
        </div>

        @if($location_continuity)
        <div class="alert alert-info d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-geo-alt-fill"></i>
            <div>
                <strong>Realism mode:</strong> flights must depart from the aircraft's current location.
                The departure field is filled in automatically when you select an aircraft.
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <h4 class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Submission Failed</h4>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{ route('flightadd') }}" method="POST">
            @csrf
            
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge"></i> Pilot & Airline Context
                </div>
                <div class="card-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Pilot ID & Name</label>
                            <div class="input-group">
                                <span class="input-group-text font-monospace">{{ Auth::user()->id }}</span>
                                <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Active Airline</label>
                            <input type="text" class="form-control" value="{{ session('activeairline')->name ?? 'None' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="online_network_id" class="form-label">Online Network</label>
                            <select id="online_network_id" name="online_network_id" class="form-select" required>
                                <option value="" disabled selected>Select Network...</option>
                                @foreach($prefill_online_network as $network)
                                    <option {{ old('online_network_id') == $network->id ? "selected" : "" }} value="{{ $network->id }}">{{ $network->networkname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt"></i> Flight Details
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        
                        <div class="col-md-4">
                            <label for="flightnumber" class="form-label">Flight Number</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ session('activeairline')->prefix }}</span>
                                <input type="text" name="flightnumber" class="form-control text-uppercase font-monospace" id="flightnumber" required maxlength="4" placeholder="1234" value="{{ old('flightnumber') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="callsign" class="form-label">ATC Callsign</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ session('activeairline')->icao_callsign }}</span>
                                <input type="text" class="form-control text-uppercase font-monospace" id="callsign" name="callsign" required maxlength="4" placeholder="443" value="{{ old('callsign') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="aircraft_id" class="form-label">Aircraft</label>
                            <select id="aircraft_id" name="aircraft_id" class="form-select" required>
                                <option value="" disabled selected>Select Aircraft...</option>
                                @foreach($prefill_aircraft as $aircraft)
                                    <option {{ old('aircraft_id') == $aircraft->id ? "selected" : "" }} value="{{ $aircraft->id }}" data-location="{{ $aircraft->current_loc }}">
                                        {{ $aircraft->registration }} ({{ $aircraft->full_type }}){{ $location_continuity ? ' @ ' . $aircraft->current_loc : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="departure" class="form-label">Departure ICAO</label>
                            <input type="text" name="departure_icao" class="form-control text-uppercase font-monospace fs-5" id="departure" required placeholder="EDDK" minlength="4" maxlength="4" value="{{ old('departure_icao') }}" @if($location_continuity) readonly @endif>
                        </div>
                        <div class="col-md-4">
                            <label for="arrival" class="form-label">Arrival ICAO</label>
                            <input type="text" name="arrival_icao" class="form-control text-uppercase font-monospace fs-5" id="arrival" required placeholder="EDDM" minlength="4" maxlength="4" value="{{ old('arrival_icao') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="crzalt" class="form-label">Cruise Altitude (FT)</label>
                            <input type="number" class="form-control font-monospace" step="1000" min="0" max="60000" name="crzalt" id="crzalt" required placeholder="33000" value="{{ old('crzalt') }}">
                        </div>

                        <div class="col-12">
                            <label for="route" class="form-label">Flight Route</label>
                            <textarea class="form-control text-uppercase font-monospace" name="route" id="route" rows="2" placeholder="KUMIK Y854 BOMBI T104 ROKIL" required>{{ old('route') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i> Block Times & Fuel
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="blockoff" class="form-label">Block Off (UTC)</label>
                            <input type="datetime-local" class="form-control" required name="blockoff" id="blockoff" value="{{ old('blockoff') }}" data-prefill-utc>
                        </div>
                        <div class="col-md-4">
                            <label for="blockon" class="form-label">Block On (UTC)</label>
                            <input type="datetime-local" class="form-control" required name="blockon" id="blockon" value="{{ old('blockon') }}" data-prefill-utc>
                        </div>
                        <div class="col-md-4">
                            @php $isLbs = session('activeairline')->unit_is_lbs; @endphp
                            <label for="burned_fuel" class="form-label">Burned Fuel ({{ $isLbs ? 'LBS' : 'KG' }})</label>
                            <input type="number" class="form-control font-monospace" min="1" placeholder="{{ $isLbs ? '36000' : '5900' }}" required name="burned_fuel" id="burned_fuel" value="{{ old('burned_fuel') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text"></i> Additional Remarks
                </div>
                <div class="card-body">
                    <input type="text" class="form-control" name="remarks" id="remarks" placeholder="Any issues during flight? Landing rate? etc." value="{{ old('remarks') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="reset" class="btn btn-light border px-4">Clear Form</button>
                <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="bi bi-send-fill me-2"></i> Submit PIREP</button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pre-fill Block Off / Block On with today's UTC date + current UTC time
    document.querySelectorAll('[data-prefill-utc]').forEach(function (el) {
        if (!el.value) {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            el.value = `${now.getUTCFullYear()}-${pad(now.getUTCMonth() + 1)}-${pad(now.getUTCDate())}T${pad(now.getUTCHours())}:${pad(now.getUTCMinutes())}`;
        }
    });

    // Uppercase submitted values for ICAO fields (CSS text-transform only affects display)
    ['departure', 'arrival'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => { el.value = el.value.toUpperCase(); });
    });

    @if($location_continuity)
    // Location continuity: departure is locked to the selected aircraft's current location
    const aircraftSelect = document.getElementById('aircraft_id');
    const departureInput = document.getElementById('departure');
    const syncDeparture = function () {
        const opt = aircraftSelect.options[aircraftSelect.selectedIndex];
        if (opt && opt.dataset.location) {
            departureInput.value = opt.dataset.location;
        }
    };
    aircraftSelect.addEventListener('change', syncDeparture);
    syncDeparture();
    @endif

    // Auto-fill callsign from flight number until the user manually edits the callsign
    const flightNumber = document.getElementById('flightnumber');
    const callsign = document.getElementById('callsign');
    if (flightNumber && callsign) {
        let callsignManuallyEdited = !!callsign.value;
        flightNumber.addEventListener('input', function () {
            flightNumber.value = flightNumber.value.toUpperCase();
            if (!callsignManuallyEdited) {
                callsign.value = flightNumber.value;
            }
        });
        callsign.addEventListener('input', function () {
            callsign.value = callsign.value.toUpperCase();
            callsignManuallyEdited = callsign.value !== flightNumber.value;
        });
    }
});
</script>
@endsection