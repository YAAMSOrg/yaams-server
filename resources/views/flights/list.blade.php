@extends('layouts.app')
@section('title', 'YAAMS: Flight list')
@section('content')

        <h1 class="display-4 mb-4">My flights</h1>
        <p class="lead">Here is a list of your filed flights and their PIREP status. Keep in mind, that this is only for your active airline: {{ session('activeairline')->name }}</p>
        <hr>

                @if ($flights->isEmpty())
                <div class="alert alert-warning" role="alert">
                    You have not logged any flights yet. Go ahead and <a href="{{ route('addflight') }}">file a PIREP.
                </div>
                @else
                <h2 class="h4">Flight list</h2>
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr>
                            <th scope="col" class="text-center">PIREP ID</th>
                            <th scope="col" class="text-center">Flight number</th>
                            <th scope="col" class="text-center">ATC Callsign</th>
                            <th scope="col" class="text-center">From</th>
                            <th scope="col" class="text-center">To</th>
                            <th scope="col" class="text-center">Duration</th>
                            <th scope="col" class="text-center">Date</th>
                            <th scope="col" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flights as $flight)
                            <tr>  
                                <td class="text-center"><a href="{{ route('viewflight', $flight->id) }}">{{ $flight->id }}</a></td>
                                <td class="text-center">{{ $flight->full_flight_number }}</td>
                                <td class="text-center">{{ $flight->full_icao_callsign }}</td>
                                <td class="text-center">{{ $flight->departure_icao }}</td>
                                <td class="text-center">{{ $flight->arrival_icao }}</td>
                                <td class="text-center">{{ $flight->flight_duration }}</td>
                                <td class="text-center">{{ $flight->flight_date }}</td>
                                <td class="text-center">TODO</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
@endsection

