@extends('layouts.app')
@section('title', 'YAAMS: File PIREP')
@section('content')
        <div class="container" >
            <h1 class="display-2">File a PIREP</h1>
            <p class="lead">Here you can manually file a PIREP. Please fill out all the fields.</p>
                <form action="{{ route('addflight') }}" class="row g-3">
                    @csrf
                    <div class="col-md-2">
                        <label for="airline" class="form-label">Airline</label>
                        <select id="airline" class="form-select" aria-label="Select the airline">
                            <option value="1">LH - Lufthansa Virtual</option>
                            <option value="2">4U - Germanwings Virtual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="flightnumber" class="form-label">Flight number</label>
                        <input type="flightnumber" class="form-control" id="flightnumber"  required maxlength="4" minlength="1" placeholder="1234">
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
                        <select id="aircraft" class="form-select" aria-label="Select the aircraft">
                            <option selected></option>
                            <option value="1">A321-200 D-AFME</option>
                            <option value="2">A320-214 D-AMET</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="callsign" class="form-label">Callsign</label>
                        <input type="text" class="form-control" style="text-transform:uppercase" id="callsign" required minlength="4" maxlength="7" placeholder="DLH141">
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
                        <textarea class="form-control" style="font-family: monospace; font-size: 18px; text-transform:uppercase" aria-label="With textarea" id="route" required>KUMIK Y854 BOMBI T104 ROKIL</textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="network" class="form-label">Online network</label>
                        <select id="network" class="form-select" aria-label="Select the online network">
                            <option value="1">OFFLINE</option>
                            <option value="2">VATSIM</option>
                            <option value="3">IVAO</option>
                            <option value="4">POSCON</option>
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

@endsection