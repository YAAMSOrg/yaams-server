{{-- Page header shared by every admin page: sits above the sidebar row and
     names the page generically ("Airline details", never the airline itself -
     the entity card inside the main column shows what is being edited).
     Every page renders the same breadcrumb row so the layout lines up; pass
     `crumbs` (label => url) for the levels between "Administration" and the
     current page. --}}
@props([
    'icon',
    'title',
    'subtitle' => null,
    'crumbs' => [],
])
@php($isOverview = request()->routeIs('admin.dashboard'))
<div class="yaams-admin-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                @if ($isOverview)
                    <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-shield-lock me-1"></i>Administration</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none"><i class="bi bi-shield-lock me-1"></i>Administration</a></li>
                    @foreach ($crumbs as $label => $url)
                        <li class="breadcrumb-item"><a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a></li>
                    @endforeach
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                @endif
            </ol>
        </nav>
        <h1 class="h3 mb-1"><i class="bi bi-{{ $icon }} me-2"></i> {{ $title }}</h1>
        @if ($subtitle || $slot->isNotEmpty())
            <p class="text-muted mb-0">{{ $subtitle }}{{ $slot }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
