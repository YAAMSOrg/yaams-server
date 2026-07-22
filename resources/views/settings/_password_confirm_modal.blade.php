{{--
    Reusable password-confirmation modal for a sensitive action.
    Expects: $id, $action, $method (POST|DELETE), $title, $body, $submitLabel, $submitClass
--}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ $action }}" method="post">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ $body }}</p>
                    <label for="{{ $id }}_password" class="form-label">Current password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key text-secondary"></i></span>
                        <input type="password"
                               id="{{ $id }}_password"
                               name="current_password"
                               class="form-control"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ $submitClass }}">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
