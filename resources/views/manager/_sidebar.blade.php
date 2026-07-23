{{-- Airline management sections sidebar. Pass $active
     ('operations' | 'members' | 'invitecodes' | 'announcements') to highlight the current section. --}}
@php($active = $active ?? 'operations')
<div class="col-12 col-lg-3">
    <div class="card">
        <div class="list-group list-group-flush">
            <a href="{{ route('airline.settings') }}"
               class="list-group-item list-group-item-action {{ $active === 'operations' ? 'active' : '' }}">
                <i class="bi bi-sliders me-2"></i> Operations
            </a>
            <a href="{{ route('members.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'members' ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> Members
            </a>
            <a href="{{ route('invitecodes.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'invitecodes' ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated me-2"></i> Invite codes
            </a>
            <a href="{{ route('notams.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'announcements' ? 'active' : '' }}">
                <i class="bi bi-megaphone me-2"></i> Announcements
            </a>
        </div>
    </div>
</div>
