<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationsController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function viewNotifications() {
        $notifications = Notification::where('target_id', '=', auth()->user()->id)->where('acknowledged', '=', '0')->get();

        return view('dashboard.notifications', ['notifications' => $notifications]);
    }

    public function acknowledge(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->target_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->update(['acknowledged' => true]);

        return redirect()->back()->with('success', 'Notification dismissed.');
    }
    
}
