@extends('layouts.app')
@section('title', 'YAAMS: Flight list')
@section('content')

        <h1 class="display-4 mb-4">My flights</h1>
        <p class="lead">Here is a list of your filed flights and their PIREP status. Keep in mind, that this is only for your active airline: {{ session('activeairline')->name }}</p>
        <hr>
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

        <button type="button" class="btn btn-success" style="margin-bottom: 5px; float: right" onclick="window.location.href='{{ route('addflight') }}'">File PIREP</button>

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
                            <th scope="col" class="text-center">From / To</th>
                            <th scope="col" class="text-center">Duration</th>
                            <th scope="col" class="text-center">Aircraft</th>
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
                                <td class="text-center"><abbr title="{{ $flight->departure_airport->name }}">{{ $flight->departure_airport->icao_code }}</abbr> <i class="bi-arrow-right"></i> <abbr title="{{ $flight->arrival_airport->name }}">{{ $flight->arrival_icao }}</abbr></td>
                                <td class="text-center">{{ $flight->flight_duration }}</td>
                                <td class="text-center"><abbr title="{{ $flight->aircraft->full_type }}">{{ $flight->aircraft->registration }}</abbr></td>
                                <td class="text-center">{{ $flight->flight_date }}</td>
                                <td class="text-center">{{ $flight->status->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @if($maxPages > 1)
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link" href="{{ route('flightlist', ['page' => $currentPage-1], false)}}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            @for($page=1;$page<=$maxPages;$page++)
                                <li class="page-item @if($page === $currentPage) active @endif"><a class="page-link" href="{{ route('flightlist', ['page' => $page], false)}}">{{ $page }}</a></li>
                            @endfor

                            <li class="page-item">
                                <a class="page-link" href="{{ route('flightlist', ['page' => $currentPage+1], false)}}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                @endif
                @endif
@endsection

