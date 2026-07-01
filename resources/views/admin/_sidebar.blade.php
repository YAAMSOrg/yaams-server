{{-- Admin sections sidebar. Pass $active ('overview' | 'instance') to highlight
     the current section. Users/Airlines management are not built yet. --}}
@php($active = $active ?? 'overview')
<div class="col-12 col-lg-3">
    <div class="card">
        <div class="list-group list-group-flush">
            <a href="{{ route('admin.dashboard') }}"
               class="list-group-item list-group-item-action {{ $active === 'overview' ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Overview
            </a>
            <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                <span><i class="bi bi-people me-2"></i> Users</span>
                <span class="badge bg-secondary-subtle text-secondary">Soon</span>
            </span>
            <span class="list-group-item d-flex justify-content-between align-items-center text-muted">
                <span><i class="bi bi-buildings me-2"></i> Airlines</span>
                <span class="badge bg-secondary-subtle text-secondary">Soon</span>
            </span>
            <a href="{{ route('admin.settings.edit') }}"
               class="list-group-item list-group-item-action {{ $active === 'instance' ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i> Instance
            </a>
        </div>
    </div>
</div>
