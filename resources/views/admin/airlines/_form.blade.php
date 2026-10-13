{{-- Airline profile + settings fields, shared by the admin create and edit
     forms. Expects $airline (null when creating) and $bag (error bag name). --}}
@php
    $value = fn (string $field, $default = null) => old($field, $airline?->{$field} ?? $default);
    $flag = fn (string $field, bool $default) => (string) old($field, $airline ? (int) $airline->{$field} : (int) $default) === '1';
@endphp
<div class="row g-3">
    <div class="col-12 col-md-8">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" required maxlength="255"
               class="form-control @error('name', $bag) is-invalid @enderror" value="{{ $value('name') }}">
        @error('name', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-4">
        <label for="founded_at" class="form-label">Founded</label>
        <input type="date" name="founded_at" id="founded_at"
               class="form-control @error('founded_at', $bag) is-invalid @enderror" value="{{ old('founded_at', $airline?->founded_at?->toDateString()) }}">
        @error('founded_at', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="icao_callsign" class="form-label">ICAO code</label>
        <input type="text" name="icao_callsign" id="icao_callsign" required minlength="2" maxlength="3"
               class="form-control text-uppercase font-monospace @error('icao_callsign', $bag) is-invalid @enderror" value="{{ $value('icao_callsign') }}">
        @error('icao_callsign', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="prefix" class="form-label">IATA prefix</label>
        <input type="text" name="prefix" id="prefix" required minlength="2" maxlength="2"
               class="form-control text-uppercase font-monospace @error('prefix', $bag) is-invalid @enderror" value="{{ $value('prefix') }}">
        @error('prefix', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="atc_callsign" class="form-label">ATC callsign</label>
        <input type="text" name="atc_callsign" id="atc_callsign" required minlength="2" maxlength="10"
               class="form-control text-uppercase @error('atc_callsign', $bag) is-invalid @enderror" value="{{ $value('atc_callsign') }}">
        @error('atc_callsign', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="hub" class="form-label">Hub (ICAO)</label>
        <input type="text" name="hub" id="hub" required maxlength="4"
               class="form-control text-uppercase font-monospace @error('hub', $bag) is-invalid @enderror" value="{{ $value('hub') }}">
        @error('hub', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="country" class="form-label">Country</label>
        <input type="text" name="country" id="country" required minlength="2" maxlength="2" placeholder="DE"
               class="form-control text-uppercase @error('country', $bag) is-invalid @enderror" value="{{ $value('country') }}">
        @error('country', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="website" class="form-label">Website</label>
        <input type="url" name="website" id="website" maxlength="255"
               class="form-control @error('website', $bag) is-invalid @enderror" value="{{ $value('website') }}">
        @error('website', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="3" maxlength="1000"
                  class="form-control @error('description', $bag) is-invalid @enderror">{{ $value('description') }}</textarea>
        @error('description', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="unit_is_lbs" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="unit_is_lbs" value="1" id="unit_is_lbs" @checked($flag('unit_is_lbs', false))>
            <label class="form-check-label" for="unit_is_lbs">Weights in lbs <span class="text-muted small">(off: kg)</span></label>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="require_pirep_review" value="0">
            <input class="form-check-input @error('require_pirep_review', $bag) is-invalid @enderror" type="checkbox" role="switch" name="require_pirep_review" value="1" id="require_pirep_review" @checked($flag('require_pirep_review', true))>
            <label class="form-check-label" for="require_pirep_review">Require PIREP review</label>
            @error('require_pirep_review', $bag)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="location_continuity" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="location_continuity" value="1" id="location_continuity" @checked($flag('location_continuity', false))>
            <label class="form-check-label" for="location_continuity">Location continuity <span class="text-muted small">(flights must depart where the aircraft is)</span></label>
        </div>
    </div>
</div>
