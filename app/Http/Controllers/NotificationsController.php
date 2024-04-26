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
        $notifications = Notification::all();

        return view('dashboard.notifications', ['notifications' => $notifications]);
    }
    
}
