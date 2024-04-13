@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')
@section('content')

            <h1 class="display-4 mb-4">Welcome, {{ Auth::user()->name }}!</h1>
            @if( $flight_count == 0)
                <p class="lead">You have no logged flights on your current airline {{ session('activeairline')->name }}.</p>
            @else
                <p class="lead">You have a total of <b>{{ $flight_hours }} hours</b> in <b>{{ $flight_count }} flights</b> logged for {{ session('activeairline')->name }}.</p>
            @endif

            @if(is_null(Auth::user()->email_verified_at))
            <div class="alert alert-warning" role="alert">
                Please verify your email address, so you can start using this YAAMS instance!
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger" role="alert">
                You did something nasty!
            </div>
            @endif

            <div class="my-4">
                <h2 class="h4">Live Flights</h2>
                <div class="border p-4" style="min-height: 400px;">
                    <p>Feature not yet implemented.</p>
                </div>
            </div>

            <ul>
            </ul>
            <div class="my-4">
                <h2 class="h4">Last 5 Flights of {{ session('activeairline')->name }}</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-center" scope="col">Flight</th>
                            <th class="text-center" scope="col">Callsign</th>
                            <th class="text-center" scope="col">From / To</th>
                            <th class="text-center" scope="col">Aircraft</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flights as $flight)
                        <tr>
                            <td class="text-center">{{ $flight->full_flight_number }}</td>
                            <td class="text-center">{{ $flight->full_icao_callsign }}</td>
                            <td class="text-center"><abbr title="{{ $flight->departure_airport->name }}">{{ $flight->departure_airport->icao_code }}</abbr> <i class="bi-arrow-right"></i> <abbr title="{{ $flight->arrival_airport->name }}">{{ $flight->arrival_icao }}</abbr></td>
                            <td class="text-center"><abbr title="{{ $flight->aircraft->full_type }}">{{ $flight->aircraft->registration }}</abbr></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

@endsection
