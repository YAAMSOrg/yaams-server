{{-- Shared body of every error page (resources/views/errors/*).
     An optional `actions` slot replaces the default "Go back" / "Back to homepage" buttons;
     `supportEmail` adds a "Contact support" mailto. --}}
@props(['code', 'icon', 'heading', 'message', 'supportEmail' => null])

<div class="card shadow-sm text-center">
    <div class="card-body p-4 p-sm-5">
        <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="{{ $instanceName ?? config('app.name') }} Logo" height="48" class="mb-4">

        <div class="error-code mb-2">{{ $code }}</div>
        <h1 class="h4 fw-bold mb-3">
            <i class="bi {{ $icon }} error-icon me-1 align-middle"></i>
            {{ $heading }}
        </h1>
        <p class="text-secondary mb-4">{{ $message }}</p>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
            @isset($actions)
                {{ $actions }}
            @else
                <a href="javascript:history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-1"></i> Go back
                </a>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="bi bi-house me-1"></i> Back to homepage
                </a>
            @endisset
        </div>

        @if($supportEmail)
            <p class="small text-secondary mt-4 mb-0">
                Still stuck? <a href="mailto:{{ $supportEmail }}">Contact support</a>
            </p>
        @endif
    </div>
</div>
