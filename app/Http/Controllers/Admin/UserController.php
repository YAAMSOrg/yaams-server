<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\DeleteUser;
use App\Actions\Users\ResetTwoFactor;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

/**
 * Instance-wide user management. Access is restricted to Super-Admins via the
 * `role:Super-Admin` middleware on the route group (see routes/web.php).
 *
 * Every mutation is recorded in the activity log with the admin as causer and
 * the managed user as subject.
 */
class UserController extends Controller
{
    public const STATUSES = ['active', 'suspended', 'unverified'];
    public const SORTS = ['newest', 'name', 'last_online'];

    public function index(Request $request)
    {
        $filters = [
            'q'      => trim((string) $request->query('q')),
            'status' => in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null,
            'role'   => $request->query('role') === 'super-admin' ? 'super-admin' : null,
            'tfa'    => in_array($request->query('tfa'), ['enabled', 'disabled'], true) ? $request->query('tfa') : null,
            'sort'   => in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest',
        ];

        $users = User::query()
            ->with('roles')
            ->withCount(['airlines', 'flights'])
            ->search($filters['q'])
            ->when($filters['status'] === 'active', fn ($q) => $q->whereNull('suspended_at'))
            ->when($filters['status'] === 'suspended', fn ($q) => $q->whereNotNull('suspended_at'))
            ->when($filters['status'] === 'unverified', fn ($q) => $q->whereNull('email_verified_at'))
            ->when($filters['role'], fn ($q) => $q->role('Super-Admin'))
            ->when($filters['tfa'] === 'enabled', fn ($q) => $q->whereNotNull('two_factor_confirmed_at'))
            ->when($filters['tfa'] === 'disabled', fn ($q) => $q->whereNull('two_factor_confirmed_at'))
            ->when($filters['sort'] === 'name', fn ($q) => $q->orderBy('name'))
            ->when($filters['sort'] === 'last_online', fn ($q) => $q->orderByDesc('last_online_at'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users'   => $users,
            'filters' => $filters,
            'counts'  => [
                'total'      => User::count(),
                'suspended'  => User::whereNotNull('suspended_at')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
            ],
        ]);
    }

