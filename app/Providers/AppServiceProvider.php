<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Fortify\SetActiveAirline;
use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreatesNewUsers::class, CreateNewUser::class);
        $this->app->bind(ResetsUserPasswords::class, ResetUserPassword::class);
        $this->app->bind(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->bind(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Expose the instance name to every view (brand + page title). Guarded
        // with hasTable so `artisan migrate` / fresh installs (before the
        // settings table exists) don't blow up.
        View::share('instanceName', Schema::hasTable('settings')
            ? Setting::get('app_name', config('app.name'))
            : config('app.name'));

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
            // Honour the instance-wide registration toggle (settings table).
            if (Setting::get('allow_registration', '1') !== '1') {
                return redirect()->route('login')
                    ->with('status', 'Registration is currently closed on this instance.');
            }

            return view('auth.register');
        });

        // No standalone "verify email" page — the portal shows the unverified
        // notice (with a resend link), so send anyone hitting the notice route there.
        Fortify::verifyEmailView(function () {
            return redirect()->route('portal');
        });

        // Password reset flow: request a reset link, then set a new password
        // from the emailed token link.
        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function (Request $request) {
            return view('auth.reset-password', ['request' => $request]);
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
    }
}