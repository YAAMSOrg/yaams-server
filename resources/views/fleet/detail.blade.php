@extends('layouts.app')
@section('title', 'View aircraft')
@section('content')
            <h1 class="display-4 mb-4">View aircraft</h1>       
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

            <h5 class="display-5">{{ $aircraft->registration }}</h2>

            <dl class="row">
                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ $aircraft->full_type }}</dd>
              
                <dt class="col-sm-3">In service since</dt>
                <dd class="col-sm-9">TODO</dd>

                <dt class="col-sm-3">First flight</dt>
                <dd class="col-sm-9">TODO</dd>
            </dl>

            <b>I like the idea of this page, so I'm keeping it. But it needs to be filled.</b>


            <h6 class="display-6">Flight list</h2>
            
            TODO

            <h6 class="display-6">What to do here</h6>

            TODO
            <br>

            <input type="button" class="btn btn-secondary" value="Back" onclick="window.location.href='{{ route('fleetmanager') }}'">
@endsection