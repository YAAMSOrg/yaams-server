@extends('layouts.app')
@section('title', 'YAAMS: Security Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i> Account Settings</h1>
        <p class="text-muted mb-0">Manage your profile, security and notification preferences.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: settings sections --}}
    @include('settings._sidebar', ['active' => 'security'])

    {{-- Main: password form --}}
    <div class="col-12 col-lg-9">
        @if (session('status') === 'password-updated')
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> Your password has been updated.
            </div>
        @endif

        @if ($errors->updatePassword->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->updatePassword->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('user-password.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i> Change password</h6>

                    <div class="mb-4">
                        <label for="current_password" class="form-label">Current password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key text-secondary"></i></span>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">New password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-control"
                                   autocomplete="new-password"
                                   required>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-2"></i> Update password
                    </button>
                </form>
            </div>
        </div>

        {{-- API tokens --}}
        <div class="card mt-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-code-square me-2 text-primary"></i> API tokens</h6>
                <p class="text-muted">
                    Personal access tokens authenticate requests against the REST API.
                    Send them as a <code>Bearer</code> token in the <code>Authorization</code> header.
                </p>

                @if (session('status') === 'token-created')
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Token created.</strong>&nbsp;Copy it now — it will not be shown again.
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   id="plain-text-token"
                                   class="form-control font-monospace"
                                   value="{{ session('plain_text_token') }}"
                                   readonly
                                   onclick="this.select()">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="navigator.clipboard.writeText(document.getElementById('plain-text-token').value)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('status') === 'token-revoked')
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i> The token has been revoked.
                    </div>
                @endif

                @error('token_name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <form action="{{ route('settings.tokens.store') }}" method="post" class="mb-4">
                    @csrf
                    <label for="token_name" class="form-label">Create a new token</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag text-secondary"></i></span>
                        <input type="text"
                               id="token_name"
                               name="token_name"
                               class="form-control @error('token_name') is-invalid @enderror"
                               placeholder="Token name, e.g. ACARS client"
                               maxlength="255"
                               required>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-plus-circle me-2"></i> Create token
                        </button>
                    </div>
                </form>

                @if ($tokens->isEmpty())
                    <p class="text-muted mb-0">You have no API tokens.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th>Last used</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                    <tr>
                                        <td>{{ $token->name }}</td>
                                        <td>{{ $token->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $token->last_used_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('settings.tokens.destroy', $token->id) }}"
                                                  method="post"
                                                  onsubmit="return confirm('Revoke the token “{{ $token->name }}”? Applications using it will lose API access immediately.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="bi bi-x-circle me-1"></i> Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
