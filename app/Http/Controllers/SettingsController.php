<?php

namespace App\Http\Controllers;

use App\Models\AirlineMembership;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/**
 * User-facing account settings (profile, security, notification preferences).
 *
 * Distinct from Admin\SettingsController, which edits instance-wide settings.
 * Profile and password changes are handled by Fortify's existing routes; this
 * controller only renders the pages and persists the email-notification toggle.
 */
class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function profile()
    {
        return view('settings.profile');
    }

    public function security(Request $request)
    {
        return view('settings.security', [
            'tokens' => $request->user()->tokens()->latest()->get(),
            // Recovery codes are shown once (right after confirming/regenerating,
            // or after an explicit password-confirmed reveal) and hidden otherwise.
            'revealRecoveryCodes' => (bool) $request->session()->get('reveal_recovery_codes'),
        ]);
    }

    /**
     * Begin two-factor enrolment. Requires the account password, then generates
     * an unconfirmed secret; the user confirms it with a TOTP code afterwards.
     */
    public function enableTwoFactor(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $enable($request->user());

        return redirect()->route('settings.security');
    }

    /**
     * Finish enrolment by verifying a TOTP code. On success the fresh recovery
     * codes are revealed once.
     */
    public function confirmTwoFactor(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => ['required', 'string']]);

        $confirm($request->user(), $request->input('code'));

        return redirect()
            ->route('settings.security')
            ->with('status', 'two-factor-authentication-confirmed')
            ->with('reveal_recovery_codes', true);
    }

    /**
     * Regenerate the recovery codes (password-gated) and reveal the new set once.
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $generate($request->user());

        return redirect()
            ->route('settings.security')
            ->with('status', 'recovery-codes-generated')
            ->with('reveal_recovery_codes', true);
    }

    /**
     * Re-display the existing recovery codes once, behind a password check.
     */
    public function revealRecoveryCodes(Request $request)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        return redirect()
            ->route('settings.security')
            ->with('reveal_recovery_codes', true);
    }

    /**
     * Disable two-factor authentication (password-gated).
     */
    public function disableTwoFactor(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $disable($request->user());

        return redirect()
            ->route('settings.security')
            ->with('status', 'two-factor-authentication-disabled');
    }

    public function storeToken(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->input('token_name'));

        activity()
            ->causedBy($request->user())
            ->withProperties(['level' => ActivityLevel::INFO, 'token_name' => $token->accessToken->name])
            ->event('api_token_created')
            ->log('Created API token');

        // The plain-text token is flashed once and never retrievable again —
        // only its hash is stored in personal_access_tokens.
        return redirect()
            ->route('settings.security')
            ->with('status', 'token-created')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroyToken(Request $request, int $tokenId)
    {
        $token = $request->user()->tokens()->findOrFail($tokenId);
        $token->delete();

        activity()
            ->causedBy($request->user())
            ->withProperties(['level' => ActivityLevel::INFO, 'token_name' => $token->name])
            ->event('api_token_revoked')
            ->log('Revoked API token');

        return redirect()
            ->route('settings.security')
            ->with('status', 'token-revoked');
    }

    public function notifications()
    {
        return view('settings.notifications');
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->email_notifications = $request->boolean('email_notifications');
        $user->save();

        return redirect()
            ->route('settings.notifications')
            ->with('status', 'Notification preferences saved.');
    }

    public function danger()
    {
        return view('settings.danger');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password does not match your current password.'),
            ])->errorBag('deleteAccount');
        }

        // Refuse if the user is the sole Manager of any airline - deleting them
        // would leave that airline without a manager.
        $strandedAirlines = $user->airlines()
            ->wherePivot('role', 'Manager')
            ->get()
            ->filter(function ($airline) {
                return AirlineMembership::where('airline_id', $airline->id)
                    ->where('role', 'Manager')
                    ->count() <= 1;
            });

        if ($strandedAirlines->isNotEmpty()) {
            throw ValidationException::withMessages([
                'password' => __('You are the only manager of :airlines. Appoint another manager before deleting your account.', [
                    'airlines' => $strandedAirlines->pluck('name')->join(', '),
                ]),
            ])->errorBag('deleteAccount');
        }

        // Record the deletion before the causer row disappears; capture identity
        // in the properties so the audit entry stays meaningful.
        activity()
            ->causedBy($user)
            ->withProperties([
                'level' => ActivityLevel::INFO,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->log('Deleted own account');

        // Clean up user-keyed rows the database won't cascade on its own.
        $user->tokens()->delete();
        $user->notifications()->delete();
        $user->syncRoles([]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Flights are anonymized (pilot_id -> NULL) via the FK; memberships,
        // NOTAMs and invite codes cascade automatically.
        $user->delete();

        return redirect()->route('home')->with('status', 'account-deleted');
    }
}
