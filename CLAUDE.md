# AGENTS.md
## Project Overview

YAAMS (Yet Another Airline Management System) is a Laravel 13.x application providing a virtual airline management system with two parallel interfaces:
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
PHPUnit suite under `tests/` (Unit + Feature, incl. `tests/Feature/Api`). Tests run against SQLite `:memory:` (configured in `phpunit.xml`), so no database service is needed. 

## Architecture
### Error Pages
- Branded HTTP error pages live in `resources/views/errors/` and are picked up by Laravel automatically (no handler changes). JSON/API requests still get JSON errors; with `APP_DEBUG=true` a 500 still shows the debug page.
-  Never depend on session, auth, CSRF

### Authentication & Authorization

Two auth systems coexist:
- **Fortify** — web session login/register at `app/Actions/Fortify/`; `CreateNewUser` implements the `CreatesNewUsers` contract and is bound in `AppServiceProvider::register()`
- **Sanctum** — stateless API token auth; `auth:sanctum` middleware on all `/api/v1/` routes

Two role systems coexist — do not confuse them:
- **Spatie roles** (global): only `Super-Admin` matters - it bypasses all checks via `Gate::before` in `AuthServiceProvider` (and is the `administerInstance` gate). The global `Pilot` / `Manager` roles and their `add aircraft` / `edit aircraft` / `review flight` permissions are legacy setup data and grant nothing.
- **Per-airline role** in `airline_memberships.role` (enum: `Pilot`, `Dispatcher`, `Manager`; the owner always has Manager rights): the low-level helpers are `User::airlineRole()`, `isMemberOf()`, `hasAirlineRole()`, `isManagerOf()`, `canReviewFlightsFor()` (they reuse a loaded `airlines` relation instead of querying).

**`App\Policies\AirlinePolicy` is the single source of per-airline permissions** - web controllers, views, API requests and the API `abilities` all go through it (`$user->can('manageMembers', $airline)`). It is session-free (works under Sanctum) and does not check suspension (the `airline.writable` middleware does). Abilities: `create` (found an airline: Super-Admin or `allow_user_airline_creation`), `fileFlights` (member), `reviewFlights` (Dispatcher/Manager), `addAircraft`, `editAircraft`, `manageMembers`, `manageInviteCodes`, `manageNotams`, `editSettings` (Manager). Record-level rules stay in `AircraftPolicy` (owned + not retired) and `FlightPolicy::review()` (only Managers review their own PIREP). **A new per-airline permission is a new `AirlinePolicy` method plus an entry in `App\Support\Abilities::AIRLINE`** - never an inline role check.

### Admin Airline Management

Super-Admins manage every airline at `/admin/airlines` (`Admin\AirlineController`, views in `resources/views/admin/airlines/`, routes in the `admin.` group, aliased `AdminAirlineController` in `routes/web.php`):

- **List** - search (name / ICAO / prefix / ATC callsign), filters (status `active|suspended|orphaned`, PIREP review on/off), sort, member/fleet/flight/pending counts
- **Create** - founds an airline on behalf of an existing user, who becomes owner + Manager
- **User picker** - users are chosen with `<x-admin.user-picker>` (`resources/views/components/admin/user-picker.blade.php`, search-as-you-type, submits the user id), backed by the Super-Admin-only JSON endpoint `GET /admin/users/lookup?q=&airline=` (`Admin\UserController::lookup()`, name/e-mail match, min. 2 chars, max. 8 results, `airline` leaves out existing members)
- **Membership notifications** - `App\Notifications\AddedToAirline` (admin adds a member or founds an airline for a user) and `RemovedFromAirline` (admin or manager removes a member, or the airline is deleted) go to the affected user (in-app + mail per `email_notifications`, `QueuesMailChannel`), never to an admin changing their own membership. Both carry the airline name/ICAO as plain strings, not the model, so the queued mail still renders after the airline is deleted. Joining via invite code sends nothing to the joiner (they did it themselves)
- **Detail** - profile + settings form (same rules as founding, plus `require_pirep_review`/`location_continuity`; review cannot be switched off while flights are pending), members (change role, remove, add an existing user directly without invite), ownership transfer (also fixes orphaned airlines whose owner was deleted), stats, recent activity (link to `/admin/activity?airline={id}`, which covers the airline and its flights, aircraft and invite codes)
- **Suspension (freeze)** - `airlines.suspended_at` + `suspension_reason` (not fillable, set via `forceFill`; replaced the old `active` flag). A suspended airline stays viewable for its members but is read-only:
  - `EnsureAirlineNotSuspended` (`airline.writable` alias) refuses every non-GET request: applied to the whole web `airline` group (notification acks and the fleet search are exempt via `withoutMiddleware`) and to the API write routes (403). It resolves the airline from the `{airline}` / `{flight}` route parameter or the session
  - invite codes of a suspended airline cannot be redeemed (`PortalController::redeem()`), it is hidden from the landing page (`Airline::scopeActive()`), and `layouts/_airline_suspended` shows a banner; `AirlineResource` exposes `isSuspended`
