@extends('layouts.app')
@section('title', 'Create Airline')

@section('content')
<x-admin.page-header icon="building-add" title="Create airline"
                     subtitle="Found an airline on behalf of a user, who becomes its owner and Manager."
                     :crumbs="['Airlines' => route('admin.airlines.index')]" />

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'airlines'])

    <div class="col-12 col-lg-9">
        <form method="POST" action="{{ route('admin.airlines.store') }}" class="card">
            @csrf
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Owner</label>
                    <x-admin.user-picker name="owner_id" placeholder="Search by name or e-mail" />
                    <div class="form-text">Becomes owner and Manager of the new airline. Ownership can be transferred later.</div>
                </div>

                @include('admin.airlines._form', ['airline' => null, 'bag' => 'default'])
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Create airline</button>
                <a href="{{ route('admin.airlines.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
