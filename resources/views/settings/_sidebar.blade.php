{{-- User settings sections sidebar. Pass $active
     ('profile' | 'security' | 'notifications' | 'danger') to highlight
     the current section. --}}
@php($active = $active ?? 'profile')
<div class="col-12 col-lg-3">
    <div class="card">
        <div class="list-group list-group-flush">
            <a href="{{ route('settings.profile') }}"
               class="list-group-item list-group-item-action {{ $active === 'profile' ? 'active' : '' }}">
                <i class="bi bi-person me-2"></i> Profile
            </a>
            <a href="{{ route('settings.security') }}"
               class="list-group-item list-group-item-action {{ $active === 'security' ? 'active' : '' }}">
                <i class="bi bi-shield-lock me-2"></i> Security
            </a>
            <a href="{{ route('settings.notifications') }}"
               class="list-group-item list-group-item-action {{ $active === 'notifications' ? 'active' : '' }}">
                <i class="bi bi-bell me-2"></i> Notifications
            </a>
            <a href="{{ route('settings.danger') }}"
               class="list-group-item list-group-item-action text-danger {{ $active === 'danger' ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle me-2"></i> Danger zone
            </a>
        </div>
    </div>
</div>
