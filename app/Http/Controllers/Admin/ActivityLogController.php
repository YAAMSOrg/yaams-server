<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
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
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity', [
            'activities' => $activities,
            'levels'     => array_keys(ActivityLevel::LEVELS),
            'level'      => $level,
            'user'       => $user,
        ]);
    }
}
