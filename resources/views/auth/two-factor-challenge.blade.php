@extends('layouts.loginlayout')
@section('title', 'Two-Factor Confirmation')
@section('content')

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="YAAMS Logo" height="60" class="mb-3">
            <h1 class="h4 mb-2 fw-bold">Two-factor confirmation</h1>
            <p class="text-muted small mb-0" data-tf="code-hint">
                Enter the code from your authenticator app to finish signing in.
            </p>
            <p class="text-muted small mb-0 d-none" data-tf="recovery-hint">
                Enter one of your emergency recovery codes.
            </p>
        </div>

        <form action="{{ route('two-factor.login') }}" method="post">
            @csrf

            {{-- Authenticator code --}}
            <div class="mb-3" data-tf="code-field">
                <label for="code" class="form-label">Authenticator code</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock text-secondary"></i></span>
                    <input type="text"
                           id="code"
                           name="code"
                           class="form-control @error('code') is-invalid @enderror"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           autofocus
                           placeholder="123456">
                </div>
                @error('code')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Recovery code (hidden until the user opts in) --}}
            <div class="mb-3 d-none" data-tf="recovery-field">
                <label for="recovery_code" class="form-label">Recovery code</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key text-secondary"></i></span>
                    <input type="text"
                           id="recovery_code"
                           name="recovery_code"
                           class="form-control @error('recovery_code') is-invalid @enderror"
                           autocomplete="off"
                           placeholder="xxxxxxxxxx-xxxxxxxxxx">
                </div>
                @error('recovery_code')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary w-100 mb-3" type="submit">
                <i class="bi bi-box-arrow-in-right me-2"></i> Confirm
            </button>

            <button type="button" class="btn btn-outline-secondary w-100 btn-sm" data-tf="toggle">
                Use a recovery code
            </button>
        </form>
    </div>
</div>

<div class="text-center mt-4">
    <small class="text-muted">&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
</div>

<script>
    (function () {
        const toggle = document.querySelector('[data-tf="toggle"]');
        const codeField = document.querySelector('[data-tf="code-field"]');
        const recoveryField = document.querySelector('[data-tf="recovery-field"]');
        const codeInput = document.getElementById('code');
        const recoveryInput = document.getElementById('recovery_code');
        const codeHint = document.querySelector('[data-tf="code-hint"]');
        const recoveryHint = document.querySelector('[data-tf="recovery-hint"]');
        let showingRecovery = false;

        toggle.addEventListener('click', function () {
            showingRecovery = !showingRecovery;
            codeField.classList.toggle('d-none', showingRecovery);
            recoveryField.classList.toggle('d-none', !showingRecovery);
            codeHint.classList.toggle('d-none', showingRecovery);
            recoveryHint.classList.toggle('d-none', !showingRecovery);
            toggle.textContent = showingRecovery ? 'Use an authenticator code' : 'Use a recovery code';
            // Clear the hidden field so only the active one is submitted.
            if (showingRecovery) {
                codeInput.value = '';
                recoveryInput.focus();
            } else {
                recoveryInput.value = '';
                codeInput.focus();
            }
        });
    })();
</script>

@endsection
