# YAAMS - Yet Another Airline Management System

An decentralised open-source virtual airline management platform. Track PIREPs, manage fleets, and build thriving virtual airline communities - all in one place.

![video demo](https://github.com/YAAMSOrg/yaams-server/blob/main/Docs/res/showcase.gif)

## Features

* PIREP filing, review, and acceptance workflow
* Fleet management with airframe tracking and metrics
* Pilot dashboard with verified flight hours and statistics
* Multi-airline support with easy switching
* In-app notification system
* REST API (v1) for custom ACARS clients and integrations

## Technical Foundation

* Framework: Laravel 12.x
* Frontend: Bootstrap 5.3.3 / Blade / Vanilla CSS
* Authentication: Laravel Sanctum & Fortify
* Permissions: Spatie Laravel Permission
* Environment: Docker-ready and NixOS support

## Development Setup

### Containerized Environment (Docker/Podman)

1. Build the application image:
   ```bash
   cd Docker
   docker build . -t yaams-app:dev
   ```
2. Setup the network and infrastructure:
   ```bash
   docker network create yaams
   cd Docker
   docker-compose up -d
   ```
3. Initialize the application:
   ```bash
   docker run -it --rm --network yaams -u $(id -u):$(id -g) -v $(pwd):/app -p 8000:8000 yaams-app:dev bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```
4. In a separate shell, start the queue worker so notification emails are
   delivered (they are queued so a slow/failing SMTP can't block PIREP filing):
   ```bash
   docker exec yaams-dev-app php artisan queue:work
   ```
   Sent mail is viewable in smtp4dev at http://localhost:8081.

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