- **Deletion** - shared `App\Actions\Airlines\DeleteAirline` (type the ICAO to confirm): deletes flights, aircraft, screenshots (rows + files), memberships and the airline (invite codes, NOTAMs cascade)
- **Safeguards** - the owner can be neither demoted nor removed (transfer first), and the last Manager can be neither demoted nor removed
- **Session freshness** - `RequiresActiveAirline` reloads the session airline on every request, so admin edits apply immediately and members of a deleted airline (or removed members) are sent back to `/portal`
- **Audit** - causer = admin, subject = airline: `airline_suspended`, `airline_reactivated`, `airline_ownership_transferred_by_admin`, `airline_deleted_by_admin` (`warning`); `airline_created_by_admin`, `airline_updated_by_admin`, `airline_member_added_by_admin`, `airline_member_role_changed_by_admin`, `airline_member_removed_by_admin` (`info`)

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
- `imprint_enabled` / `imprint_content`, `privacy_content`, `terms_enabled` / `terms_content`, `legal_updated_at` — legal pages (see Legal Pages & GDPR below). Edited on their own admin page `/admin/legal`, not the instance settings page, and **intentionally not** in the setup wizard.

### Legal Pages & GDPR

Instances may run in the EU, so operators get Imprint / Privacy Policy / Terms of Use pages plus GDPR self-service. Everything goes through `App\Support\LegalPage`:

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

**Invite codes** (`invite_codes` table) are generated by airline managers (per-airline `Manager` role) via `/airline/invitecodes`. Format: `{ICAO}-{4 digits}` e.g. `DLH-4918`. The manager selects the role the invitee will receive on join. Codes are single-use: `used_by` and `used_at` are set on redemption. On redemption, `PortalController::redeem()` notifies the code's creator via `App\Notifications\InviteCodeRedeemed` (in-app + mail per `email_notifications`, `QueuesMailChannel`), unless the creator is the redeeming user.

The `RequiresActiveAirline` middleware (`airline` alias in `Kernel.php`) guards all routes that depend on an active airline session and redirects to `/portal` if none is set. Apply it to any new airline-dependent routes.

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

### PIREP Review Decisions

Reviewers decide pending PIREPs from the review panel on the PIREP detail page (`flights/detail.blade.php`, shown only when `can('review', $flight)` and the flight is pending; pilots see an "Awaiting review" note instead):

- **Who and when** - `flights.reviewed_by` (FK users, null on delete) + `reviewed_at`, written only through `Flight::recordReview($reviewer, $statusId)` (not fillable) by both web and API accept/reject. Shown as "Accepted/Rejected by X" on the detail page, exposed as `reviewedBy` / `reviewedAt` in `FlightResource`, included in the GDPR export (`pirep_reviews`)
- **Reason required** - rejecting needs `rejection_remarks` (max. 1000) on the web and in the API (422 otherwise), so the pilot always learns why
- **Next PIREP** - after a web decision the reviewer lands on `Flight::nextPendingFor($reviewer)` (oldest pending PIREP of the airline they may review), or the review queue when none are left
- **Plausibility facts** - the panel shows block time, groundspeed (distance / block time) and fuel per block hour; facts only, no thresholds

### Notifications (Multi-Channel)

Notifications use Laravel's native notification system so delivery channels are pluggable without touching the trigger/listener logic. A notification class (e.g. `app/Notifications/PirepFiled.php`) declares its channels in `via()` — `database` is always included, `mail` is appended only when the recipient's `email_notifications` preference is on (see User Account Settings) — and renders each with a matching method (`toArray()`, `toMail()`).

