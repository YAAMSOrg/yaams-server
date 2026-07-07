<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FlightCollection;
use App\Http\Resources\V1\FlightResource;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\Airport;
use App\Events\FlightFiled;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepRejected;
use App\Support\ActivityLevel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class FlightAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List current user's flights.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Flight::with(['airline', 'aircraft', 'pilot', 'status'])
            ->where('pilot_id', $user->id);

        if ($request->has('airline_id')) {
            $query->where('airline_id', $request->airline_id);
        }

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        return new FlightCollection($query->orderBy('created_at', 'DESC')->paginate(15));
    }

    /**
     * Submit a new PIREP.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            "airline_id" => "required|exists:airlines,id",
            "flightnumber" => "numeric|digits_between:1,4|required",
            "departure_icao" => "alpha|max:4|required",
            "arrival_icao" => "alpha|max:4|required",
            "aircraft_id" => "numeric|required",
            "callsign" => [
                "required",
                "max:4",
                "regex:/^[0-9]{1,4}[A-Za-z]{0,2}$/",
            ],
            "crzalt" => "numeric|max:50000|digits_between:1,5|required",
            "blockoff" => "required|date_format:Y-m-d H:i:s",
            "blockon" => "required|date_format:Y-m-d H:i:s",
            "burned_fuel" => "numeric|required",
            "route" => "required",
            "online_network_id" => "required|exists:online_networks,id",
            "remarks" => "nullable|regex:/^[\pL\s\d\.\,\-]+$/u",
        ]);

        $airline = Airline::findOrFail($validated['airline_id']);

        // Check if user is member of the airline
        if (!$user->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        // Validate Airports
        if (!Airport::find($validated['departure_icao'])) {
            throw ValidationException::withMessages(['departure_icao' => 'Departure airport not found.']);
        }
        if (!Airport::find($validated['arrival_icao'])) {
            throw ValidationException::withMessages(['arrival_icao' => 'Arrival airport not found.']);
        }

        // Validate Aircraft
        $aircraft = Aircraft::query()
            ->where("id", "=", $validated['aircraft_id'])
            ->where("used_by", "=", $airline->id)
            ->where("status", Aircraft::STATUS_ACTIVE)
            ->first();
        if (is_null($aircraft)) {
            throw ValidationException::withMessages(['aircraft_id' => 'This aircraft is not available or not owned by your airline.']);
        }

        // Location continuity: the flight must depart from where the airframe currently is
        if ($airline->location_continuity
            && strtoupper($validated['departure_icao']) !== strtoupper((string) $aircraft->current_loc)) {
            throw ValidationException::withMessages([
                'departure_icao' => 'Location continuity is enabled: ' . $aircraft->registration
                    . ' is currently located at ' . ($aircraft->current_loc ?: 'an unknown location') . '.',
            ]);
        }

        $flight = Flight::create($validated + ['pilot_id' => $user->id]);

        // Location continuity: the airframe has physically moved to the arrival airport
        if ($airline->location_continuity) {
            $aircraft->update(['current_loc' => strtoupper($validated['arrival_icao'])]);
        }

        if ($airline->require_pirep_review) {
            event(new FlightFiled($flight));
        } else {
            // Auto-accept: the airline does not require PIREP review, so nothing is dispatched to reviewers
            $flight->status_id = 2;
            $flight->save();
        }

        activity()
            ->causedBy($user)
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_filed')
            ->log('Filed PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }

    /**
     * List pending PIREPs for an airline (for reviewers).
     */
    public function reviewList(Airline $airline)
    {
        $user = request()->user();

        // Per-airline Dispatcher/Manager role - same rule as the web review pages.
        if (!$user->canReviewFlightsFor($airline)) {
            return response()->json(['message' => 'You do not have permission to review flights for this airline.'], 403);
        }

        $flights = Flight::with(['airline', 'aircraft', 'pilot', 'status'])
            ->where('airline_id', $airline->id)
            ->where('status_id', 1)
            ->orderBy('created_at', 'DESC')
            ->get();

        return new FlightCollection($flights);
    }

    /**
     * Accept a PIREP.
     */
    public function accept(Flight $flight)
    {
        $user = request()->user();

        // Per-airline Dispatcher/Manager role - same rule as the web review pages.
        if (!$user->canReviewFlightsFor($flight->airline)) {
            return response()->json(['message' => 'You do not have permission to review flights for this airline.'], 403);
        }

        if ($flight->pilot_id === $user->id
            && !$user->isManagerOf($flight->airline)
            && !$user->hasRole('Super-Admin')) {
            return response()->json(['message' => 'Dispatchers cannot accept their own PIREP.'], 403);
        }

        $flight->status_id = 2;
        $flight->save();

        // Notify the pilot (in-app + email). Channels live in PirepAccepted::via().
        Notification::send($flight->pilot, new PirepAccepted($flight));

        activity()
            ->causedBy($user)
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_accepted')
            ->log('Accepted PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }

    /**
     * Reject a PIREP.
     */
    public function reject(Request $request, Flight $flight)
    {
        $user = $request->user();

        // Per-airline Dispatcher/Manager role - same rule as the web review pages.
        if (!$user->canReviewFlightsFor($flight->airline)) {
            return response()->json(['message' => 'You do not have permission to review flights for this airline.'], 403);
        }

        if ($flight->pilot_id === $user->id
            && !$user->isManagerOf($flight->airline)
            && !$user->hasRole('Super-Admin')) {
            return response()->json(['message' => 'Dispatchers cannot reject their own PIREP.'], 403);
        }

        $wasPending = $flight->status_id === 1;

        $flight->status_id = 3;

        if ($request->has('rejection_remarks')) {
            $flight->rejection_remarks = $request->input('rejection_remarks');
        }

        $flight->save();

        // Location continuity: undo the movement from filing, unless a later flight has already moved the airframe on
        if ($wasPending
            && $flight->airline->location_continuity
            && strtoupper((string) $flight->aircraft->current_loc) === strtoupper($flight->arrival_icao)) {
            $flight->aircraft->update(['current_loc' => strtoupper($flight->departure_icao)]);
        }

        // Notify the pilot (in-app + email). Channels live in PirepRejected::via().
        Notification::send($flight->pilot, new PirepRejected($flight));

        activity()
            ->causedBy($user)
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_rejected')
            ->log('Rejected PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }
}
