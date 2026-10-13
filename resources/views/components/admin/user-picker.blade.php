{{-- Search-as-you-type user picker for admin forms. Submits the chosen user's
     id as `name`. Backed by admin.users.lookup; pass `airline` to leave out
     users who are already members of that airline. --}}
@props([
    'name' => 'user_id',
    'airline' => null,
    'bag' => 'default',
    'placeholder' => 'Search by name or e-mail',
])
@php
    $id = 'user-picker-'.$name.'-'.uniqid();
    $selected = old($name) ? \App\Models\User::find(old($name)) : null;
    $error = $errors->getBag($bag)->first($name);
    $lookupUrl = route('admin.users.lookup', array_filter(['airline' => $airline]));
@endphp
<div {{ $attributes->merge(['class' => 'yaams-user-picker position-relative']) }}
     data-user-picker data-lookup-url="{{ $lookupUrl }}">
    <input type="hidden" name="{{ $name }}" value="{{ $selected?->id }}" data-picker-value>

    {{-- Chosen user --}}
    <div class="yaams-user-picker__selected form-control {{ $error ? 'is-invalid' : '' }}"
         data-picker-selected @if (! $selected) hidden @endif>
        <span data-picker-selected-avatar>@if ($selected)<x-pilot-avatar :name="$selected->name" size="32" />@endif</span>
        <span class="min-w-0 flex-grow-1 lh-sm">
            <span class="fw-semibold d-block text-truncate" data-picker-selected-name>{{ $selected?->name }}</span>
            <span class="small text-muted d-block text-truncate" data-picker-selected-email>{{ $selected?->email }}</span>
        </span>
        <button type="button" class="btn btn-sm btn-link text-muted p-0" data-picker-clear aria-label="Choose a different user">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Search --}}
    <div class="input-group has-validation" data-picker-search @if ($selected) hidden @endif>
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" id="{{ $id }}" class="form-control {{ $error ? 'is-invalid' : '' }}" autocomplete="off"
               placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
               role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="{{ $id }}-list"
               data-picker-input>
        <span class="input-group-text d-none" data-picker-spinner>
            <span class="spinner-border spinner-border-sm text-muted" role="status" aria-label="Searching"></span>
        </span>
    </div>

    <div class="yaams-user-picker__menu list-group shadow-sm" id="{{ $id }}-list" role="listbox" data-picker-menu hidden></div>

    @if ($error)
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-user-picker]').forEach(function (picker) {
        const input = picker.querySelector('[data-picker-input]');
        const menu = picker.querySelector('[data-picker-menu]');
        const value = picker.querySelector('[data-picker-value]');
        const search = picker.querySelector('[data-picker-search]');
        const selected = picker.querySelector('[data-picker-selected]');
        const spinner = picker.querySelector('[data-picker-spinner]');
        let results = [];
        let active = -1;
        let timer = null;
        let request = null;

        function open(isOpen) {
            menu.hidden = !isOpen;
            input.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function message(text) {
            menu.replaceChildren();
            const item = document.createElement('div');
            item.className = 'list-group-item small text-muted';
            item.textContent = text;
            menu.appendChild(item);
            open(true);
        }

        function highlight(index) {
            active = index;
            menu.querySelectorAll('[role=option]').forEach(function (option, i) {
                option.classList.toggle('active', i === index);
                option.setAttribute('aria-selected', i === index ? 'true' : 'false');
                if (i === index) {
                    option.scrollIntoView({ block: 'nearest' });
                    input.setAttribute('aria-activedescendant', option.id);
                }
            });
        }

        function render() {
            if (results.length === 0) {
                message('No matching users.');
                return;
            }

            menu.replaceChildren();
            results.forEach(function (user, i) {
                const option = document.createElement('button');
                option.type = 'button';
                option.id = menu.id + '-' + user.id;
                option.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
                option.setAttribute('role', 'option');
                option.innerHTML = user.avatar;

                const text = document.createElement('span');
                text.className = 'min-w-0 flex-grow-1 lh-sm';
                const name = document.createElement('span');
                name.className = 'fw-semibold d-block text-truncate';
                name.textContent = user.name;
                const email = document.createElement('span');
                email.className = 'small d-block text-truncate yaams-user-picker__email';
                email.textContent = user.email;
                text.append(name, email);
                option.appendChild(text);

                if (user.suspended) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-danger-subtle text-danger';
                    badge.textContent = 'Suspended';
                    option.appendChild(badge);
                }

                option.addEventListener('mousedown', function (event) { event.preventDefault(); });
                option.addEventListener('click', function () { choose(user); });
                option.addEventListener('mousemove', function () { if (active !== i) highlight(i); });
                menu.appendChild(option);
            });
            open(true);
            highlight(0);
        }

        function lookup(term) {
            if (request) request.abort();
            request = new AbortController();
            spinner.classList.remove('d-none');

            const url = new URL(picker.dataset.lookupUrl, window.location.origin);
            url.searchParams.set('q', term);

            fetch(url, { headers: { 'Accept': 'application/json' }, signal: request.signal })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.json();
                })
                .then(function (json) {
                    results = json.data;
                    render();
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') message('Search failed. Please try again.');
                })
                .finally(function () { spinner.classList.add('d-none'); });
        }

        function choose(user) {
            value.value = user.id;
            selected.querySelector('[data-picker-selected-avatar]').innerHTML = user.avatar;
            selected.querySelector('[data-picker-selected-name]').textContent = user.name;
            selected.querySelector('[data-picker-selected-email]').textContent = user.email;
            selected.hidden = false;
            search.hidden = true;
            open(false);
        }

        picker.querySelector('[data-picker-clear]').addEventListener('click', function () {
            value.value = '';
            selected.hidden = true;
            selected.classList.remove('is-invalid');
            search.hidden = false;
            input.value = '';
            results = [];
            input.focus();
        });

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const term = input.value.trim();
            if (term.length < 2) {
                if (request) request.abort();
                open(false);
                return;
            }
            timer = setTimeout(function () { lookup(term); }, 200);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' && results.length) {
                event.preventDefault();
                if (menu.hidden) { render(); return; }
                highlight(Math.min(active + 1, results.length - 1));
            } else if (event.key === 'ArrowUp' && results.length) {
                event.preventDefault();
                highlight(Math.max(active - 1, 0));
            } else if (event.key === 'Enter') {
                // Never submit the form with only a search term typed
                event.preventDefault();
                if (!menu.hidden && results[active]) choose(results[active]);
            } else if (event.key === 'Escape') {
                open(false);
            }
        });

        input.addEventListener('blur', function () { open(false); });
        input.addEventListener('focus', function () {
            if (results.length && input.value.trim().length >= 2) render();
        });
    });
});
</script>
@endpush
@endonce
