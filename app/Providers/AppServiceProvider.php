<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\SetActiveAirline;
use App\Channels\InAppChannel;
use App\Events\FlightFiled;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreatesNewUsers::class, CreateNewUser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify rate limiters — referenced by config('fortify.limiters'),
        // which makes Fortify attach throttle:login / throttle:two-factor
        // middleware to its routes. Without these definitions the throttle
        // middleware throws MissingRateLimiterException on login.
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Fortify Views registrieren
        Fortify::loginView(function () {
            return view('auth.login'); 
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // No standalone "verify email" page — the portal shows the unverified
        // notice (with a resend link), so send anyone hitting the notice route there.
        Fortify::verifyEmailView(function () {
            return redirect()->route('portal');
        });

        // Die Login-Pipeline von Fortify anpassen:
        Fortify::authenticateThrough(function () {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
                SetActiveAirline::class, // <- Hier klinken wir uns ein, sobald die Session bereit ist!
            ]);
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });

        // Register the custom "inapp" notification channel so notifications can
        // list it in their via() alongside built-in channels like "mail".
        Notification::extend('inapp', function ($app) {
            return new InAppChannel();
        });
    }
}