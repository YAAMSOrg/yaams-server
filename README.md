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

## Production Deployment

Production runs from a prebuilt multi-arch (amd64 + arm64) image. The image bundles the app with nginx + PHP-FPM (Alpine-based); you only need Docker and the single `docker-compose.prod.yml` file.

### Deploying on a server
1. Copy `docker-compose.prod.yml` and `.env.production.example` to the server.
2. Create the environment file and generate an app key:
   ```bash
   cp .env.production.example .env
   docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show
   # paste the key into APP_KEY, then set DB_PASSWORD, APP_URL, mail + domain settings
   ```
3. Start the stack (bundled MariaDB, migrations, app, queue worker, scheduler):
   ```bash
   docker compose -f docker-compose.prod.yml up -d
   ```
4. Open the app and complete the `/setup` wizard.

### TLS / reverse proxy
The `app` service serves **plain HTTP on `127.0.0.1:8000`** - terminate TLS with your own reverse proxy on the host. Example Caddy block:
```
your.domain {
    reverse_proxy 127.0.0.1:8000
}
```
Laravel is configured to trust the proxy's forwarded headers, so it generates `https://` URLs and logs real client IPs. The container itself never handles TLS or certificates - that is entirely the reverse proxy's job.

### Upgrading
```bash
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d
```
The one-shot `migrate` service applies new migrations on each `up`; the `db` data and uploaded files persist in named volumes.

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

### Default Test Accounts
The database seeder creates three tiers of users for testing (Password for all: `start`):
* Pilot: `homer@test.com`
* Manager: `test@test.com`
* Super-Admin: `admin@test.com`

---

Note: This project was originally launched as a traditional development effort without the use of Artificial Intelligence. However, as the scope expanded, many of the current advanced features and architectural refinements were implemented with significant AI assistance to ensure modern standards and rapid delivery.
