@extends('layouts.app')
@section('title', 'YAAMS: File PIREP')
@section('content')

                <h1 class="display-4 mb-4">File a PIREP</h1>
                <p class="lead">Here you can manually file a PIREP. Please fill out all the fields.</p>

                <form action="{{ route('addflight') }}" class="row g-3">
                    @csrf
                    <div class="col-md-1">
                        <label for="pilotid" class="form-label">Pilot ID</label>
                        <p id="pilot_id" style="font-family: 'Courier New', Courier, monospace">{{ Auth::user()->id }}</p>
                    </div>
                    <div class="col-md-4">
                        <label for="pilot" class="form-label">Pilot</label>
                        <p id="pilot_name" style="font-family: 'Courier New', Courier, monospace">{{ Auth::user()->name }}</p>
                    </div>
                    <hr>
                    <h3>Flight details</h3>
                    <div class="col-md-2">
                        <label for="airline" class="form-label">Airline</label>
                        <p id="airline">{{ session('activeairline')->airline->name }}</p>
                    </div>
                    <div class="col-md-2">
                        <label for="flightnumber" class="form-label">Flight number</label>
                        <div class="input-group">
                            <span class="input-group-text" id="flightnumber_prefix">{{ session('activeairline')->airline->prefix }}</span>
                            <input type="text" class="form-control" id="flightnumber" required maxlength="4" minlength="1" placeholder="1234" aria-describedby="basic-addon3 basic-addon4">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="departure" class="form-label">Departure</label>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="departure" required placeholder="EDDK" minlength="4" maxlength="4">
                    </div>
                    <div class="col-md-2">
                        <label for="arrival" class="form-label">Arrival</label>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="arrival" required placeholder="EDDM" minlength="4" maxlength="4">
                    </div>
                    <div class="col-md-4">
                        <label for="aircraft" class="form-label">Aircraft</label>
                        <select id="aircraft" class="form-select" aria-label="Select the aircraft" required>
                            @foreach($prefill_aircraft as $prefill_aircraft_item)
                            <option value="{{ $prefill_aircraft_item->id }}">{{ $prefill_aircraft_item->registration }} ({{ $prefill_aircraft_item->full_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="callsign" class="form-label">Callsign</label>
                        <div class="input-group">
                            <span class="input-group-text" id="callsign_prefix">{{ session('activeairline')->airline->icao_callsign }}</span>
                            <input type="text" class="form-control" style="text-transform:uppercase" id="callsign" required minlength="4" maxlength="7" placeholder="443">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="cruisealt" class="form-label">Cruise altitude</label>
                        <input type="number" class="form-control" min="0" style="text-transform:uppercase" step="1000" id="cruisealt" required minlength="4"  required placeholder="33000" maxlength="7">
                    </div>
                    <div class="col-md-2">
                        <label for="blockofftime" class="form-label">Block off time (Zulu)</label>
                        <input type="datetime-local" class="form-control" required id="blockofftime">
                    </div>
                    <div class="col-md-2">
                        <label for="blockontime" class="form-label">Block on time (Zulu)</label>
                        <input type="datetime-local" class="form-control"  required id="blockontime">
                    </div>
                    <div class="col-md-2">
                        <label for="fuel" class="form-label">Burned Fuel (KG)</label>
                        <input type="number" class="form-control"  min="0" required placeholder="5900"  id="fuel">
                    </div>
                    <div class="col-md-12">
                        <label for="route" class="form-label">Route</label>
                        <textarea class="form-control" style="font-family: monospace; font-size: 18px; text-transform:uppercase" aria-label="With textarea" id="route" placeholder="KUMIK Y854 BOMBI T104 ROKIL" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="network" class="form-label">Online network</label>
                        <select id="network" class="form-select" required aria-label="Select the online network">
                            @foreach($prefill_online_network as $prefill_online_network_item)
                            <option value="{{ $prefill_online_network_item->id }}">{{ $prefill_online_network_item->networkname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" class="form-control" id="remarks" placeholder="Landing was a bit hard ... " >
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">File PIREP</button>
                        <button type="reset" class="btn btn-secondary">Clear fields</button>
                    </div>
                </form>
            </div>
        </div>
@endsection