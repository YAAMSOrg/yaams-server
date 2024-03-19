@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')
@section('content')

    <div class="container" >
    <h1 class="display-2">Welcome, {{ Auth::user()->name }}!</h1>
    <p class="lead">You have a total of <b>161 hours</b> in <b>54 flights</b>.</p>
    <hr>
        @if(is_null(Auth::user()->email_verified_at))
            <div class="alert alert-warning" role="alert">
                Please verify your e-mail address, so you can start using this YAAMS instance!
            </div>
        @else 
        @endif  

        <p>Your homebase is {{ Auth::user()->homebase }}</p>
        </div>
    </div>
@endsection
