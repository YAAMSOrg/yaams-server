@extends('layouts.app')
@section('title', 'Create Airline')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.airlines.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> All airlines</a>
        <h1 class="h3 mb-1 mt-1"><i class="bi bi-building-add me-2"></i> Create airline</h1>
        <p class="text-muted mb-0">Found an airline on behalf of a user, who becomes its owner and Manager.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'airlines'])

    <div class="col-12 col-lg-9">
        <form method="POST" action="{{ route('admin.airlines.store') }}" class="card">
            @csrf
            <div class="card-body">
                <div class="mb-4">
                    <label for="owner_email" class="form-label">Owner e-mail address</label>
                    <input type="email" name="owner_email" id="owner_email" required maxlength="255"
                           class="form-control @error('owner_email') is-invalid @enderror" value="{{ old('owner_email') }}">
                    @error('owner_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Must be an existing account. Ownership can be transferred later.</div>
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
