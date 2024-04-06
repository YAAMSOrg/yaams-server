@extends('layouts.app')
@section('title', 'YAAMS: Pilot Dashboard')
@section('content')

<div class="container mt-5" style="max-width: 2000px">  
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-4">Fleet overview</h1>
            <p class="lead">Here is a list of all aircraft and their current locations according to their last flight.</p>

            @can('add aircraft')
                <button type="button" class="btn btn-primary" style="float: right" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Add aircraft</button>
            @endcan
            
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

            @can('add aircraft')
            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Add aircraft</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('fleetmanager') }}" method="post" style="display: inline">
                            @csrf
                            <input type="hidden" id="in_service_since" name="in_service_since" value="2023-05-05" hidden
                                required>
                            <input type="hidden" id="used_by" name="used_by" value="1" hidden required>
                            <div class="row">
                                <div class="mb-3">
                                    <label for="registration" class="form-label">Tail number</label>
                                    <input type="text" id="registration" name="registration"
                                        style="text-transform:uppercase" class="form-control" required placeholder="D-EXAM"
                                        minlength="4" maxlength="6">
                                </div>
                                <div class="mb-3">
                                    <label for="manufacturer" class="form-label">Manufacturer</label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer"
                                        maxlength="100" required placeholder="Boeing">
                                </div>
                                <div class="mb-3">
                                    <label for="model" class="form-label">Model</label>
                                    <input type="text" class="form-control" id="model" name="model" maxlength="100" required
                                        placeholder="737-800WL">
                                </div>
                                <div class="mb-3">
                                    <label for="current_loc" class="form-label">First location</label>
                                    <input type="text" class="form-control" id="current_loc" name="current_loc"
                                        minlength="4" maxlength="4" pattern="[A-Z]+" required placeholder="EDDL">
                                </div>
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks / Description</label>
                                    <textarea class="form-control" style="font-family: monospace; font-size: 18px;"
                                        aria-label="With textarea" id="remarks" name="remarks"
                                        placeholder="Split scimitar winglet variant"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Add aircraft</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        @if(!$fleet->count() == 0)
            <div class="my-4">
                <h2 class="h4">Current active fleet</h2>
                <table class="table">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="text-center">Tail number</th>
                            <th scope="col" class="text-center">Airline</th>
                            <th scope="col" class="text-center">Type</th>
                            <th scope="col" class="text-center">Current location</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fleet as $aircraft)
                            <tr>
                                <th scope="row" class="text-center">{{ $aircraft->registration }}</th>
        
                                <td class="text-center">{{ $aircraft->airline->name }}</td>
        
                                <td class="text-center">{{ $aircraft->full_type }}</td>
        
                                <td class="text-center">@if(is_null($aircraft->current_loc))
                                    <abbr title="This might be, because the aircraft just got initialized.">No location
                                        found</abbr>
                                @else
                                    {{ $aircraft->current_loc }}
                                @endif
                                <td class="text-center"><a href="">View and Edit</a></td>
                        </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($maxPages > 1)
                <nav>
                    <ul class="pagination justify-content-center">
                      <li class="page-item">
                        <a class="page-link" href="{{ route('fleetmanager', ['page' => $currentPage-1], false)}}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>
        
                      @for($page=1;$page<=$maxPages;$page++)
                        <li class="page-item @if($page === $currentPage) active @endif"><a class="page-link" href="{{ route('fleetmanager', ['page' => $page], false)}}">{{ $page }}</a></li>
                      @endfor
                      
                      <li class="page-item">
                        <a class="page-link" href="{{ route('fleetmanager', ['page' => $currentPage+1], false)}}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                      </li>
                    </ul>
                </nav>
                @endif
            </div>
        @else
            <br><br><!-- This is ugly. Fix it! FIXME-->
            <div class="alert alert-info center-block">No aircraft has been added yet.</p> 
        @endif
        </div>
    </div>
</div>

@endsection
