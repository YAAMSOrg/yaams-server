# YAAMS - Yet Another Airline Management System

An decentralised open-source virtual airline management platform. Track PIREPs, manage fleets, and build thriving virtual airline communities - all in one place.

![video demo](https://github.com/YAAMSOrg/yaams-server/blob/main/Docs/res/showcase.gif)

## Features

* PIREP filing, review, and acceptance workflow
* Fleet management with airframe tracking and metrics
* Pilot dashboard with verified flight hours and statistics
* Multi-airline support with easy switching
* In-app and email notification system
* Activity log / audit trail with a configurable verbosity level
* REST API (v1) for custom ACARS clients and integrations

## Technical Foundation

* Framework: Laravel 12.x
* Frontend: Bootstrap 5.3.3 / Blade / Vanilla CSS
* Authentication: Laravel Sanctum & Fortify
* Permissions: Spatie Laravel Permission
* Environment: Docker-ready and NixOS support

## Development Setup

### Containerized Environment (Docker/Podman)

1. Create the shared network:
   ```bash
   docker network create yaams
   ```
2. Install PHP dependencies and prepare the environment file (the compose
   services mount the repo and expect `vendor/` to exist):
   ```bash
   cp .env.example .env
   docker run --rm -v $(pwd):/app -w /app composer:latest install
   ```
3. Bring up the whole stack:
   ```bash
   cd Docker
   docker compose up -d
   ```
   This starts everything and applies migrations automatically:
   * `db` - MariaDB (creates the `yaams` schema, with a healthcheck)
   * `migrate` - one-shot, runs `php artisan migrate` once the DB is healthy
   * `app` - `php artisan serve --host=0.0.0.0` on http://localhost:8000
   * `worker` - `queue:work`, so notification emails are delivered (they are
     queued, so a slow/failing SMTP can't block PIREP filing)
   * `scheduler` - `schedule:work`, prunes the activity log daily
   * `phpmyadmin` - http://localhost:8080
   * `smtp4dev` - catches outgoing mail at http://localhost:8081
4. Generate the app key and seed the test data (one-time):
   ```bash
   docker exec yaams-dev-app php artisan key:generate
   docker exec yaams-dev-app php artisan migrate --seed
   ```
   The seeder prints the Sanctum API tokens for the test users to stdout.

### Native Setup
1. Ensure PHP 8.2+, Composer, and a MariaDB/MySQL database are installed.
2. Clone the repository and install dependencies:
   ```bash
   composer install
   php artisan migrate --seed
   ```

## Default Test Accounts
The database seeder creates three tiers of users for testing (Password for all: `start`):
* Pilot: `homer@test.com`
* Manager: `test@test.com`
* Super-Admin: `admin@test.com`

---

Note: This project was originally launched as a traditional development effort without the use of Artificial Intelligence. However, as the scope expanded, many of the current advanced features and architectural refinements were implemented with significant AI assistance to ensure modern standards and rapid delivery.
