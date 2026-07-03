<?php

namespace App\Http\Controllers;

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

    public function security()
    {
        return view('settings.security');
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
