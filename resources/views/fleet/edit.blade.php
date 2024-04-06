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
        
            <form action="{{ route('editfleet', $aircraft->id) }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" id="used_by" name="used_by" value="1" hidden required>
                <div class="col-md-4">
                    <label for="registration" class="form-label">Registration (tail number)</label>
                    <input type="text" class="form-control" id="registration" name="registration" value="{{ $aircraft->registration }}" required>
                </div>
                <div class="col-md-4">
                    <label for="manufacturer" class="form-label">Manufacturer</label>
                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ $aircraft->manufacturer }}" required>
                </div>
                <div class="col-md-4">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="{{ $aircraft->model }}" required>
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
                <div class="col-md-12">
                    <label for="remarks" class="form-label">Remarks / Description</label>
                    <textarea class="form-control" id="remarks" name="remarks">{{ $aircraft->remarks }}</textarea>
                </div>
                <div class="">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
@endsection
