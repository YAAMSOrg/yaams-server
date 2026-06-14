# YAAMS - Yet Another Airline Management System

YAAMS is a modern, high-performance Virtual Airline Management System built with a strict "API-first" philosophy. Designed as a flexible alternative to legacy systems, YAAMS provides a robust foundation for virtual aviation organizations to manage their operations, fleets, and pilot communities through modern web technologies and a powerful RESTful interface.

## Core Pillars

### API-First Architecture
Every feature available in the web interface is backed by a fully documented RESTful API (v1). This allows developers to build custom ACARS clients, mobile apps, or specialized flight tracking tools that integrate seamlessly with the YAAMS ecosystem.

### Comprehensive PIREP Workflow
YAAMS features a sophisticated Flight Reporting (PIREP) system:
* Automated notification system for airline managers when new flights are filed.
* Detailed validation interface for reviewers to accept or reject flights.
* Support for rejection remarks to provide clear feedback to pilots.
* Real-time notification updates for pilots regarding their flight status.

### Professional Fleet Management
Manage your airline's assets with precision:
* Detailed aircraft tracking including service dates and airframe history.
* Status-aware fleet listings (Active/Inactive).
* Automated airframe metrics based on validated flight hours.

### Pilot Experience
A clean, responsive dashboard designed for clarity:
* Personalized statistics including verified flight hours and count.
* Real-time notification center for operation updates.
* Integrated airline switching for pilots flying for multiple organizations.

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