- **`database`** is Laravel's built-in channel. It persists to the standard `notifications` table (uuid PK, polymorphic `notifiable`, JSON `data`, `read_at`) via `Illuminate\Notifications\DatabaseNotification`. A notification opts in by returning `'database'` from `via()` and implementing `toArray($notifiable): array` (`title`, `message`, optional `url`) — the payload is stored in `data`. The bell/list UI reads unread rows through the `Notifiable` trait's `unreadNotifications` relation; `User::countNewNotifications()` wraps `unreadNotifications()->count()`. Dismissing a notification calls `$notification->markAsRead()`.
- **`mail`** is Laravel's built-in channel; `toMail()` returns a `MailMessage`.
- Notification classes implement `ShouldQueue`, and use the `App\Notifications\Concerns\QueuesMailChannel` trait. The trait's `viaConnections()` pins the `database` channel to the `sync` connection (so the in-app/bell notification is written immediately, with no worker needed) while the `mail` channel falls through to the default queue connection (`QUEUE_CONNECTION`, `database` by default). A slow or failing SMTP server therefore never blocks or fails the triggering PIREP request — the email is delivered by the background worker. Any new PIREP notification should `use QueuesMailChannel` for the same guarantee.
- Fortify's auth mails are queued too: `User::sendEmailVerificationNotification()` / `sendPasswordResetNotification()` send `App\Notifications\VerifyEmail` / `ResetPassword`, `ShouldQueue` subclasses of Laravel's built-ins, so a broken SMTP server never fails registration, an email change or a reset request.

**To add a channel** (e.g. webhook): add a channel class + `Notification::extend('webhook', ...)`, add `'webhook'` to `via()`, and add a `toWebhook()` renderer. No event/listener changes. `via()` receives `$notifiable`, so per-user channel preferences live there — the `mail` channel is already gated on the recipient's `email_notifications` preference.

Accept/reject actions notify the pilot the same way — `Notification::send($flight->pilot, new PirepAccepted($flight))` (and `PirepRejected`) in `FlightController` / `FlightAPIController`.

### API Resources

All API responses go through Laravel API Resources at `app/Http/Resources/V1/`. Controllers return `FlightResource`, `FlightCollection`, etc. — never raw model data.

### API Reference Data

`GET /api/v1/online-networks` (`OnlineNetworkAPIController`, `OnlineNetworkResource`: `id`, `name` from `networkname`) lists the valid `online_network_id` values for filing a PIREP. Unpaginated on purpose (small, stable list), so it is not on the `ListRequest` contract. IDs differ between installs (the setup wizard and the dev seeder insert in different orders), so clients must never hardcode them.

### API Notifications

`Api\V1\NotificationAPIController` exposes the token owner's in-app (`database` channel) notifications, the API counterpart of the web `NotificationsController`:

- `GET /api/v1/notifications` - list on the `ListRequest` contract (`ListNotificationsRequest`: sorts `created_at` / `read_at`, default `-created_at`, `unread` boolean filter). Items via `NotificationResource`: `id` (uuid), `type`, `title`, `message`, `url` (nullable), `readAt`, `createdAt`
- `GET /api/v1/notifications/unread-count` - `{ "count": n }`, a single `count()` query for polling the bell
- `POST /api/v1/notifications/{id}/read` (returns the item, idempotent) and `POST /api/v1/notifications/read-all` (`204`, one bulk `update`)
- **Scoping** - ids are resolved through `$request->user()->notifications()->findOrFail()`, never route-model binding, so another user's notification is a plain `404`
- **`type`** is `Str::camel(class_basename(...))` of the notification class (`pirepFiled`, `twoFactorReset`, ...), never the FQCN. A new notification class with the `database` channel is a new `type` - add it to the list in the controller's `@group` docblock
- **Sorting gotcha** - Laravel's `notifications()` relation has a built-in `latest()`, so the controller calls `reorder()` before `ListRequest::paginate()`; otherwise every requested sort is overridden

### API Abilities (role-aware clients)

Clients (e.g. the YAAMS Commander desktop app) build role-aware UIs from flags computed by `App\Support\Abilities`, never from role names:

- **Instance** - `GET /api/v1/user` -> `abilities`: `foundAirline` (`can('create', Airline::class)`), `administerInstance` (Super-Admin)
- **Per airline** - `membership` on each airline of `GET /api/v1/user`, `GET /api/v1/airlines` and `GET /api/v1/airlines/{airline}` (`null` for a non-member): `role` (lowercase `pilot|dispatcher|manager`, display only), `isOwner`, `abilities` (every `Abilities::AIRLINE` key as an explicit boolean). Opt-in via `AirlineResource::withMembership()`, so airlines nested in other payloads stay lean; controllers `loadMissing('airlines')` on the user so it costs no query per airline
- Flags go through the same `Gate`/`AirlinePolicy` checks the endpoints enforce; Super-Admin reports its effective (all true) abilities; every per-airline ability is `false` while the airline is suspended
- They are UI hints only - endpoints keep enforcing and returning 403. `tests/Feature/Api/AbilitiesTest.php` pins every flag to the real allow/deny of its endpoint, so extend it when adding an ability

