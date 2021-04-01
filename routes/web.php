<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;

Route::get('/auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'store']);

Route::get('/auth/login', [LoginController::class, 'index'])->name('login');
Route::post('/auth/login', [LoginController::class, 'store']);

Route::post('/auth/logout', [LogoutController::class, 'store'])->name('logout');


Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/user/flights/add', [FlightController::class, 'index'])->name('addflight');
Route::get('/user/flights/list', function () {
    return view('flights.list');
})->name('flightlist');



Route::get('/', function () {
    return view('home.index');
})->name('home');
