<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AircraftController;

Route::get('/auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'store']);

Route::get('/auth/login', [LoginController::class, 'index'])->name('login');
Route::post('/auth/login', [LoginController::class, 'store']);

Route::post('/auth/logout', [LogoutController::class, 'store'])->name('logout');

Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('dashboard');


Route::match(['GET', 'POST'], '/user/flights/add', [FlightController::class, 'addFlight'])->name('addflight');
Route::get('/user/flights/list', [FlightController::class, 'displayFlightsForUser'])->name('flightlist');

Route::match(['GET', 'POST'], '/fleetmanager', [AircraftController::class, 'index'])->name('fleetmanager');
Route::match(['GET', 'POST'], '/fleetmanager/edit/{aircraft}', [AircraftController::class, 'edit'])->name('editfleet');

Route::get('/', function () {
    return view('home.index');
})->name('home');
