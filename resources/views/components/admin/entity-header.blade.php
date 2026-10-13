{{-- Identity card of the record an admin detail page edits (user, airline):
     first card of the main column. Slots: `tile` (code tile or avatar),
     `badges`, `actions` (quick actions) and `facts` (<x-admin.fact> items). --}}
@props(['name', 'subtitle' => null])
<div class="card yaams-entity-header mb-4">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
        <div class="yaams-entity-header__tile" aria-hidden="true">{{ $tile }}</div>
        <div class="flex-grow-1 min-w-0">
            <h2 class="h4 mb-0 text-break">{{ $name }}</h2>
            @if ($subtitle)
                <div class="text-muted small text-break">{{ $subtitle }}</div>
            @endif
            @isset($badges)
                <div class="d-flex flex-wrap gap-1 mt-1">{{ $badges }}</div>
            @endisset
        </div>
        @isset($actions)
            <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @isset($facts)
        <dl class="yaams-entity-header__facts row row-cols-2 row-cols-md-3 row-cols-xl-6 g-0 mb-0">
            {{ $facts }}
        </dl>
    @endisset
</div>
