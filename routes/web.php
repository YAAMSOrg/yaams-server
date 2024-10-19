<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AirlineMembershipController;
use App\Http\Controllers\NotificationsController;

Route::get("/auth/register", [RegisterController::class, "index"])->name(
    "register"
);
Route::post("/auth/register", [RegisterController::class, "store"]);
Route::get("/auth/login", [LoginController::class, "index"])->name("login");
Route::post("/auth/login", [LoginController::class, "store"]);
Route::post("/auth/logout", [LogoutController::class, "store"])->name("logout");

Route::get("/user/dashboard", [DashboardController::class, "index"])->name(
    "dashboard"
);
Route::match(["GET", "POST"], "/user/switchactiveairline", [
    AirlineMembershipController::class,
    "changeActiveAirline",
])->name("changeactiveairline");
Route::match(["GET", "POST"], "/user/flights/add", [
    FlightController::class,
    "addFlight",
])->name("flightadd");
Route::get("/user/flights/review", [
    FlightController::class,
    "listReviewFlights",
])->name("flightreviewindex");
Route::get("/user/flights/list", [
    FlightController::class,
    "displayFlightsForUser",
])->name("flightlist");
Route::get("/user/flights/view/{flight}", [
    FlightController::class,
    "view",
])->name("viewflight");
Route::match(["GET", "POST"], "/user/flights/accept", [
    FlightController::class,
    "acceptFlight",
])->name("flightaccept");
Route::match(["GET", "POST"], "/user/flights/deny", [
    FlightController::class,
    "denyFlight",
])->name("flightdeny");

Route::get("/user/notifications", [
    NotificationsController::class,
    "viewNotifications",
])->name("usernotifications");

Route::match(["GET", "POST"], "/airline/fleetmanager", [
    AircraftController::class,
    "index",
])->name("fleetmanager");
Route::get("/airline/fleetmanager/view/{aircraft}", [
    AircraftController::class,
    "view",
])->name("viewaircraft");
Route::match(["GET", "POST"], "/airline/fleetmanager/edit/{aircraft}", [
    AircraftController::class,
    "edit",
])
    ->name("editaircraft")
    ->middleware(["role:Manager"]);

Route::get("/", function () {
    return view("home.index");
})->name("home");
