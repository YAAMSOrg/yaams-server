# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project Overview

YAAMS (Yet Another Airline Management System) is a Laravel 12.x application providing a virtual airline management system with two parallel interfaces:
- **Web UI** — Blade templates + Bootstrap 5.3.3, served via session auth (Fortify)
- **REST API v1** — JSON responses at `/api/v1/`, authenticated via Laravel Sanctum tokens

## Commands

All php artisan commands have to run using the Docker container in which PHP runs. For example: `docker exec yaams-dev-app php artisan`

### Database Setup / Reset
```bash
docker exec yaams-dev-app php artisan migrate --seed
```
Seeder prints Sanctum API tokens for the three test users to stdout.

### Testing
```bash
docker exec yaams-dev-app php artisan test
```
PHPUnit suite under `tests/` (Unit + Feature, incl. `tests/Feature/Api`). Tests run against SQLite `:memory:` (configured in `phpunit.xml`), so no database service is needed. Feature tests `use RefreshDatabase, SeedsDomain` (`tests/Concerns/SeedsDomain.php`) — the trait seeds the reference data `RefreshDatabase` does not (flight statuses, online networks, Spatie roles, a few airports) and provides the `memberOf($airline, $role)` builder. Model factories live in `database/factories/`. The suite focuses on the documented tricky rules (location continuity, PIREP review toggle, review authorization, aircraft lifecycle, invite redemption) and web-↔-API parity. CI runs it on every PR/push to `main` via `.github/workflows/tests.yml`.

## Architecture

### Page Titles

Every web page uses the single title scheme `{Page Title} - {Instance Name}`:

- All four layouts (`app`, `landing`, `loginlayout`, `setuplayout` in `resources/views/layouts/`) render the tag themselves: `<title>@hasSection('title')@yield('title') - {{ $instanceName }}@else{{ $instanceName }}@endif</title>`
- Views declare only the bare page name, e.g. `@section('title', 'Fleet Overview')` - never hardcode the app name, "YAAMS", or a separator in a view
- `$instanceName` is `View::share`d in `AppServiceProvider` (the `app_name` setting, falling back to `config('app.name')`), so the configured instance name appears in every title
- The public landing page (`home/index.blade.php`) sets no title section on purpose - it shows just the instance name

### Authentication & Authorization

Two auth systems coexist:
- **Fortify** — web session login/register at `app/Actions/Fortify/`; `CreateNewUser` implements the `CreatesNewUsers` contract and is bound in `AppServiceProvider::register()`
- **Sanctum** — stateless API token auth; `auth:sanctum` middleware on all `/api/v1/` routes

Two role systems coexist — do not confuse them:
- **Spatie roles** (global): `Pilot`, `Manager` (`add aircraft`, `edit aircraft`, `review flight`), `Super-Admin` (bypasses all checks via `Gate::before` in `AppServiceProvider`). The `AircraftPolicy` handles model-level aircraft authorization.
- **Per-airline role** in `airline_memberships.role` (enum: `Pilot`, `Dispatcher`, `Manager`): checked via `User::isManagerOf()`, `hasAirlineRole()`, `canReviewFlightsFor()`. This is what controls invite code generation and flight review access.

### Instance Settings

Instance-wide configuration is stored as key/value rows in the `settings` table (`key` is the string PK). Read values via `Setting::get('key', $default)` (`app/Models/Setting.php`); write via `Setting::set('key', $value)`. Every known key and its fallback value lives in `Setting::defaults()` — `get()` falls back to that when a row is missing, so callers never hardcode defaults. Boolean settings are stored as the strings `'1'`/`'0'`.

New settings must be wired into all three places:
- `Setting::defaults()` — declare the key and its default value
- The `/setup` wizard (`SetupController::store()` + `resources/views/setup/index.blade.php`) — written on first install
- The admin settings page (`Admin\SettingsController` + `resources/views/admin/settings.blade.php`) — editable after install by a Super-Admin

