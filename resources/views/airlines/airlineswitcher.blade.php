@extends('layouts.app')
@section('title', 'Welcome to YAAMS')
@section('content')
            <h1 class="display-4 mb-4">Switch active airline</h1>
            <p class="lead">Change the current airline for your active session.</p>

            @if(!empty($current_active))
            <p>
                Current active airline: <b>{{ session('activeairline')->name }}</b>
            </p>
            @endif

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

            <form method=POST class="row g-2" action="{{ route('changeactiveairline') }}">
                @csrf
                <div class="col-md-4">
                    <label for="airline" class="form-label">Choose an airline to be active</label>
                    <select id="airline" name="airline_id" class="form-select" required aria-label="Select the online network">
                        @foreach($memberships as $membership)
                            <option name="{{ $membership->airline->id }}" value="{{ $membership->airline->id }}">{{ $membership->airline->name }} - {{ $membership->airline->icao_callsign }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Switch</button>
                </div>
            </form>
@endsection