### API Pagination & Filtering

Every list endpoint (airlines, aircraft, my flights, review queue, notifications) is paginated, sortable and filterable through one contract - keep new list endpoints on it:

- **Request** - a subclass of `App\Http\Requests\Api\V1\ListRequest` (`ListAirlinesRequest`, `ListAircraftRequest`, `ListFlightsRequest`, `ListReviewFlightsRequest`, `ListNotificationsRequest`; the two flight lists share `FlightListRequest`). It declares `sortable()` (public key => column, or a closure `fn (Builder $query) => Expression` for a computed sort - it must qualify its columns), `defaultSort()`, `filterRules()` and `applyFilters()`; the controller calls `$request->paginate($query)` (filters, sort + id tie-breaker, `per_page`, `withQueryString()`)
- **Params** - flat query params: `page`, `per_page` (default 25, max 100), `sort` (`-` prefix = descending), filters. Invalid values are a 422, never ignored. Boolean flags use `in:1,0,true,false` (Laravel's `boolean` rule refuses `true`/`false` from a query string). Flight status is filtered by name (`status=accepted`); `status_id` is a deprecated alias
- **Flight sorts** - `FlightListRequest` (both flight lists): `flight_number` (numeric), `callsign`, `departure`, `arrival`, `block_off`, `block_on`, `duration` (computed via `App\Support\BlockTime::secondsSql()`), `burned_fuel`, `created_at` (default `-created_at`). Only "my flights" adds `status`, sorted by `status_id` (workflow order Pending, Accepted, Rejected); the review queue holds only pending flights
- **Authorization** - membership/reviewer checks live in the request's `authorize()` with `failedAuthorization()` throwing the endpoint's 403 message, so a forbidden caller gets a 403 before any 422
- **Response** - collections extend `App\Http\Resources\V1\ApiCollection`: Laravel's standard `data` / `links` / `meta`, with the redundant `meta.links` pager array removed
- **Errors** - `App\Exceptions\Handler` renders JSON for every `api/*` request (`shouldRenderJsonWhen`), even without `Accept: application/json`, so API clients never get a redirect to `/login`
- **Docs** - document every filter/sort as `@queryParam`, and include `links`/`meta` in the `@response` example; the shared contract is described in `config/scribe.php` `intro_text`

### API Documentation (Scribe)

The `/api/v1` REST API is documented with [Scribe](https://scribe.knuckles.wtf) (`knuckleswtf/scribe`, a **runtime** dependency in `require`). Docs are generated as a **`laravel`-type Blade view** (`resources/views/scribe/index.blade.php` + assets under `public/vendor/scribe/`) and served by a Laravel route at **`/docs`** — `Route::view('/docs', 'scribe.index')->name('scribe')` in `routes/web.php`. Because the page is rendered at request time, its **base URL resolves to the live `APP_URL`** on each request (fixing the old static-HTML bug where the build-time `localhost` was baked in). An "API Reference" link is in the footer of both the `app` and `landing` layouts (`url('/docs')`).

  - **Production** — baked into the image at build time: `Docker/Dockerfile.prod` runs `scribe:generate` in the runtime stage (Scribe is in the runtime `vendor`, no separate docs/dev-vendor stage). To make the copy-paste example snippets and the "Try It Out" host dynamic too (Scribe pre-renders `base_url` into those, which would otherwise freeze to the build-time URL), it generates against a sentinel base URL (`APP_URL=https://scribe-base-url.invalid`) and then `sed`-replaces that sentinel with `{{ config('app.url') }}` directly in the generated view — the request-time Blade render then resolves every occurrence to the real `APP_URL`. An arbitrary runtime UID cannot write `public/`/`resources/`, which is why generation happens at build (as root), not at container start.
  - **Dev** — the one-shot `docs` service in `Docker/docker-compose.yml` runs `scribe:generate` into the mounted repo before `app` serves; `php artisan serve` then serves `/docs` via the Laravel route. Dev needs no sentinel/`sed` — it generates against the dev `APP_URL` (`http://localhost:8000`), which is exactly where the dev server runs. Regenerate manually with `docker exec yaams-dev-app php artisan scribe:generate` after editing annotations.

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
- `MAIL_SCHEME` / `MAIL_VERIFY_PEER` — SMTP transport options in `config/mail.php` (`MAIL_ENCRYPTION` is not read by Laravel anymore). STARTTLS is used automatically when offered; `MAIL_VERIFY_PEER=false` accepts a relay with a self-signed certificate.
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

Docker setup lives in `Docker/` with `Dockerfile` and `docker-compose.yml`.

