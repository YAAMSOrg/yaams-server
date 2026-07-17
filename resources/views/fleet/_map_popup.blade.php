{{-- Fleet map marker popup: one airport, the aircraft parked there. --}}
<div class="fleet-map-popup">
    <div class="fw-bold font-monospace">{{ $airport->icao_code }}</div>
    @if($airport->name)
        <div class="text-muted small mb-2">{{ $airport->name }}</div>
    @endif
    <div class="text-muted small mb-1">{{ $aircraft->count() }} {{ \Illuminate\Support\Str::plural('aircraft', $aircraft->count()) }}</div>
    <ul class="list-unstyled mb-0 small">
        @foreach($aircraft as $ac)
            <li class="d-flex align-items-center gap-1 mb-1">
                <a href="{{ route('viewaircraft', $ac) }}" class="fw-bold text-decoration-none font-monospace">{{ $ac->registration }}</a>
                <span class="text-muted">{{ $ac->full_type }}</span>
                @if($ac->status === \App\Models\Aircraft::STATUS_INACTIVE)
                    <span class="badge bg-secondary ms-auto">Inactive</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
