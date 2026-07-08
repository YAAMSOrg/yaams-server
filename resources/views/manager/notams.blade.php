@extends('layouts.app')

@section('title', 'Airline Management — YAAMS')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-speedometer2 me-2"></i> Airline Management</h1>
        <p class="text-muted mb-0">{{ $airline->name }} ({{ $airline->icao_callsign }})</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: airline management sections --}}
    @include('manager._sidebar', ['active' => 'announcements'])

    {{-- Main: announcements --}}
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

        {{-- Post a new NOTAM --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-megaphone text-primary"></i>
                <span>Post a NOTAM</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('notams.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="{{ old('title') }}" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="body">Message</label>
                        <textarea class="form-control" id="body" name="body" rows="4" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="expires_at">Expires at <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                               value="{{ old('expires_at') }}">
                        <p class="text-muted small mb-0 mt-1">Times are in {{ \App\Support\Timezone::current() }}. Leave empty to keep the NOTAM visible until you delete it.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Post &amp; notify crew
                    </button>
                </form>
            </div>
        </div>

        {{-- Existing NOTAMs --}}
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-list-ul text-primary"></i>
                <span>Posted NOTAMs</span>
            </div>
            <div class="card-body p-0">
                @forelse($notams as $notam)
                    <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="fw-semibold mb-1">
                                    {{ $notam->title }}
                                    @if($notam->expires_at && $notam->expires_at->isPast())
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">Expired</span>
                                    @endif
                                </h6>
                                <p class="mb-2 text-body" style="white-space: pre-line;">{{ $notam->body }}</p>
                                <p class="text-muted small mb-0">
                                    {{ $notam->author->name ?? '—' }} · {{ $notam->created_at->diffForHumans() }}
                                    @if($notam->expires_at)
                                        · Expires {{ \App\Support\Timezone::format($notam->expires_at, 'M d, Y H:i') }} {{ \App\Support\Timezone::current() }}
                                    @else
                                        · No expiry
                                    @endif
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#editNotam{{ $notam->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('notams.destroy', $notam) }}" method="POST"
                                      onsubmit="return confirm('Delete this NOTAM?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editNotam{{ $notam->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('notams.update', $notam) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">
                                            <i class="bi bi-pencil me-2 text-primary"></i>Edit NOTAM
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="title{{ $notam->id }}">Title</label>
                                            <input type="text" class="form-control" id="title{{ $notam->id }}"
                                                   name="title" value="{{ $notam->title }}" maxlength="255" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="body{{ $notam->id }}">Message</label>
                                            <textarea class="form-control" id="body{{ $notam->id }}" name="body"
                                                      rows="4" required>{{ $notam->body }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="expires_at{{ $notam->id }}">Expires at <span class="text-muted fw-normal">(optional)</span></label>
                                            <input type="datetime-local" class="form-control" id="expires_at{{ $notam->id }}"
                                                   name="expires_at" value="{{ \App\Support\Timezone::format($notam->expires_at, 'Y-m-d\TH:i') }}">
                                            <p class="text-muted small mb-0 mt-1">Times are in {{ \App\Support\Timezone::current() }}.</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i> Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-megaphone text-secondary fs-3 d-block mb-2"></i>
                        No NOTAMs posted yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
