<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function viewNotifications() {
        $notifications = auth()->user()->unreadNotifications;

        return view('dashboard.notifications', ['notifications' => $notifications]);
    }

    public function acknowledge(DatabaseNotification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== auth()->id()
            || $notification->notifiable_type !== auth()->user()->getMorphClass()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification dismissed.');
    }

    public function acknowledgeAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications cleared.');
    }

}
