{{-- Airline settings sections sidebar. Pass $active ('operations' | 'invitecodes')
     to highlight the current section. --}}
@php($active = $active ?? 'operations')
<div class="col-12 col-lg-3">
    <div class="card">
        <div class="list-group list-group-flush">
            <a href="{{ route('airline.settings') }}"
               class="list-group-item list-group-item-action {{ $active === 'operations' ? 'active' : '' }}">
                <i class="bi bi-sliders me-2"></i> Operations
            </a>
            <a href="{{ route('invitecodes.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'invitecodes' ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated me-2"></i> Invite codes
            </a>
        </div>
    </div>
</div>