Current setting keys:
- `app_name` — display name shown in the page title and header (default: `config('app.name')`, `'YAAMS'`)
- `timezone` — instance display timezone (a PHP timezone identifier, default: `config('app.timezone')`, `'UTC'`). The DB stores everything in UTC; this is used only for admin- and crew-facing datetimes (e.g. NOTAM expiry) via `App\Support\Timezone` (`current()`, `toUtc()`, `format()`). Flight/PIREP times are aviation data and intentionally stay in UTC (Zulu) — never route them through `Timezone`.
- `support_email` — optional contact address; shown in the site footer as a mailto link when set (default: `null`)
- `allow_user_airline_creation` — `'1'` if any registered user may found an airline; `'0'` (default) if only Super-Admins can
- `allow_registration` — `'1'` (default) if public self-registration is open; `'0'` if new users cannot self-register
- `show_public_stats` — `'1'` if the totals (airlines, pilots, flights, hours) are shown on the public landing page; `'0'` if they are hidden
- `LOG_LEVEL` — activity-log verbosity threshold: `debug` (default) records everything incl. model changes, `info` records user actions only, `warning` records security events only. Wired into `Setting::defaults()` + the admin settings page but **intentionally not** the setup wizard (see Application Logging below).
- `aircraft_image_max_filesize_kb` — max accepted upload size for an aircraft screenshot, in KB (default `4096`).
- `aircraft_image_max_dimension` — max accepted width **and** height for an aircraft screenshot, in px (default `4000`). Enforced as a reject rule (also guards against decompression bombs).
- `aircraft_image_max_per_aircraft` — max number of screenshots per aircraft (default `12`).
  These three image keys follow the `LOG_LEVEL` precedent: wired into `Setting::defaults()` + the admin settings page but **intentionally not** the setup wizard (post-install fine-tuning, see Aircraft Screenshot Gallery below).

### Application Logging (Activity Log)

An audit trail of who did what, built on `spatie/laravel-activitylog` (stored in the `activity_log` table). Written synchronously - no queue worker required.

- **Automatic model logging** — core domain models (`Flight`, `Aircraft`, `Airline`, `InviteCode`, `User`) use the `App\Models\Concerns\LogsModelActivity` trait, which wraps Spatie's `LogsActivity` with a shared `LogOptions` (`logFillable()->logOnlyDirty()->logExcept(['password','remember_token'])->dontSubmitEmptyLogs()`). These entries are recorded at the `debug` level. `Setting` is deliberately excluded — its string primary key is incompatible with the `activity_log.subject_id` bigint column, and settings changes are covered by an explicit action log instead.
- **Explicit action logging** — meaningful user actions call the `activity()` helper directly, tagging a level in properties: login/logout (`info`) and failed login (`warning`) via `App\Listeners\AuthEventSubscriber` (registered in `EventServiceProvider::$subscribe`); PIREP filed/accepted/rejected in `FlightController` + `Api\V1\FlightAPIController` (`info`); invite redeemed in `PortalController::redeem()` (`info`); settings updated in `Admin\SettingsController::update()` (`info`).
- **Level gate** — every activity carries a `level` (default `debug`). An `Activity::saving` hook in `AppServiceProvider::boot()` copies it onto the dedicated `level` column and drops the record when its weight is below the `LOG_LEVEL` threshold (`App\Support\ActivityLevel`: `debug=10, info=20, warning=30`). This single gate governs both automatic and explicit logs.
- **Viewer** — Super-Admins browse the log at `/admin/activity` (`Admin\ActivityLogController` + `resources/views/admin/activity.blade.php`), with a level filter and pagination; linked from the admin sidebar.
- **Subject labels** — the viewer renders each subject via `App\Support\ActivityLabel::for()` (e.g. an aircraft shows `D-EXAM (FVA)`, registration + operating airline ICAO callsign) instead of `Aircraft #1`, falling back to `ClassName #id` for unknown types. The controller eager-loads the aircraft's airline through the polymorphic subject (`morphWith`) to avoid an N+1.
- **Retention** — the log lives in the DB, so OS `logrotate` does not apply. Pruning is handled by Spatie's `activitylog:clean` command, scheduled daily in `App\Console\Kernel::schedule()`. The window is `ACTIVITYLOG_RETENTION_DAYS` (`.env`, default `180`), read by `config/activitylog.php`. This requires the Laravel scheduler to be running (see Environment → Scheduler).
- Adding a new setting still requires the three-place wiring described above, **except** `LOG_LEVEL`, which is intentionally omitted from the setup wizard so first-install stays minimal.

### User Onboarding & Invite Code System

New users register with no airline membership. After login or registration, Fortify redirects to `/user/dashboard`; if no active airline is in the session, `DashboardController` redirects to `/portal`. The dashboard route must **not** be inside the `airline` middleware group — the controller handles that redirect itself.

The **Airline Portal** (`/portal`, always accessible from the nav) lets users:
- See their current airline memberships and switch between them
- Redeem a single-use invite code to join an airline
- Found a new airline (if permitted — see `allow_user_airline_creation` above)

