@extends('layouts.app')
@section('title', 'YAAMS: Fleet overview')
@section('content')
        <div class="container" >
            <h1 class="display-2">Fleet overview</h1>
            <p class="lead" style="float: left">Here is a list of all aircraft and their current locations according to their last flight.</p>

            @if ($errors->any())
            <div class="alert alert-danger">
                Error during request: 
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- TODO: Only show if user is a manager of the particular airline! -->
            <button type="button" class="btn btn-primary" style="float: right" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        Add aircraft
            </button>
                    <!-- Modal -->
                    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add aircraft</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('fleetmanager') }}" method="post" style="display: inline">
                                        @csrf
                                        <input type="hidden" id="in_service_since" name="in_service_since" value="2023-05-05" hidden required>
                                        <input type="hidden" id="used_by" name="used_by" value="1" hidden required>
                                        <div>
                                            <label for="registration" class="form-label">Registration</label>
                                            <input type="text" id="registration" name="registration" style="text-transform:uppercase" class="form-control" required placeholder="D-EXAM" minlength="4" maxlength="6">
                                        </div>
                                        <div>
                                            <label for="manufacturer" class="form-label">Manufacturer</label>
                                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" required placeholder="Boeing">
                                        </div>
                                        <div>
                                            <label for="model" class="form-label">Model</label>
                                                <input type="text" class="form-control" id="model" name="model" required placeholder="737-800WL">
                                        </div>
                                        <div>
                                            <label for="current_loc" class="form-label">First location</label>
                                            <input type="text" class="form-control" id="current_loc" name="current_loc" required placeholder="EDDL">
                                        </div>
                                        <div>
                                            <label for="remarks" class="form-label">Remarks / Description</label>
                                            <textarea class="form-control" style="font-family: monospace; font-size: 18px; text-transform:uppercase" aria-label="With textarea" id="remarks" name="remarks" required>Split Scimilar Winglet Variant</textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">File PIREP</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
            
            <table class="table">
                <thead class="table-dark">
                    <tr>
                    <th scope="col">Tail number</th>
                    <th scope="col">Airline</th>
                    <th scope="col">Type</th>
                    <th scope="col">Current location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fleet as $aircraft)
                    <tr>
                        <th scope="row">{{ $aircraft->registration }}</th>
                        
                        <td>{{ $aircraft->airline->name }}</td>
                        
                        <td>{{ $aircraft->full_type }}</td>

                        <td>@if(is_null($aircraft->current_loc))
                            <abbr title="This might be, because the aircraft just got initialized.">No location found</abbr>
                            @else 
                            {{ $aircraft->current_loc }} 
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection