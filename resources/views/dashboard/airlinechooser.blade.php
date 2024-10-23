@extends('layouts.app')
@section('title', 'YAAMS: Choose airline')
@section('content')

    <h1 class="display-4 mb-4">Choose active airline</h1>
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

    <form method=POST class="row g-2" action="{{ route('changeactiveairline') }}">
        @csrf
        <div class="col-md-4">
            <label for="airline" class="form-label">Choose an airline to be active</label>
            <select id="airline" name="airline_id" class="form-select" required aria-label="Select the active airline">
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
