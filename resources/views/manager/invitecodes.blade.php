@extends('layouts.app')

@section('title', 'Invite Codes — YAAMS')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Invite Codes</h2>
        <p class="text-muted mb-0 small">{{ $airline->name }} ({{ $airline->icao_callsign }})</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
        <i class="bi bi-plus-lg me-1"></i> Generate Code
    </button>
</div>

<div class="card">
    <div class="card-header">All Invite Codes</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Role</th>
                    <th>Created by</th>
                    <th>Status</th>
                    <th>Used by</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($codes as $code)
                    <tr>
                        <td><span class="font-monospace fw-semibold">{{ $code->code }}</span></td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ $code->role }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $code->creator->name ?? '—' }}</td>
                        <td>
                            @if($code->isUsed())
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Used</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            @if($code->usedBy)
                                {{ $code->usedBy->name }}
                                <span class="text-muted"> · {{ $code->used_at->diffForHumans() }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            @unless($code->isUsed())
                                <form action="{{ route('invitecodes.destroy', $code) }}" method="POST"
                                      onsubmit="return confirm('Delete this code?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No invite codes yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Generate Modal --}}
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="generateModalLabel">
                    <i class="bi bi-ticket-perforated me-2 text-primary"></i>Generate Invite Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('invitecodes.generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">The generated code will follow the format
                        <span class="font-monospace fw-semibold">{{ strtoupper($airline->icao_callsign) }}-XXXX</span>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label" for="role">Role for the invitee</label>
                        <select name="role" id="role" class="form-select">
                            <option value="Pilot">Pilot</option>
                            <option value="Dispatcher">Dispatcher</option>
                            <option value="Manager">Manager</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
