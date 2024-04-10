@extends('layouts.app')
@section('title', 'Edit aircraft')
@section('content')
            <h1 class="display-4 mb-4">Edit aircraft</h1>
            <p class="lead">Please fill out all fields.</p>
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

            <form action="{{ route('editaircraft', $aircraft->id) }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" id="used_by" name="used_by" value="{{ session('activeairline')->id }}" hidden required>
                <div class="col-md-4">
                    <label for="registration" class="form-label">Registration (tail number)</label>
                    <input type="text" class="form-control" id="registration" name="registration" value="{{ $aircraft->registration }}" maxlength="6" required>
                </div>
                <div class="col-md-4">
                    <label for="manufacturer" class="form-label">Manufacturer</label>
                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ $aircraft->manufacturer }}" maxlength="100" required>
                </div>
                <div class="col-md-4">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="{{ $aircraft->model }}" maxlength="100" required>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" role="switch" id="active" 
                            @if( $aircraft->active  == 1) 
                                checked
                            @endif
                        >
                        <label class="form-check-label" for="active">Aircraft in service</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location <i>(Only Admins can change this)</i></label>
                    <p><abbr title="{{ $aircraft->location->name }}">{{ $aircraft->location->icao_code }}</abbr></p>
                </div>
                <div class="col-md-12">
                    <label for="remarks" class="form-label">Remarks / Description</label>
                    <textarea class="form-control" id="remarks" name="remarks">{{ $aircraft->remarks }}</textarea>
                </div>
                <div class="">
                    <button type="submit" class="btn btn-success">Save</button>
                    <input type="button" class="btn btn-secondary" value="Back" onclick="window.location.href='{{ route('fleetmanager') }}'">
                </div>
            </form>
@endsection