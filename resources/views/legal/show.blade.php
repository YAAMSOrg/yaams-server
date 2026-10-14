@extends('layouts.app')
@section('title', $title)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="mb-4">
            <h1 class="h3 mb-1"><i class="bi bi-{{ $icon }} me-2"></i> {{ $title }}</h1>
            @if ($updatedAt)
                <p class="text-muted mb-0">Last updated {{ $updatedAt->toDateString() }}</p>
            @endif
        </div>

        <div class="card">
            <div class="card-body p-4 p-lg-5 yaams-legal">
                {{ $body }}
            </div>
        </div>
    </div>
</div>
@endsection
