@extends('layouts.app')
@section('title', 'Welcome to YAAMS')
@section('content')
            <h1 class="display-4 mb-4">Switch active airline</h1>
            <p class="lead">Change the current airline for your active session.</p>
            <p>
                Current active airline: <b>Bla</b>
            </p>

            <form method=POST class="row g-2" action="{{ route('changeactiveairline') }}">
                @csrf
                <div class="col-md-4">
                    <label for="airline" class="form-label">Choose an airline to be active</label>
                    <select id="airline" class="form-select" required aria-label="Select the online network">
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Switch</button>
                </div>
            </form>
@endsection
