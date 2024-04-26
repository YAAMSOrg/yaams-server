@extends('layouts.app')
@section('title', 'YAAMS: Flight list')
@section('content')

        <h1 class="display-4 mb-4">Notifications</h1>
        <!--<p class="lead"></p>-->
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

        @if ($notifications->isEmpty())
        <div class="alert alert-warning" role="alert">
            You have no new notifications.
        </div>
        @else
        <h2 class="h4">Notification overview</h2>
        <!-- Maybe use cards ?? -->
        @endif

@endsection