**Invite codes** (`invite_codes` table) are generated by airline managers (per-airline `Manager` role) via `/airline/invitecodes`. Format: `{ICAO}-{4 digits}` e.g. `DLH-4918`. The manager selects the role the invitee will receive on join. Codes are single-use: `used_by` and `used_at` are set on redemption.

The `RequiresActiveAirline` middleware (`airline` alias in `Kernel.php`) guards all routes that depend on an active airline session and redirects to `/portal` if none is set. Apply it to any new airline-dependent routes.

### User Account Settings

User-facing account settings live at `/settings/*` (`App\Http\Controllers\SettingsController`, views in `resources/views/settings/`) — do not confuse this with `Admin\SettingsController`, which edits instance-wide settings. `routes/web.php` imports the admin one aliased as `AdminSettingsController` to avoid the name clash.

The page mirrors the admin dashboard layout (page header + `row g-4` with a `settings._sidebar` nav card and a `col-lg-9` main area). Sections, keyed by the sidebar's `$active` argument:
- **Profile** (`settings.profile`) — name/email form posting to Fortify's existing `user-profile-information.update` route. Since `User implements MustVerifyEmail`, changing the email re-triggers verification. Errors read from the `updateProfileInformation` bag.
- **Security** (`settings.security`) — password form posting to Fortify's `user-password.update` route. Errors read from the `updatePassword` bag. Both Fortify features are enabled in `config/fortify.php`.
- **Notifications** (`settings.notifications`) — toggles the `users.email_notifications` column via `SettingsController::updateNotifications()`. The form uses a hidden `0` + checkbox `1` so unchecking submits a value.
- **Danger zone** (`settings.danger`) — placeholder only; the "Delete account" button is disabled and no route/logic exists yet.

The `email_notifications` boolean column (`$fillable` + `'boolean'` cast on `User`) is read in each PIREP notification's `via()`: `database` is always on, `mail` is appended only when `$notifiable->email_notifications ?? true`.

### Active Airline Session Pattern

The currently selected airline is stored in the PHP session as `activeairline` (a full `Airline` model). Controllers retrieve it via `session()->get('activeairline')`. The `FlightController::addFlight()` method intentionally reloads this from the DB on each PIREP submission to avoid stale state.

Users can belong to multiple airlines; switching is handled by `AirlineMembershipController::changeActiveAirline()`.

### Flight Status IDs

Hardcoded integer FK in the `flights.status_id` column (references `flight_statuses` table):
- `1` — Pending (newly filed)
- `2` — Accepted
- `3` — Rejected

Filtering for "accepted" flights always uses `status_id = 2`.

### Aircraft Lifecycle

Aircraft have a three-state lifecycle stored in the `aircraft.status` string column (constants on `App\Models\Aircraft`):
- `active` (`STATUS_ACTIVE`) — in service, the only state that can be assigned to flights.
- `inactive` (`STATUS_INACTIVE`) — temporarily grounded; reversible via the edit form's status toggle.
- `retired` (`STATUS_RETIRED`) — **permanent, irreversible** soft-delete. Cannot fly, cannot be reactivated or edited. The row is never hard-deleted so past flights (which FK `flights.aircraft_id`) keep their history intact.

### Aircraft Screenshot Gallery (community uploads + manager moderation)

Aircraft carry a gallery of flight-simulator screenshots (`aircraft_images` table, `App\Models\AircraftImage`). Any airline member may contribute a shot; **pilot uploads land in a pending queue and stay hidden until a Manager approves them** (Manager uploads are auto-approved). Exactly one approved image is flagged `is_primary` (the shot that best shows the livery).

### Location Continuity (opt-in realism mode)

Per-airline boolean `airlines.location_continuity` (default off), toggled by per-airline Managers at `/airline/settings` (`AirlineController::settings()`/`updateSettings()`, view `manager/settings.blade.php`). `updateSettings()` puts the reloaded airline back into the session so the flag takes effect immediately. When enabled for an airline:

