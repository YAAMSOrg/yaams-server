<?php

namespace App\Http\Controllers;

use App\Support\ActivityLevel;
use Illuminate\Http\Request;

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
        ]);
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
}
