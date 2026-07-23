<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);

        // Reload from DB — the session model is a snapshot
        $airline = Airline::findOrFail($airline->id);

        // Owner first (represented by -1), then Managers (0), Dispatchers (1), Pilots (2); alphabetical within each.
        // Sorted in PHP so it stays portable across MySQL/SQLite (tests).
        $roleOrder = ['Manager' => 0, 'Dispatcher' => 1, 'Pilot' => 2];
        $members = $airline->users()
            ->orderBy('name')
            ->get()
            ->sortBy(fn ($member) => [
                $member->id === $airline->owner_user_id ? -1 : ($roleOrder[$member->pivot->role] ?? 99),
                $member->name
            ])
            ->values();

        return view('manager.members', compact('airline', 'members'));
    }

    public function update(Request $request, User $member)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($airline->isMember($member), 403);

        // Reload fresh to have accurate owner_user_id
        $airline = Airline::findOrFail($airline->id);

        $viewerIsOwner = auth()->user()->isOwnerOf($airline);
        $targetIsOwner = $airline->owner_user_id === $member->id;

        abort_if($targetIsOwner, 403);

        $request->validate(['role' => 'required|in:Pilot,Dispatcher,Manager']);

        if (! $viewerIsOwner) {
            $isTargetManager = $member->hasAirlineRole($airline, 'Manager');
            abort_if($isTargetManager || $request->role === 'Manager', 403);
        }

        $airline->users()->updateExistingPivot($member->id, ['role' => $request->role]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($airline)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('airline_member_role_changed')
            ->log($member->name . ' is now ' . $request->role . ' at ' . $airline->name);

        return back()->with('success', $member->name . "'s role updated to " . $request->role . '.');
    }

    public function destroy(User $member)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($airline->isMember($member), 403);

        // Reload fresh to have accurate owner_user_id
        $airline = Airline::findOrFail($airline->id);

        $viewerIsOwner = auth()->user()->isOwnerOf($airline);
        $targetIsOwner = $airline->owner_user_id === $member->id;

        abort_if($targetIsOwner, 403);

        if (! $viewerIsOwner) {
            $isTargetManager = $member->hasAirlineRole($airline, 'Manager');
            abort_if($isTargetManager, 403);
        }

        $airline->users()->detach($member->id);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($airline)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('airline_member_removed')
            ->log($member->name . ' was removed from ' . $airline->name);

        return back()->with('success', $member->name . ' has been removed from the airline.');
    }

    public function transferOwnership(User $member)
    {
        $airline = session('activeairline');

        abort_unless($airline, 403);
        $airline = Airline::findOrFail($airline->id);

        abort_unless(auth()->user()->isOwnerOf($airline), 403);
        abort_unless($airline->isMember($member), 403);
        abort_if($member->id === auth()->id(), 422);

        DB::transaction(function () use ($airline, $member) {
            // Promote new owner to Manager role in memberships
            $airline->users()->updateExistingPivot($member->id, ['role' => 'Manager']);
            // Keep old owner as Manager in memberships
            $airline->users()->updateExistingPivot(auth()->id(), ['role' => 'Manager']);
            
            $airline->owner_user_id = $member->id;
            $airline->save();
        });

        // re-put the fresh airline into the session
        session(['activeairline' => $airline]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($airline)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('airline_ownership_transferred')
            ->log('Ownership of ' . $airline->name . ' transferred to ' . $member->name);

        return back()->with('success', 'Ownership transferred to ' . $member->name . '. You are now a Manager.');
    }
}
