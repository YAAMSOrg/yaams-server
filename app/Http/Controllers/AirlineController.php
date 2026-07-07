<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Setting;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;

class AirlineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function canFoundAirline(): bool
    {
        return auth()->user()->hasRole('Super-Admin')
            || Setting::get('allow_user_airline_creation') === '1';
    }

    public function found()
    {
        abort_unless($this->canFoundAirline(), 403);

        return view('airline.found');
    }

    public function foundStore(Request $request)
    {
        abort_unless($this->canFoundAirline(), 403);

        $validated = $request->validate([
            'airline_name'     => 'required|string|max:255',
            'airline_prefix'   => 'required|string|size:2|alpha',
            'airline_icao'     => 'required|string|min:2|max:3|alpha',
            'airline_callsign' => 'required|alpha|min:2|max:10',
            'airline_hub'      => 'required|string|max:4|exists:airports,icao_code',
            'airline_country'  => 'required|string|size:2|alpha',
            'airline_desc'     => 'nullable|string|max:1000',
            'airline_website'  => 'nullable|url|max:255',
            'airline_founded'  => 'nullable|date',
            'unit_is_lbs'      => 'nullable|boolean',
        ]);

        $airline = Airline::create([
            'name'                 => $validated['airline_name'],
            'prefix'               => strtoupper($validated['airline_prefix']),
            'icao_callsign'        => strtoupper($validated['airline_icao']),
            'atc_callsign'         => strtoupper($validated['airline_callsign']),
            'hub'                  => strtoupper($validated['airline_hub']),
            'country'              => strtoupper($validated['airline_country']),
            'description'          => $validated['airline_desc'] ?? null,
            'website'              => $validated['airline_website'] ?? null,
            'founded_at'           => $validated['airline_founded'] ?? now()->toDateString(),
            'unit_is_lbs'          => $request->boolean('unit_is_lbs'),
            'active'               => true,
            'require_pirep_review' => true,
        ]);

        $airline->users()->attach(auth()->id(), ['role' => 'Manager']);
        $request->session()->put('activeairline', $airline);

        return redirect()->route('dashboard')
            ->with('success', $airline->name . ' has been founded. Welcome aboard as Manager!');
    }

    public function settings()
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        // Reload from DB — the session model is a snapshot
        $airline = Airline::findOrFail($airline->id);

        return view('manager.settings', compact('airline'));
    }

    public function updateSettings(Request $request)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        $airline = Airline::findOrFail($airline->id);

        // Review cannot be switched off while flights are still pending — they would be
        // stranded in a review queue that is no longer reachable from the nav.
        if ($airline->require_pirep_review
            && ! $request->boolean('require_pirep_review')
            && $airline->flights()->where('status_id', 1)->exists()) {
            return back()->withErrors([
                'require_pirep_review' => 'PIREP review cannot be disabled while flights are still pending review. Accept or reject them first.',
            ]);
        }

        $airline->update([
            'location_continuity'  => $request->boolean('location_continuity'),
            'require_pirep_review' => $request->boolean('require_pirep_review'),
        ]);

        // Keep the session snapshot in sync so the PIREP form sees the new flag immediately
        $request->session()->put('activeairline', $airline);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($airline)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('airline_settings_updated')
            ->log('Updated airline settings for ' . $airline->name);

        return back()->with('success', 'Airline settings saved.');
    }
}

