@extends('layouts.app')

@section('title', 'Members')

@section('content')

@php
    $viewerIsOwner = auth()->user()->isOwnerOf($airline);
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-speedometer2 me-2"></i> Airline Management</h1>
        <p class="text-muted mb-0">{{ $airline->name }} ({{ $airline->icao_callsign }})</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: airline settings sections --}}
    @include('manager._sidebar', ['active' => 'members'])

    {{-- Main: members --}}
    <div class="col-12 col-lg-9">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <span>Members</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            @php
                                $rowIsOwner = $airline->owner_user_id === $member->id;
                                $rowIsManager = $member->pivot->role === 'Manager';
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $member->name }}</span>
                                    @if($member->id === auth()->id())
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">You</span>
                                    @endif
                                    @if($rowIsOwner)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"><i class="bi bi-star-fill me-1"></i>Owner</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rowIsOwner)
                                        <span class="text-muted">Owner</span>
                                    @else
                                        @if($rowIsManager && ! $viewerIsOwner)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Manager</span>
                                        @else
                                            <form action="{{ route('members.update', $member) }}" method="POST" class="d-flex gap-2 align-items-center">
                                                @csrf
                                                @method('PUT')
                                                <select name="role" class="form-select form-select-sm" style="width:auto"
                                                        onchange="this.form.querySelector('button').classList.remove('d-none')">
                                                    <option value="Pilot" @selected($member->pivot->role === 'Pilot')>Pilot</option>
                                                    <option value="Dispatcher" @selected($member->pivot->role === 'Dispatcher')>Dispatcher</option>
                                                    @if($viewerIsOwner)
                                                        <option value="Manager" @selected($member->pivot->role === 'Manager')>Manager</option>
                                                    @endif
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary d-none">Save</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(! $rowIsOwner)
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($viewerIsOwner)
                                                <form action="{{ route('members.transfer', $member) }}" method="POST"
                                                      onsubmit="return confirm('Are you sure you want to transfer ownership of this airline to {{ $member->name }}? You will step down to a Manager.')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-gift-fill me-1"></i> Make Owner
                                                    </button>
                                                </form>
                                            @endif

                                            @if($viewerIsOwner || ! $rowIsManager)
                                                <form action="{{ route('members.destroy', $member) }}" method="POST"
                                                      onsubmit="return confirm('Remove {{ $member->name }} from the airline?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-person-x"></i> Remove
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No members yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
