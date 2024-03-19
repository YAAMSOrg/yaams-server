@extends('layouts.app')
@section('title', 'YAAMS: Fleet overview')
@section('content')
        <div class="container" >
            <h1 class="display-2">Aircraft list</h1>
            <p class="lead">Here is a list of all aircraft and their current locations according to their last flight.</p>

            <table class="table">
                <thead class="table-dark">
                    <tr>
                    <th scope="col">Tail number</th>
                    <th scope="col">Airline</th>
                    <th scope="col">Type</th>
                    <th scope="col">Current location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fleet as $aircraft)
                    <tr>
                        <th scope="row">{{ $aircraft->registration }}</th>
                        
                        <td>{{ $aircraft->airline->name }}</td>
                        
                        <td>{{ $aircraft->full_type }}</td>

                        <td>@if(is_null($aircraft->current_loc))
                            <abbr title="This might be, because the aircraft just got initialized.">No location found</abbr>
                            @else 
                            {{ $aircraft->current_loc }} 
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection