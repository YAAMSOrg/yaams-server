@extends('layouts.app')
@section('title', 'Instance Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-gear me-2"></i> Instance Settings</h1>
        <p class="text-muted mb-0">Configure instance-wide options for this YAAMS install.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'instance'])

    {{-- Main: settings form --}}
    <div class="col-12 col-lg-9">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    {{-- General --}}
                    <h6 class="fw-semibold mb-3"><i class="bi bi-globe2 me-2 text-primary"></i> General</h6>

                    <div class="mb-4">
                        <label for="app_name" class="form-label">Instance name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag text-secondary"></i></span>
                            <input type="text"
                                   id="app_name"
                                   name="app_name"
                                   class="form-control @error('app_name') is-invalid @enderror"
                                   value="{{ old('app_name', $settings['app_name']) }}"
                                   required>
                        </div>
                        <div class="form-text">Shown in the page title and header across the app.</div>
                    </div>

                    <div class="mb-4">
                        <label for="support_email" class="form-label">Support email <span class="text-muted fw-normal">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                            <input type="email"
                                   id="support_email"
                                   name="support_email"
                                   class="form-control @error('support_email') is-invalid @enderror"
                                   placeholder="support@example.com"
                                   value="{{ old('support_email', $settings['support_email']) }}">
                        </div>
                        <div class="form-text">Displayed in the site footer as a contact link when set.</div>
                    </div>

                    <div class="mb-4">
                        <label for="timezone" class="form-label">Display timezone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-clock text-secondary"></i></span>
                            <select id="timezone"
                                    name="timezone"
                                    class="form-select @error('timezone') is-invalid @enderror"
                                    required>
                                @foreach (timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $settings['timezone']) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text">Used for admin- and crew-facing times such as announcement (NOTAM) expiry. Flight and PIREP times always stay in UTC (Zulu).</div>
                    </div>

                    {{-- Policies --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-shield-check me-2 text-info"></i> Policies</h6>

                    <div class="mb-4">
                        <label class="form-label d-block">Public registration</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="allow_registration" id="registration_open" value="1"
                                   {{ old('allow_registration', $settings['allow_registration']) === '1' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="registration_open">
                                <i class="bi bi-door-open me-1"></i> Open
                            </label>

                            <input type="radio" class="btn-check" name="allow_registration" id="registration_closed" value="0"
                                   {{ old('allow_registration', $settings['allow_registration']) === '0' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="registration_closed">
                                <i class="bi bi-door-closed me-1"></i> Closed
                            </label>
                        </div>
                        <div class="form-text">When closed, new users cannot self-register.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Public statistics</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="show_public_stats" id="public_stats_shown" value="1"
                                   {{ old('show_public_stats', $settings['show_public_stats']) === '1' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="public_stats_shown">
                                <i class="bi bi-eye me-1"></i> Shown
                            </label>

                            <input type="radio" class="btn-check" name="show_public_stats" id="public_stats_hidden" value="0"
                                   {{ old('show_public_stats', $settings['show_public_stats']) === '0' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="public_stats_hidden">
                                <i class="bi bi-eye-slash me-1"></i> Hidden
                            </label>
                        </div>
                        <div class="form-text">When hidden, the totals (airlines, pilots, flights, hours) are removed from the public landing page.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Who can found new airlines?</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="allow_user_airline_creation" id="airline_creation_all" value="1"
                                   {{ old('allow_user_airline_creation', $settings['allow_user_airline_creation']) === '1' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="airline_creation_all">
                                <i class="bi bi-people me-1"></i> Any registered user
                            </label>

                            <input type="radio" class="btn-check" name="allow_user_airline_creation" id="airline_creation_admin" value="0"
                                   {{ old('allow_user_airline_creation', $settings['allow_user_airline_creation']) === '0' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="airline_creation_admin">
                                <i class="bi bi-person-lock me-1"></i> Admins only
                            </label>
                        </div>
                        <div class="form-text">Admins can always create airlines regardless of this setting.</div>
                    </div>

                    {{-- Logging --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-journal-text me-2 text-warning"></i> Logging</h6>

                    <div class="mb-4">
                        <label class="form-label d-block">Activity log level</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="LOG_LEVEL" id="log_level_debug" value="debug"
                                   {{ old('LOG_LEVEL', $settings['LOG_LEVEL']) === 'debug' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="log_level_debug">
                                <i class="bi bi-bug me-1"></i> Debug
                            </label>

                            <input type="radio" class="btn-check" name="LOG_LEVEL" id="log_level_info" value="info"
                                   {{ old('LOG_LEVEL', $settings['LOG_LEVEL']) === 'info' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="log_level_info">
                                <i class="bi bi-info-circle me-1"></i> Info
                            </label>

                            <input type="radio" class="btn-check" name="LOG_LEVEL" id="log_level_warning" value="warning"
                                   {{ old('LOG_LEVEL', $settings['LOG_LEVEL']) === 'warning' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="log_level_warning">
                                <i class="bi bi-exclamation-triangle me-1"></i> Warning
                            </label>
                        </div>
                        <div class="form-text">
                            Verbosity threshold for the activity log.
                            <strong>Debug</strong> records everything including model changes;
                            <strong>Info</strong> records user actions only (logins, PIREPs, invites);
                            <strong>Warning</strong> records security events only (failed logins).
                        </div>
                    </div>

                    {{-- Aircraft gallery --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-images me-2 text-primary"></i> Aircraft Screenshots</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="aircraft_image_max_filesize_kb" class="form-label">Max file size (KB)</label>
                            <input type="number" min="256" max="51200" class="form-control" id="aircraft_image_max_filesize_kb"
                                   name="aircraft_image_max_filesize_kb"
                                   value="{{ old('aircraft_image_max_filesize_kb', $settings['aircraft_image_max_filesize_kb']) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="aircraft_image_max_dimension" class="form-label">Max dimension (px)</label>
                            <input type="number" min="320" max="10000" class="form-control" id="aircraft_image_max_dimension"
                                   name="aircraft_image_max_dimension"
                                   value="{{ old('aircraft_image_max_dimension', $settings['aircraft_image_max_dimension']) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="aircraft_image_max_per_aircraft" class="form-label">Max images per aircraft</label>
                            <input type="number" min="1" max="100" class="form-control" id="aircraft_image_max_per_aircraft"
                                   name="aircraft_image_max_per_aircraft"
                                   value="{{ old('aircraft_image_max_per_aircraft', $settings['aircraft_image_max_per_aircraft']) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-text">
                                Limits for uploaded flight-simulator screenshots. Uploads larger than the max
                                file size or dimension are rejected, then re-encoded to WebP (stripping EXIF).
                                The max dimension is checked on both width and height.
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-2"></i> Save settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
