@extends('layouts.app')
@section('title', 'View flight')
@section('content')

            @if($errors->any())
            <div class="alert alert-danger">
                Error during request:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h5 class="display-5">Viewing Flight <i>{{ $flight->full_flight_number }} / {{ $flight->full_icao_callsign }}</i></h5>
            <h6 class="display-6">{{ $flight->departure_icao }} <i class="bi-arrow-right-square-fill"></i> {{ $flight->arrival_icao }}
                <small class="text-body-secondary">Unique PIREP ID: {{ $flight->id }}</small>
            </h6>

            <dl class="row">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9">{{ $flight->flight_date }}</dd>
              
                <dt class="col-sm-3">In service since</dt>
                <dd class="col-sm-9">TODO</dd>

                <dt class="col-sm-3">First flight</dt>
                <dd class="col-sm-9">TODO</dd>
            </dl>

            <b>I like the idea of this page, so I'm keeping it. But it needs to be filled.</b>

            TODO

            <h6 class="display-6">What to do here</h6>

            TODO
            <br>

            <input type="button" class="btn btn-secondary" value="Back" onclick="window.location.href='{{ route('flightlist') }}'">
@endsection