    /**
     * JSON lookup backing the admin user picker (<x-admin.user-picker>).
     * Matches name or e-mail; with `airline` set, members of that airline are
     * left out so only addable users are offered.
     */
    public function lookup(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->search($term)
            ->when($request->filled('airline'), fn ($q) => $q->whereDoesntHave(
                'airlines',
                fn ($q) => $q->where('airline_id', $request->integer('airline')),
            ))
            ->orderBy('name')
            ->take(8)
            ->get(['id', 'name', 'email', 'suspended_at']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'suspended' => $user->isSuspended(),
                'avatar'    => Blade::render('<x-pilot-avatar :name="$name" size="32" />', ['name' => $user->name]),
            ]),
        ]);
    }

    public function show(Request $request, User $user, DeleteUser $deleteUser, ResetTwoFactor $resetTwoFactor)
    {
        $user->load(['roles', 'tokens' => fn ($q) => $q->latest()]);

        $airlines = $user->airlines()->orderBy('name')->get();

        $activities = Activity::query()
            ->with('causer')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id))
                ->orWhere(fn ($q) => $q->where('subject_type', $user->getMorphClass())->where('subject_id', $user->id)))
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', [
            'user'             => $user,
            'airlines'         => $airlines,
            'activities'       => $activities,
            'stats'            => [
                'flights'  => $user->flights()->count(),
                'accepted' => $user->flights()->where('status_id', 2)->count(),
                'pending'  => $user->flights()->where('status_id', 1)->count(),
            ],
            'isSelf'           => $request->user()->is($user),
            'hasTwoFactor'     => $resetTwoFactor->hasTwoFactor($user),
            'blockingAirlines' => $deleteUser->blockingAirlines($user),
        ]);
    }

    /**
     * Edit name, e-mail address and e-mail verification state.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validateWithBag('profile', [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email_verified' => ['required', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->boolean('email_verified') && ! $user->email_verified_at) {
            $user->email_verified_at = now();
        } elseif (! $request->boolean('email_verified')) {
            $user->email_verified_at = null;
        }

        $changes = array_keys($user->getDirty());
        $user->save();

        if ($changes) {
            $this->log($request, $user, ActivityLevel::INFO, 'user_updated_by_admin', 'Updated user profile', ['fields' => $changes]);
        }

        return $this->back($user, 'Profile saved.');
    }

    /**
     * Grant or revoke the global Super-Admin role.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['super_admin' => ['required', 'boolean']]);

        if ($request->boolean('super_admin')) {
            if (! $user->hasRole('Super-Admin')) {
                $user->assignRole('Super-Admin');
                $this->log($request, $user, ActivityLevel::WARNING, 'super_admin_granted', 'Granted Super-Admin role');
            }

            return $this->back($user, 'Super-Admin role granted.');
        }

        if ($user->hasRole('Super-Admin')) {
            $this->guardNotSelf($request, $user, 'You cannot revoke your own Super-Admin role.');
            $this->guardNotLastAdmin($user, 'This is the last active Super-Admin. Grant the role to someone else first.');

            $user->removeRole('Super-Admin');
            $this->log($request, $user, ActivityLevel::WARNING, 'super_admin_revoked', 'Revoked Super-Admin role');
        }

        return $this->back($user, 'Super-Admin role revoked.');
    }

    public function suspend(Request $request, User $user)
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $this->guardNotSelf($request, $user, 'You cannot suspend your own account.');
        $this->guardNotLastAdmin($user, 'This is the last active Super-Admin and cannot be suspended.');

        if (! $user->isSuspended()) {
            $user->forceFill([
                'suspended_at'      => now(),
                'suspension_reason' => $validated['reason'] ?? null,
            ])->save();

            $this->log($request, $user, ActivityLevel::WARNING, 'user_suspended', 'Suspended user', [
                'reason' => $validated['reason'] ?? null,
            ]);
        }

        return $this->back($user, 'User suspended. Their sessions end on the next request and API tokens stop working.');
    }

    public function reactivate(Request $request, User $user)
    {
        if ($user->isSuspended()) {
            $user->forceFill(['suspended_at' => null, 'suspension_reason' => null])->save();

            $this->log($request, $user, ActivityLevel::WARNING, 'user_reactivated', 'Reactivated user');
        }

        return $this->back($user, 'User reactivated.');
    }

    public function resetTwoFactor(Request $request, User $user, ResetTwoFactor $reset)
    {
        if (! $reset->hasTwoFactor($user)) {
            return $this->back($user, 'This user has no two-factor authentication set up.');
        }

        $reset($user, $request->user(), 'admin');

        return $this->back($user, 'Two-factor authentication removed. The user has been informed by e-mail.');
    }

    public function sendPasswordReset(Request $request, User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['action' => __($status)]);
        }

        $this->log($request, $user, ActivityLevel::INFO, 'password_reset_sent', 'Sent password reset link');

        return $this->back($user, 'Password reset link sent to '.$user->email.'.');
    }

    public function revokeTokens(Request $request, User $user)
    {
        $count = $user->tokens()->count();

        if ($count > 0) {
            $user->tokens()->delete();
            $this->log($request, $user, ActivityLevel::WARNING, 'api_tokens_revoked', 'Revoked all API tokens', ['count' => $count]);
        }

        return $this->back($user, "Revoked {$count} API token(s).");
    }

    public function destroy(Request $request, User $user, DeleteUser $deleteUser)
    {
        $request->validateWithBag('deleteUser', [
            'confirm_email' => ['required', 'string', Rule::in([$user->email])],
        ], [
            'confirm_email.in' => 'The e-mail address does not match.',
        ]);

        $this->guardNotSelf($request, $user, 'Delete your own account from your account settings instead.');
        $this->guardNotLastAdmin($user, 'This is the last active Super-Admin and cannot be deleted.');

        $blocking = $deleteUser->blockingAirlines($user);

        if ($blocking->isNotEmpty()) {
            throw ValidationException::withMessages([
                'action' => 'This user owns or is the only manager of '.$blocking->pluck('name')->join(', ')
                    .'. Transfer ownership or appoint another manager first.',
            ]);
        }

        $name = $user->name;
        $deleteUser($user, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', "User {$name} deleted.");
    }

    /**
     * Admins manage their own account via /settings - acting on themselves
     * here could lock them out mid-session.
     */
    private function guardNotSelf(Request $request, User $user, string $message): void
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }

    /**
     * The instance must always keep at least one Super-Admin who can sign in.
     */
    private function guardNotLastAdmin(User $user, string $message): void
    {
        if (! $user->hasRole('Super-Admin') || $user->isSuspended()) {
            return;
        }

        $otherActiveAdmins = User::role('Super-Admin')
            ->whereNull('suspended_at')
            ->whereKeyNot($user->id)
            ->exists();

        if (! $otherActiveAdmins) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }

    private function log(Request $request, User $user, string $level, string $event, string $description, array $properties = []): void
    {
        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['level' => $level] + $properties)
            ->event($event)
            ->log($description);
    }

    private function back(User $user, string $status)
    {
        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', $status);
    }
}
