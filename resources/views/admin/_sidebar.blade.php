{{-- Admin sections sidebar. Pass $active ('overview' | 'users' | 'airlines' | 'instance' | 'activity')
     to highlight the current section. --}}
@php($active = $active ?? 'overview')
<div class="col-12 col-lg-3">
    <div class="card">
        <div class="list-group list-group-flush">
            <a href="{{ route('admin.dashboard') }}"
               class="list-group-item list-group-item-action {{ $active === 'overview' ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Overview
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'users' ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> Users
            </a>
            <a href="{{ route('admin.airlines.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'airlines' ? 'active' : '' }}">
                <i class="bi bi-buildings me-2"></i> Airlines
            </a>
            <a href="{{ route('admin.settings.edit') }}"
               class="list-group-item list-group-item-action {{ $active === 'instance' ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i> Instance
            </a>
            <a href="{{ route('admin.activity.index') }}"
               class="list-group-item list-group-item-action {{ $active === 'activity' ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i> Activity log
            </a>
        </div>
    </div>
</div>
