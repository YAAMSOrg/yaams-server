<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFlightRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Http\Resources\V1\FlightResource;
use App\Models\Flight;
use App\Models\Airline;
use App\Events\FlightFiled;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepRejected;
use App\Support\ActivityLevel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

class FlightAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List current user's flights for a specific airline.
     */
    public function index(Request $request, Airline $airline)
    {
        if (! $request->user()->isMemberOf($airline)) {
            return response()->json(['message' => 'You are not a member of this airline.'], 403);
        }

        $user = $request->user();
        $query = Flight::with(['airline', 'aircraft', 'pilot', 'status'])
            ->where('pilot_id', $user->id)
            ->where('airline_id', $airline->id);

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        return new FlightCollection($query->orderBy('created_at', 'DESC')->paginate(15));
    }

    /**
     * Submit a new PIREP. Membership, airport/aircraft existence and the
     * location-continuity rule are enforced by StoreFlightRequest.
     */
    public function store(StoreFlightRequest $request, Airline $airline)
    {
        $user = $request->user();
        $validated = $request->validated();
        $aircraft = $request->aircraft();

        $flight = Flight::create($validated + [
            'pilot_id' => $user->id,
            'airline_id' => $airline->id,
        ]);

        // Location continuity: the airframe has physically moved to the arrival airport
        if ($airline->location_continuity) {
            $aircraft->update(['current_loc' => $validated['arrival_icao']]);
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

        return (new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * List pending PIREPs for an airline (for reviewers).
     */
    public function reviewList(Request $request, Airline $airline)
    {
        // Per-airline Dispatcher/Manager role - same rule as the web review pages.
        if (! $request->user()->canReviewFlightsFor($airline)) {
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
    public function accept(Request $request, Flight $flight)
    {
        $this->authorize('review', $flight);

        $flight->status_id = 2;
        $flight->save();

        // Notify the pilot (in-app + email). Channels live in PirepAccepted::via().
        Notification::send($flight->pilot, new PirepAccepted($flight));

        activity()
            ->causedBy($request->user())
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
        $this->authorize('review', $flight);

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
            ->causedBy($request->user())
            ->performedOn($flight)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('pirep_rejected')
            ->log('Rejected PIREP ' . $flight->full_flight_number);

        return new FlightResource($flight->load(['airline', 'aircraft', 'pilot', 'status']));
    }
}
