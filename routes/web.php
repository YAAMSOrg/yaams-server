<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AirlineMembershipController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\InviteCodeController;

// Setup wizard — only accessible before any user exists
Route::get('/setup', [SetupController::class, 'show'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Global Welcome / Guest landing page
Route::get("/", [HomeController::class, "index"])->name("home");

Route::middleware(['auth'])->group(function () {

    // -------------------------------------------------------------------------
    // Routes that do NOT require an active airline session
    // -------------------------------------------------------------------------

    // Airline Portal — join via invite code, always accessible
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::post('/portal/redeem', [PortalController::class, 'redeem'])->name('portal.redeem');

    // Airline switcher — no active airline required (that's the point)
    Route::match(["GET", "POST"], "/user/switchactiveairline", [
        AirlineMembershipController::class,
        "changeActiveAirline",
    ])->name("changeactiveairline");

    // Dashboard — no airline middleware; controller handles the redirect to /portal itself
    Route::get("/user/dashboard", [DashboardController::class, "index"])->name("dashboard");

    // -------------------------------------------------------------------------
    // Routes that require an active airline session
    // -------------------------------------------------------------------------

    Route::middleware(['airline'])->group(function () {

        // Invite code management (manager check enforced in controller)
        Route::get('/airline/invitecodes', [InviteCodeController::class, 'index'])->name('invitecodes.index');
        Route::post('/airline/invitecodes/generate', [InviteCodeController::class, 'generate'])->name('invitecodes.generate');
        Route::delete('/airline/invitecodes/{inviteCode}', [InviteCodeController::class, 'destroy'])->name('invitecodes.destroy');

        // Flight Management
        Route::match(["GET", "POST"], "/user/flights/add", [FlightController::class, "addFlight"])->name("flightadd");
        Route::get("/user/flights/review", [FlightController::class, "listReviewFlights"])->name("flightreviewindex");
        Route::post("/user/flights/review/{flight}/accept", [FlightController::class, "acceptFlight"])->name("flightreviewaccept");
        Route::post("/user/flights/review/{flight}/reject", [FlightController::class, "rejectFlight"])->name("flightreviewreject");
        Route::get("/user/flights/list", [FlightController::class, "displayFlightsForUser"])->name("flightlist");
        Route::get("/user/flights/view/{flight}", [FlightController::class, "view"])->name("viewflight");

        // Notifications
        Route::get("/user/notifications", [NotificationsController::class, "viewNotifications"])->name("usernotifications");
        Route::post("/user/notifications/{notification}/acknowledge", [NotificationsController::class, "acknowledge"])->name("notificationsacknowledge");

        // Fleet Management
        Route::match(["GET", "POST"], "/airline/fleetmanager", [AircraftController::class, "index"])->name("fleetmanager");
        Route::match(["GET", "POST"], "/airline/fleetmanager/create", [AircraftController::class, "create"])
            ->name("createaircraft")
            ->middleware(["can:add aircraft"]);
        Route::get("/airline/fleetmanager/view/{aircraft}", [AircraftController::class, "view"])->name("viewaircraft");
        Route::match(["GET", "POST"], "/airline/fleetmanager/edit/{aircraft}", [AircraftController::class, "edit"])
            ->name("editaircraft")
            ->middleware(["can:edit aircraft"]);
    });
});
