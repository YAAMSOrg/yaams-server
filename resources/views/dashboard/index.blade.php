@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')
@section('content')

    <div class="container" >
    <h1 class="display-2">Welcome, {{ Auth::user()->name }}!</h1>
    <p class="lead">You have a total of <b>161 hours</b> in <b>54 flights</b>.</p>
    <hr>
        <div class="">
        <!-- Here comes a map of the current flights, currently just a placeholder image-->
        <img src="https://placeimg.com/1280/720/any" alt="Map with current flights">
        </div>
    </div>
@endsection
