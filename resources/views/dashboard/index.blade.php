@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')
@section('content')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-4">Welcome, {{ Auth::user()->name }}!</h1>
            <p class="lead">You have a total of <b>161 hours</b> in <b>54 flights</b>.</p>

            @if(is_null(Auth::user()->email_verified_at))
            <div class="alert alert-warning" role="alert">
                Please verify your email address, so you can start using this YAAMS instance!
            </div>
            @endif

            <div class="my-4">
                <h2 class="h4">Live Flights</h2>
                <div class="border p-4" style="min-height: 400px;">
                    <p>Feature not yet implemented.</p>
                </div>
            </div>

            <div class="my-4">
                <h2 class="h4">Last 5 Flights</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Flight</th>
                            <th scope="col">Departure</th>
                            <th scope="col">Arrival</th>
                            <th scope="col">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Flight 1</td>
                            <td>Departure 1</td>
                            <td>Arrival 1</td>
                            <td>Duration 1</td>
                        </tr>
                        <tr>
                            <td>Flight 2</td>
                            <td>Departure 2</td>
                            <td>Arrival 2</td>
                            <td>Duration 2</td>
                        </tr>
                        <tr>
                            <td>Flight 3</td>
                            <td>Departure 3</td>
                            <td>Arrival 3</td>
                            <td>Duration 3</td>
                        </tr>
                        <tr>
                            <td>Flight 4</td>
                            <td>Departure 4</td>
                            <td>Arrival 4</td>
                            <td>Duration 4</td>
                        </tr>
                        <tr>
                            <td>Flight 5</td>
                            <td>Departure 5</td>
                            <td>Arrival 5</td>
                            <td>Duration 5</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
