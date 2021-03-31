@extends('layouts.app')

@section('content')

    <div class="flex justify-center">
            <div class="w-8/12 bg-white p-6 rounded-lg">
                <h1 class="text-6xl font-normal leading-normal mt-0 mb-2 text-black-800">
                    Welcome, {{ Auth::user()->name }}!
                </h1>
                <p>
                    You have a total of <b>161 hours</b> in <b>54 flights</b>.
                </p>
                <h2 class="text-3xl font-normal leading-normal mt-0 mb-2 text-black-800">
                    Current flight(s)
                </h2>
                <div class="">
                    <!-- Here comes a map of the current flights, currently just a placeholder image-->
                    <img src="https://placeimg.com/1280/720/any" alt="Map with current flights">
                </div>
            </div>
    </div>
@endsection
