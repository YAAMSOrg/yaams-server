<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;

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

        // Managers first, then Dispatchers, then Pilots; alphabetical within each.
        // Sorted in PHP so it stays portable across MySQL/SQLite (tests).
        $roleOrder = ['Manager' => 0, 'Dispatcher' => 1, 'Pilot' => 2];
        $members = $airline->users()
            ->orderBy('name')
            ->get()
            ->sortBy(fn ($member) => [$roleOrder[$member->pivot->role] ?? 99, $member->name])
            ->values();

        return view('manager.members', compact('airline', 'members'));
    }

    public function update(Request $request, User $member)
    {
        $airline = session('activeairline');

        abort_unless($airline && auth()->user()->isManagerOf($airline), 403);
        abort_unless($airline->isMember($member), 403);

        $request->validate(['role' => 'required|in:Pilot,Dispatcher,Manager']);

        if ($this->wouldRemoveLastManager($airline, $member, $request->role)) {
            return back()->withErrors([
                'role' => 'You cannot demote the last remaining Manager. Promote another member to Manager first.',
            ]);
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

        if ($this->wouldRemoveLastManager($airline, $member, null)) {
            return back()->withErrors([
                'member' => 'You cannot remove the last remaining Manager. Promote another member to Manager first.',
            ]);
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

    /**
     * Guard against leaving the airline with no Manager. A change only endangers the
     * last Manager when it demotes (new role other than Manager) or removes ($newRole
     * === null) a member who is currently a Manager and no other Manager remains.
     */
    private function wouldRemoveLastManager(Airline $airline, User $member, ?string $newRole): bool
    {
        if (! $member->isManagerOf($airline) || $newRole === 'Manager') {
            return false;
        }

        $otherManagers = $airline->users()
            ->wherePivot('role', 'Manager')
            ->where('users.id', '!=', $member->id)
            ->count();

        return $otherManagers === 0;
    }
}
