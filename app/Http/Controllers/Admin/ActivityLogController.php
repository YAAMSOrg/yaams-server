<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\InviteCode;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Browse the instance-wide activity log. Access is restricted to
     * Super-Admins via the `role:Super-Admin` middleware on the route group
     * (see routes/web.php).
     */
    public function index(Request $request)
    {
        $level = $request->query('level');

        // Optional per-user filter: everything the user did or that was done to them.
        $user = $request->filled('user') ? User::find($request->integer('user')) : null;

        // Optional per-airline filter: entries about the airline itself or about
        // its flights, aircraft and invite codes.
        $airline = $request->filled('airline') ? Airline::find($request->integer('airline')) : null;

        $activities = Activity::with([
                'causer',
                // Constrain the polymorphic subject load so aircraft rows can render
                // their operating airline (see ActivityLabel) without an N+1.
                'subject' => fn ($morphTo) => $morphTo->morphWith([Aircraft::class => ['airline']]),
            ])
            ->when(in_array($level, array_keys(ActivityLevel::LEVELS), true), fn ($q) => $q->where('level', $level))
            ->when($user, fn ($q) => $q->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id))
                ->orWhere(fn ($q) => $q->where('subject_type', $user->getMorphClass())->where('subject_id', $user->id))))
            ->when($airline, fn ($q) => $q->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('subject_type', $airline->getMorphClass())->where('subject_id', $airline->id))
                ->orWhereHasMorph('subject', [Flight::class, InviteCode::class], fn ($q) => $q->where('airline_id', $airline->id))
                ->orWhereHasMorph('subject', [Aircraft::class], fn ($q) => $q->where('used_by', $airline->id))))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity', [
            'activities' => $activities,
            'levels'     => array_keys(ActivityLevel::LEVELS),
            'level'      => $level,
            'user'       => $user,
            'airline'    => $airline,
        ]);
    }
}
