# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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

## Architecture

### Authentication & Authorization

Two auth systems coexist:
- **Fortify** — web session login/register at `app/Actions/Fortify/`
- **Sanctum** — stateless API token auth; `auth:sanctum` middleware on all `/api/v1/` routes

Authorization uses **Spatie Laravel Permission** with three roles:
- `Pilot` — no special permissions (default for new users)
- `Manager` — `add aircraft`, `edit aircraft`, `review flight`
- `Super-Admin` — bypasses all permission checks via `Gate::before` in `AuthServiceProvider`

The `AircraftPolicy` (`app/Policies/AircraftPolicy.php`) handles model-level aircraft authorization.

### Active Airline Session Pattern

The currently selected airline is stored in the PHP session as `activeairline` (a full `Airline` model). Controllers retrieve it via `session()->get('activeairline')`. The `FlightController::addFlight()` method intentionally reloads this from the DB on each PIREP submission to avoid stale state.

Users can belong to multiple airlines; switching is handled by `AirlineMembershipController::changeActiveAirline()`.

### Flight Status IDs

Hardcoded integer FK in the `flights.status_id` column (references `flight_statuses` table):
- `1` — Pending (newly filed)
- `2` — Accepted
- `3` — Rejected

Filtering for "accepted" flights always uses `status_id = 2`.

### PIREP Workflow (Event-Driven)

When a PIREP is filed (both web and API paths), `event(new FlightFiled($flight))` is dispatched. The `FlightFiledNotification` listener (`app/Listeners/FlightFiledNotification.php`) finds all airline members with `review flight` permission and creates in-app `Notification` records for them. Accept/reject actions create notifications for the pilot.

### API Resources

All API responses go through Laravel API Resources at `app/Http/Resources/V1/`. Controllers return `FlightResource`, `FlightCollection`, etc. — never raw model data.

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

Docker setup lives in `Docker/` with `Dockerfile` and `docker-compose.yml`. A Nix flake (`flake.nix`) is also provided for NixOS environments.