- **Filing restriction** — a PIREP's `departure_icao` must equal the aircraft's `current_loc` (checked case-insensitively in `FlightController::addFlight()` and `Api\V1\FlightAPIController::store()`; both fetch the Aircraft model where they previously only counted).
- **Movement on filing** — after `Flight::create()`, the aircraft's `current_loc` is set to the (uppercased) arrival ICAO. The plane moves immediately, so pilots can chain legs before review.
- **Revert on rejection** — rejecting a *pending* PIREP moves the aircraft back to the flight's departure, but only if `current_loc` still equals that flight's arrival (i.e. no later flight has moved it on). Accepting a PIREP changes nothing (the plane already moved at filing).
- **Escape hatch** — managers can still relocate an aircraft manually via the fleet edit form (e.g. to fix a typo'd arrival or ferry a stranded airframe).

Airlines that have not opted in keep fully manual `current_loc` management — flights never move their aircraft. The flag is exposed as `locationContinuity` in `AirlineResource`, and the PIREP web form (`flights/add.blade.php`) auto-fills + locks the departure field from the selected aircraft's `data-location` when the mode is on.

### PIREP Review Requirement (per-airline)

Per-airline boolean `airlines.require_pirep_review` (default on at founding/setup), toggled on the same `/airline/settings` page as location continuity. When **on**, filed PIREPs stay pending (`status_id = 1`) and the `FlightFiled` event notifies reviewers. When **off**, both filing paths set `status_id = 2` (accepted) immediately after create and skip the `FlightFiled` event entirely — no reviewer notifications, nothing in the review queue. Location continuity is unaffected either way (the aircraft moves at filing time).

Two safeguards: `AirlineController::updateSettings()` refuses to switch review **off while flights are still pending** (they would be stranded in an unreachable queue — accept/reject them first), and the "Review flights" entry in the navbar's Management dropdown is hidden when the active airline has review disabled (the session airline is refreshed on settings save, so this applies immediately).

### PIREP Workflow (Event-Driven)

When a PIREP is filed (both web and API paths) and the airline requires PIREP review (see above), `event(new FlightFiled($flight))` is dispatched. The `FlightFiledNotification` listener (`app/Listeners/FlightFiledNotification.php`) resolves the airline's reviewers (per-airline `Dispatcher`/`Manager`, excluding the filing pilot) and hands them to Laravel's notification system via `Notification::send($reviewers, new PirepFiled($flight))`.

### Notifications (Multi-Channel)

Notifications use Laravel's native notification system so delivery channels are pluggable without touching the trigger/listener logic. A notification class (e.g. `app/Notifications/PirepFiled.php`) declares its channels in `via()` — `database` is always included, `mail` is appended only when the recipient's `email_notifications` preference is on (see User Account Settings) — and renders each with a matching method (`toArray()`, `toMail()`).

- **`database`** is Laravel's built-in channel. It persists to the standard `notifications` table (uuid PK, polymorphic `notifiable`, JSON `data`, `read_at`) via `Illuminate\Notifications\DatabaseNotification`. A notification opts in by returning `'database'` from `via()` and implementing `toArray($notifiable): array` (`title`, `message`, optional `url`) — the payload is stored in `data`. The bell/list UI reads unread rows through the `Notifiable` trait's `unreadNotifications` relation; `User::countNewNotifications()` wraps `unreadNotifications()->count()`. Dismissing a notification calls `$notification->markAsRead()`.
- **`mail`** is Laravel's built-in channel; `toMail()` returns a `MailMessage`.
- Notification classes implement `ShouldQueue`, and use the `App\Notifications\Concerns\QueuesMailChannel` trait. The trait's `viaConnections()` pins the `database` channel to the `sync` connection (so the in-app/bell notification is written immediately, with no worker needed) while the `mail` channel falls through to the default queue connection (`QUEUE_CONNECTION`, `database` by default). A slow or failing SMTP server therefore never blocks or fails the triggering PIREP request — the email is delivered by the background worker. Any new PIREP notification should `use QueuesMailChannel` for the same guarantee.

**To add a channel** (e.g. webhook): add a channel class + `Notification::extend('webhook', ...)`, add `'webhook'` to `via()`, and add a `toWebhook()` renderer. No event/listener changes. `via()` receives `$notifiable`, so per-user channel preferences live there — the `mail` channel is already gated on the recipient's `email_notifications` preference.

Accept/reject actions notify the pilot the same way — `Notification::send($flight->pilot, new PirepAccepted($flight))` (and `PirepRejected`) in `FlightController` / `FlightAPIController`.

### API Resources

All API responses go through Laravel API Resources at `app/Http/Resources/V1/`. Controllers return `FlightResource`, `FlightCollection`, etc. — never raw model data.

### API Documentation (Scribe)

The `/api/v1` REST API is documented with [Scribe](https://scribe.knuckles.wtf) (`knuckleswtf/scribe`, a dev dependency). Docs are generated as **static HTML** into `public/docs/` and served **directly by the web server** (nginx in prod) as static files at **`/docs`** — there is no PHP `/docs` route and no `scribe` named route. An OpenAPI spec and Postman collection are emitted alongside under `public/docs/`. An "API Reference" link is in the footer of both the `app` and `landing` layouts (`url('/docs')`).

- **Config** — `config/scribe.php`: `type => 'static'` (output `public/docs`), `routes.match.prefixes => ['api/*']` (only API routes are documented), bearer-token auth (`auth.default => true`, so every endpoint requires a Sanctum token except those marked `@unauthenticated` — currently only `GET /api/v1/info`). The live `ResponseCalls` strategy is removed so generation is deterministic and never touches the DB. The whole config is guarded by `if (! class_exists(AuthIn::class)) return [];` — Scribe is dev-only and absent from the `--no-dev` production runtime image, so this stops `config:cache` (run by `php artisan optimize` on every container start) from fataling on the missing `Knuckles\Scribe\*` classes.
- **Doc content lives in source** — example responses, request-body fields, groups and auth flags are Scribe docblock annotations (`@group`, `@authenticated`/`@unauthenticated`, `@urlParam`, `@queryParam`, `@bodyParam`, `@response`) on the `app/Http/Controllers/Api/V1/*` controllers. `@bodyParam` values mirror the matching `Store*Request` rules. Keep them in sync when the API changes. The `GET /api/v1/user` endpoint was extracted from an inline route closure into `UserController` so it could be annotated.
- **Generation** — `php artisan scribe:generate` writes static HTML to `public/docs/`; the output plus intermediates (`.scribe/`, `storage/app/scribe/`) are **gitignored**, so it must be (re)generated wherever the app is deployed or `/docs` 404s.
  - **Production** — baked into the image at build time: `Docker/Dockerfile.prod` runs `scribe:generate` in a dedicated `docs` stage (which installs the dev deps that carry Scribe) and `COPY --from=docs`es `public/docs` into the Scribe-free runtime image. nginx serves it statically (`index index.php index.html` in `Docker/nginx.conf` resolves `/docs` → `public/docs/index.html`). No deploy step needed.
  - **Dev** — the one-shot `docs` service in `Docker/docker-compose.yml` runs `scribe:generate` into the mounted repo before `app` serves; `php artisan serve` serves the static files, and `routes/web.php` has a `Route::redirect('/docs', '/docs/index.html')` so `/docs` resolves (the built-in dev server won't auto-serve a directory index). Regenerate manually with `docker exec yaams-dev-app php artisan scribe:generate` after editing annotations.

### Prometheus Metrics

Instance monitoring endpoint at `/metrics` (`spatie/laravel-prometheus`), returning domain gauges in Prometheus text format with the `yaams_` namespace prefix:

- **Scrape-time evaluation** — every gauge is a closure in `app/Providers/PrometheusServiceProvider.php`, executed fresh on each scrape as a cheap aggregate DB query. There is no cross-request metric storage (`config/prometheus.php` keeps `'cache' => null`), so no APCu/Redis is needed. HTTP request counters/histograms are intentionally out of scope — they would require shared FPM storage and are better harvested at the reverse-proxy layer.
- **Gauges** — users, airlines, flights by review status (`status` label mapped from the hardcoded flight status IDs), aircraft by lifecycle status, unused invite codes, failed jobs, plus the package's `QueueSizeCollector` (queue depth via `Queue::size()`).
- **Auth** — `App\Http\Middleware\MetricsAuth` (wired via the `middleware` key in `config/prometheus.php`) requires `Authorization: Bearer $METRICS_TOKEN`; when `METRICS_TOKEN` is unset, the endpoint always returns 403. The token is env-level ops config (`services.metrics.token` in `config/services.php`), deliberately **not** a `Setting` row — it belongs to the scraper's infrastructure, so it gets no setup-wizard/admin-settings wiring.
- **Adding a gauge** — chain `Prometheus::addGauge('Label')->name('snake_name')->helpText(...)->value(fn () => ...)` in `PrometheusServiceProvider::register()`; labeled gauges return `[[value, [labelValue]], ...]` from the closure. Keep every closure a single cheap aggregate query.
- Scribe API docs are unaffected (Scribe only matches `api/*` routes).

### Key Model Relationships

- `Aircraft::used_by` — FK to `airlines.id` (not `airline_id`)
- `Aircraft::current_loc` — FK to `airports.id` (ICAO code)
- `Flight` appends computed attributes: `full_flight_number`, `flight_duration`, `flight_duration_minutes`, `raw_distance` (Haversine in nautical miles)
- `Aircraft` appends aggregate attributes computed from accepted flights: `total_flights_count`, `total_flights_hours`, `total_distance_flown`
- `User::logged_hours()` and `logged_flights()` accept an `Airline` argument and only count `status_id = 2` flights

### Test Accounts (seeded)

All passwords: `start`
- `homer@test.com` — Pilot
- `test@test.com` — Manager
- `admin@test.com` — Super-Admin

API tokens are printed during `php artisan migrate --seed`.

## Environment

Key `.env` values beyond standard Laravel:
- `FLIGHT_PAGE_LIMIT` — number of flights per page in the pilot flight list (default: 10)
- `QUEUE_CONNECTION` — background job backend (default: `database`; set to `redis` to use Redis). Notification email is dispatched on this queue, so a worker must be running in every environment where mail should actually be sent — see below.
- `ACTIVITYLOG_RETENTION_DAYS` — how long activity-log rows are kept before `activitylog:clean` prunes them (default: `180`). Requires the scheduler to be running — see below.
- `METRICS_TOKEN` — bearer token required to scrape the Prometheus `/metrics` endpoint (see Prometheus Metrics). Unset (default) disables the endpoint.

### Queue / Background Jobs

Outgoing notification email is queued (see the Notifications section) so a slow/failing SMTP server can never block PIREP filing/review. This requires a worker to drain the queue:

- **Dev:** the `worker` service in `Docker/docker-compose.yml` runs `queue:work --tries=3` automatically (mail lands in smtp4dev at http://localhost:8081) — no manual step. Without a running worker, in-app notifications still appear instantly but no email is sent. To drive the queue by hand instead, run `docker exec yaams-dev-app php artisan queue:work`.
- **Production:** run `php artisan queue:work --tries=3` under a process supervisor so it restarts on exit, e.g. a Supervisor program:
  ```ini
  [program:yaams-worker]
  command=php /app/artisan queue:work --tries=3 --sleep=3
  autostart=true
  autorestart=true
  numprocs=1
  ```
  Run `php artisan queue:restart` on each deploy so workers pick up new code. Failed sends are recorded in the `failed_jobs` table; inspect with `php artisan queue:failed` and requeue with `php artisan queue:retry`.
- The `database` driver uses the `jobs`/`job_batches` tables created by migration (`failed_jobs` already existed). Switching to Redis is a pure `QUEUE_CONNECTION` change — no code change, since only the in-app channel is pinned to `sync`.

### Scheduler (Cron)

`App\Console\Kernel::schedule()` registers `activitylog:clean` to run daily (prunes the `activity_log` table per `ACTIVITYLOG_RETENTION_DAYS`). Laravel's scheduler is **not** the queue worker — it is a separate process, and mail delivery (queue worker) is independent of scheduled pruning (scheduler).

- **Dev:** the `scheduler` service in `Docker/docker-compose.yml` runs `php artisan schedule:work` automatically — no cron to set up. `schedule:work` is a foreground process that internally fires `schedule:run` every minute.
- **Production:** either run `schedule:work` under a process supervisor (mirror of the `yaams-worker` Supervisor program above), or add a single system cron entry:
  ```cron
  * * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1
  ```
  Without one of these, the activity log is never pruned and grows unbounded (everything else keeps working). A full production deploy runs both the queue worker and the scheduler (plus `php artisan queue:restart` on deploy).

Docker setup lives in `Docker/` with `Dockerfile` and `docker-compose.yml`. A Nix flake (`flake.nix`) is also provided for NixOS environments.

`docker compose up -d` (from `Docker/`) brings up the whole dev stack: `db` (auto-creates the `yaams` schema, with a healthcheck), a one-shot `migrate` service that applies migrations before anything long-running starts, then `app` (`php artisan serve --host=0.0.0.0`, port 8000), `worker`, `scheduler`, `phpmyadmin`, and `smtp4dev`. The `app`/`worker`/`scheduler`/`migrate` services build from `Docker/Dockerfile`, mount the repo at `/app`, and rely on `vendor/` being installed (run `composer install` first on a fresh clone). First-time data still needs a manual seed: `docker exec yaams-dev-app php artisan migrate --seed`.

