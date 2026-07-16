<div align="center">

# ✈️ YAAMS

**Yet Another Airline Management System**

A decentralised, open-source virtual airline management platform. Track PIREPs, manage fleets, and build thriving virtual airline communities - all in one place.

[![Tests](https://github.com/YAAMSOrg/yaams-server/actions/workflows/tests.yml/badge.svg)](https://github.com/YAAMSOrg/yaams-server/actions/workflows/tests.yml)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](LICENSE)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)

![screenshot](https://github.com/user-attachments/assets/69a64cc3-d340-4f18-99ea-5ebb04804eef)

</div>

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start-development)
- [Running the Tests](#running-the-tests)
- [API](#api)
- [Monitoring](#monitoring)
- [Production Deployment](#production-deployment)
- [Contributing](#contributing)
- [License](#license)

## Features

- **PIREP workflow** - filing, review, and acceptance of pilot reports
- **Fleet management** - airframe tracking, lifecycle states, and per-aircraft metrics
- **Pilot dashboard** - verified flight hours and statistics
- **Multi-airline** - belong to several airlines and switch between them
- **Notifications** - in-app and email delivery
- **Activity log** - audit trail with a configurable verbosity level
- **Prometheus metrics** - live domain gauges at `/metrics` for monitoring
- **REST API (v1)** - for custom ACARS clients and integrations

To see more screenshots of YAAMS in action, visit our [wiki](https://github.com/YAAMSOrg/yaams-server/wiki).

## Tech Stack

| Layer | Technology |
| --- | --- |
| Framework | Laravel 12.x (PHP 8.3) |
| Frontend | Bootstrap 5.3.3 / Blade / Vanilla CSS |
| Auth | Laravel Sanctum (API) & Fortify (web) |
| Permissions | Spatie Laravel Permission |
| Environment | Docker-ready, with NixOS support |

## Quick Start (Development)

The dev stack runs entirely in containers (Docker or Podman).

1. **Create the shared network:**
   ```bash
   docker network create yaams
   ```
2. **Install PHP dependencies and prepare the environment file** (the compose services mount the repo and expect `vendor/` to exist):
   ```bash
   cp .env.example .env
   docker run --rm -v $(pwd):/app -w /app composer:latest install
   ```
3. **Bring up the whole stack:**
   ```bash
   cd Docker
   docker compose up -d
   ```
   This starts everything and applies migrations automatically:
   - `db` - MariaDB (creates the `yaams` schema, with a healthcheck)
   - `migrate` - one-shot, runs `php artisan migrate` once the DB is healthy
   - `app` - `php artisan serve` on http://localhost:8000
   - `worker` - `queue:work`, so notification emails are delivered (they are queued, so a slow/failing SMTP can't block PIREP filing)
   - `scheduler` - `schedule:work`, prunes the activity log daily
   - `phpmyadmin` - http://localhost:8080
   - `smtp4dev` - catches outgoing mail at http://localhost:8081
4. **Generate the app key and seed the database:**
   ```bash
   docker exec yaams-dev-app php artisan key:generate
   docker exec yaams-dev-app php artisan migrate --seed
   ```
   The seeder prints the Sanctum API tokens for the test users to stdout.

### Default Test Accounts

The seeder creates three tiers of users (password for all: `start`):

| Role | Email |
| --- | --- |
| Pilot | `homer@test.com` |
| Manager | `test@test.com` |
| Super-Admin | `admin@test.com` |

## Running the Tests

```bash
docker exec yaams-dev-app php artisan test
```

The PHPUnit suite (`tests/`) covers the core domain rules - location continuity, the PIREP review workflow, aircraft lifecycle, invite redemption - and web/API parity. Tests run against an in-memory SQLite database, so no database service or seeding is required. They also run automatically on every pull request and push to `main` via GitHub Actions (`.github/workflows/tests.yml`).

## API

YAAMS ships a JSON REST API under `/api/v1`, authenticated with Sanctum bearer tokens - built for custom ACARS clients and integrations. Interactive documentation (generated with [Scribe](https://scribe.knuckles.wtf)) is served at **`/docs`**, with an OpenAPI spec at `/docs.openapi` and a Postman collection at `/docs.postman`.

## Monitoring

YAAMS exposes Prometheus metrics at **`/metrics`** - domain gauges (users, airlines, flights and aircraft by status, unused invite codes, queue depth, failed jobs) computed live on each scrape, so no extra storage or services are needed.

The endpoint is disabled by default. To enable it, set `METRICS_TOKEN` in `.env` and scrape with it as a bearer token:

```yaml
scrape_configs:
  - job_name: yaams
    scheme: https
    bearer_token: "<METRICS_TOKEN>"
    static_configs:
      - targets: ["your.domain"]
```

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

## Contributing

Contributions are welcome. Please open an issue to discuss larger changes first, and make sure the test suite passes (`php artisan test`) before opening a pull request - CI runs it automatically on every PR to `main`.

## License

Released under the [GNU General Public License v3.0](LICENSE).

---

> **Note:** This project was originally launched as a traditional development effort without the use of Artificial Intelligence. However, as the scope expanded, many of the current advanced features and architectural refinements were implemented with significant AI assistance to ensure modern standards and rapid delivery.
