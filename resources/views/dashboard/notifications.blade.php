@extends('layouts.app')
@section('title', 'YAAMS: Notifications')
@section('content')

    <h1 class="display-4 mb-4">Notifications</h1>
    <hr>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Error during request:</strong>
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
        <div class="row">
            @foreach ($notifications as $notification)
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{ $notification->title }}</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $notification->message }}</p>
                            <p class="text-muted">{{ $notification->created_at }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
