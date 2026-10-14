<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Airlines\DeleteAirline;
use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Models\Airline;
use App\Models\User;
use App\Notifications\AddedToAirline;
use App\Notifications\RemovedFromAirline;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

/**
 * Instance-wide airline management. Access is restricted to Super-Admins via
 * the `role:Super-Admin` middleware on the route group (see routes/web.php).
 *
 * Every mutation is recorded in the activity log with the admin as causer and
 * the managed airline as subject.
 */
class AirlineController extends Controller
{
    public const STATUSES = ['active', 'suspended', 'orphaned'];
    public const SORTS = ['newest', 'name', 'icao', 'members', 'flights'];
    public const ROLES = ['Pilot', 'Dispatcher', 'Manager'];

    public function index(Request $request)
    {
        $filters = [
            'q'      => trim((string) $request->query('q')),
            'status' => in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null,
            'review' => in_array($request->query('review'), ['on', 'off'], true) ? $request->query('review') : null,
            'sort'   => in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest',
        ];

        $airlines = Airline::query()
            ->with('owner')
            ->withCount([
                'users',
                'flights',
                'aircraft' => fn ($q) => $q->where('status', '!=', Aircraft::STATUS_RETIRED),
                'flights as pending_flights_count' => fn ($q) => $q->where('status_id', 1),
            ])
            ->search($filters['q'])
            ->when($filters['status'] === 'active', fn ($q) => $q->active())
            ->when($filters['status'] === 'suspended', fn ($q) => $q->suspended())
            ->when($filters['status'] === 'orphaned', fn ($q) => $q->whereNull('owner_user_id'))
            ->when($filters['review'] === 'on', fn ($q) => $q->where('require_pirep_review', true))
            ->when($filters['review'] === 'off', fn ($q) => $q->where('require_pirep_review', false))
            ->when($filters['sort'] === 'name', fn ($q) => $q->orderBy('name'))
            ->when($filters['sort'] === 'icao', fn ($q) => $q->orderBy('icao_callsign'))
            ->when($filters['sort'] === 'members', fn ($q) => $q->orderByDesc('users_count'))
            ->when($filters['sort'] === 'flights', fn ($q) => $q->orderByDesc('flights_count'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.airlines.index', [
            'airlines' => $airlines,
            'filters'  => $filters,
            'counts'   => [
                'total'     => Airline::count(),
                'suspended' => Airline::suspended()->count(),
                'orphaned'  => Airline::whereNull('owner_user_id')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.airlines.create', ['roles' => self::ROLES]);
    }

    /**
     * Found an airline on behalf of an existing user, who becomes its owner
     * and Manager.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->profileRules() + [
            'owner_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'owner_id.required' => 'Choose the user who will own the airline.',
        ]);

        $owner = User::findOrFail($validated['owner_id']);

        $airline = DB::transaction(function () use ($request, $validated, $owner) {
            $airline = Airline::create($this->profileAttributes($request, $validated) + [
                'owner_user_id' => $owner->id,
            ]);

            $airline->users()->attach($owner->id, ['role' => 'Manager']);

            return $airline;
        });

        $this->log($request, $airline, ActivityLevel::INFO, 'airline_created_by_admin', 'Created airline', [
            'owner' => $owner->name,
        ]);

        $this->notifyMember($request, $owner, new AddedToAirline($airline->name, $airline->icao_callsign, 'Manager', asOwner: true));

        return $this->back($airline, $airline->name.' has been founded with '.$owner->name.' as owner.');
    }

    public function show(Airline $airline)
    {
        $airline->load('owner');

        $roleOrder = array_flip(['Manager', 'Dispatcher', 'Pilot']);
        $members = $airline->users()
            ->orderBy('name')
            ->get()
            ->sortBy(fn ($member) => [
                $member->id === $airline->owner_user_id ? -1 : ($roleOrder[$member->pivot->role] ?? 99),
                $member->name,
            ])
            ->values();

        $flightsByStatus = $airline->flights()
            ->selectRaw('status_id, count(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $aircraftByStatus = $airline->aircraft()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $aircraftIds = $airline->aircraft()->select('id');

        $activities = Activity::query()
            ->with('causer')
            ->where('subject_type', $airline->getMorphClass())
            ->where('subject_id', $airline->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.airlines.show', [
            'airline'    => $airline,
            'members'    => $members,
            'roles'      => self::ROLES,
            'activities' => $activities,
            'stats'      => [
                'pending'          => $flightsByStatus[1] ?? 0,
                'accepted'         => $flightsByStatus[2] ?? 0,
                'rejected'         => $flightsByStatus[3] ?? 0,
                'aircraftActive'   => $aircraftByStatus[Aircraft::STATUS_ACTIVE] ?? 0,
                'aircraftInactive' => $aircraftByStatus[Aircraft::STATUS_INACTIVE] ?? 0,
                'aircraftRetired'  => $aircraftByStatus[Aircraft::STATUS_RETIRED] ?? 0,
                'pendingImages'    => AircraftImage::whereIn('aircraft_id', $aircraftIds)
                    ->where('status', AircraftImage::STATUS_PENDING)
                    ->count(),
                'notams'           => $airline->notams()->active()->count(),
                'inviteCodes'      => $airline->inviteCodes()->whereNull('used_by')->count(),
            ],
        ]);
    }

    /**
     * Edit the airline profile and its per-airline settings.
     */
    public function update(Request $request, Airline $airline)
    {
        $validated = $request->validateWithBag('profile', $this->profileRules());

        // Same safeguard as the manager settings page: pending flights would be
        // stranded in a review queue that is no longer reachable.
        if ($airline->require_pirep_review
            && ! $request->boolean('require_pirep_review')
            && $airline->flights()->where('status_id', 1)->exists()) {
            throw ValidationException::withMessages([
                'require_pirep_review' => 'PIREP review cannot be disabled while flights are still pending review.',
            ])->errorBag('profile');
        }

        $airline->fill($this->profileAttributes($request, $validated));

        $changes = array_keys($airline->getDirty());
        $airline->save();

        if ($changes) {
            $this->log($request, $airline, ActivityLevel::INFO, 'airline_updated_by_admin', 'Updated airline profile', ['fields' => $changes]);
        }

        return $this->back($airline, 'Airline saved.');
    }

    public function suspend(Request $request, Airline $airline)
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        if (! $airline->isSuspended()) {
            $airline->forceFill([
                'suspended_at'      => now(),
                'suspension_reason' => $validated['reason'] ?? null,
            ])->save();

            $this->log($request, $airline, ActivityLevel::WARNING, 'airline_suspended', 'Suspended airline', [
                'reason' => $validated['reason'] ?? null,
            ]);
        }

        return $this->back($airline, 'Airline suspended. It is read-only for its members until reactivated.');
    }

    public function reactivate(Request $request, Airline $airline)
    {
        if ($airline->isSuspended()) {
            $airline->forceFill(['suspended_at' => null, 'suspension_reason' => null])->save();

            $this->log($request, $airline, ActivityLevel::WARNING, 'airline_reactivated', 'Reactivated airline');
        }

        return $this->back($airline, 'Airline reactivated.');
    }

    /**
     * Add an existing user directly, without an invite code.
     */
    public function storeMember(Request $request, Airline $airline)
    {
        $validated = $request->validateWithBag('addMember', [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role'    => ['required', Rule::in(self::ROLES)],
        ], [
            'user_id.required' => 'Search for a user and pick one from the list.',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($airline->isMember($user)) {
            throw ValidationException::withMessages(['user_id' => $user->name.' is already a member.'])->errorBag('addMember');
        }

        $airline->users()->attach($user->id, ['role' => $validated['role']]);

        $this->log($request, $airline, ActivityLevel::INFO, 'airline_member_added_by_admin',
            $user->name.' was added to '.$airline->name.' as '.$validated['role'], ['user' => $user->name, 'role' => $validated['role']]);

        $this->notifyMember($request, $user, new AddedToAirline($airline->name, $airline->icao_callsign, $validated['role']));

        return $this->back($airline, $user->name.' added as '.$validated['role'].'.');
    }

    public function updateMember(Request $request, Airline $airline, User $user)
    {
        $validated = $request->validate(['role' => ['required', Rule::in(self::ROLES)]]);

        $this->guardMember($airline, $user);

        if ($validated['role'] !== 'Manager') {
            if ($airline->isOwnedBy($user)) {
                throw ValidationException::withMessages(['action' => 'The owner must stay a Manager. Transfer ownership first.']);
            }
            $this->guardNotLastManager($airline, $user, 'This is the last Manager of the airline and cannot be demoted.');
        }

        $airline->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        $this->log($request, $airline, ActivityLevel::INFO, 'airline_member_role_changed_by_admin',
            $user->name.' is now '.$validated['role'].' at '.$airline->name, ['user' => $user->name, 'role' => $validated['role']]);

        return $this->back($airline, $user->name."'s role updated to ".$validated['role'].'.');
    }

    public function destroyMember(Request $request, Airline $airline, User $user)
    {
        $this->guardMember($airline, $user);

        if ($airline->isOwnedBy($user)) {
            throw ValidationException::withMessages(['action' => 'The owner cannot be removed. Transfer ownership first.']);
        }
        $this->guardNotLastManager($airline, $user, 'This is the last Manager of the airline and cannot be removed.');

        $airline->users()->detach($user->id);

        $this->log($request, $airline, ActivityLevel::INFO, 'airline_member_removed_by_admin',
            $user->name.' was removed from '.$airline->name, ['user' => $user->name]);

        $this->notifyMember($request, $user, new RemovedFromAirline($airline->name, $airline->icao_callsign));

        return $this->back($airline, $user->name.' has been removed from the airline.');
    }

    /**
     * Make a member the owner. Works for orphaned airlines (owner deleted) too.
     * The new owner becomes Manager; the previous owner stays a Manager.
     */
    public function transferOwnership(Request $request, Airline $airline)
    {
        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $user = User::findOrFail($validated['user_id']);
        $this->guardMember($airline, $user);

        if ($airline->isOwnedBy($user)) {
            return $this->back($airline, $user->name.' already owns this airline.');
        }

        $previousOwner = $airline->owner;

        DB::transaction(function () use ($airline, $user) {
            $airline->users()->updateExistingPivot($user->id, ['role' => 'Manager']);
            $airline->owner_user_id = $user->id;
            $airline->save();
        });

        $this->log($request, $airline, ActivityLevel::WARNING, 'airline_ownership_transferred_by_admin',
            'Ownership of '.$airline->name.' transferred to '.$user->name, [
                'from' => $previousOwner?->name,
                'to'   => $user->name,
            ]);

        return $this->back($airline, 'Ownership transferred to '.$user->name.'.');
    }

    public function destroy(Request $request, Airline $airline, DeleteAirline $deleteAirline)
    {
        $request->validateWithBag('deleteAirline', [
            'confirm_icao' => ['required', 'string', Rule::in([$airline->icao_callsign])],
        ], [
            'confirm_icao.in' => 'The ICAO code does not match.',
        ]);

        $name = $airline->name;
        $deleteAirline($airline, $request->user());

        return redirect()
            ->route('admin.airlines.index')
            ->with('status', "Airline {$name} deleted.");
    }

    /**
     * Profile + settings rules, shared by create and update. Mirrors the
     * founding form (AirlineController::foundStore()).
     */
    private function profileRules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'prefix'               => ['required', 'string', 'size:2', 'alpha'],
            'icao_callsign'        => ['required', 'string', 'min:2', 'max:3', 'alpha'],
            'atc_callsign'         => ['required', 'alpha', 'min:2', 'max:10'],
            'hub'                  => ['required', 'string', 'max:4', 'exists:airports,icao_code'],
            'country'              => ['required', 'string', 'size:2', 'alpha'],
            'description'          => ['nullable', 'string', 'max:1000'],
            'website'              => ['nullable', 'url', 'max:255'],
            'founded_at'           => ['nullable', 'date'],
            'unit_is_lbs'          => ['required', 'boolean'],
            'require_pirep_review' => ['required', 'boolean'],
            'location_continuity'  => ['required', 'boolean'],
        ];
    }

    private function profileAttributes(Request $request, array $validated): array
    {
        return [
            'name'                 => $validated['name'],
            'prefix'               => strtoupper($validated['prefix']),
            'icao_callsign'        => strtoupper($validated['icao_callsign']),
            'atc_callsign'         => strtoupper($validated['atc_callsign']),
            'hub'                  => strtoupper($validated['hub']),
            'country'              => strtoupper($validated['country']),
            'description'          => $validated['description'] ?? null,
            'website'              => $validated['website'] ?? null,
            'founded_at'           => $validated['founded_at'] ?? now()->toDateString(),
            'unit_is_lbs'          => $request->boolean('unit_is_lbs'),
            'require_pirep_review' => $request->boolean('require_pirep_review'),
            'location_continuity'  => $request->boolean('location_continuity'),
        ];
    }

    private function guardMember(Airline $airline, User $user): void
    {
        if (! $airline->isMember($user)) {
            throw ValidationException::withMessages(['action' => $user->name.' is not a member of this airline.']);
        }
    }

    /**
     * An airline must always keep at least one Manager who can run it.
     */
    private function guardNotLastManager(Airline $airline, User $user, string $message): void
    {
        if (! $user->hasAirlineRole($airline, 'Manager')) {
            return;
        }

        if (! $airline->managers()->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }

    /**
     * Membership changes are announced to the affected user, unless the admin
     * changed their own membership.
     */
    private function notifyMember(Request $request, User $user, AddedToAirline|RemovedFromAirline $notification): void
    {
        if ($request->user()->isNot($user)) {
            $user->notify($notification);
        }
    }

    private function log(Request $request, Airline $airline, string $level, string $event, string $description, array $properties = []): void
    {
        activity()
            ->causedBy($request->user())
            ->performedOn($airline)
            ->withProperties(['level' => $level] + $properties)
            ->event($event)
            ->log($description);
    }

    private function back(Airline $airline, string $status)
    {
        return redirect()
            ->route('admin.airlines.show', $airline)
            ->with('status', $status);
    }
}
