@extends('layouts.app')
@section('title', 'View Aircraft')
@section('content')

<!-- Error Handling -->
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Error during request:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Aircraft Details -->
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <!-- Aircraft Registration -->
            <h2 class="display-4">{{ $aircraft->registration }}</h2>

            <!-- Aircraft Information -->
            <div class="card">
                <div class="card-header">
                    <strong>Aircraft information</strong>
                </div>
                <div class="card-body">
                    <p><strong>Owner:</strong> {{ $aircraft->airline->name }}</p>
                    <p><strong>Type:</strong> {{ $aircraft->full_type }}</p>
                    <p><strong>Remarks:</strong> {{ $aircraft->remarks }}</p>
                    <p><strong>Total Flights:</strong> {{ $aircraft->total_flights_count }}</p>
                    <p><strong>Total Flight Hours:</strong> {{ $aircraft->total_flights_hours }} hours</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3">
                <button class="btn btn-secondary" onclick="window.location.href='{{ route('fleetmanager') }}'">Back</button>
            </div>
        </div>

        <!-- Current Location -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <strong>Current location</strong>
                </div>
                <div class="card-body">
                    <x-maps-leaflet style="height: 300px; width: 100%;" :zoomLevel="11" :markers="[['lat' => $lat, 'long' => $lon]]" :centerPoint="['lat' => $lat, 'long' => $lon]"></x-maps-leaflet>
                    <p class="text-center">Currently standing at {{ $aircraft->location->icao_code }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
