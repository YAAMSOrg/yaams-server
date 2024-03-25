# YAAMS - Yet Another Airline Management System

YAAMS is an alternative to PHPvms or Virtual Airline Manager. YAAMS takes a more modern approach to virtual airlines software by using new technologies and going by the standard of "API first". It is at a very early development stage and not ready for production use.

## Planned features

* API first, so you can build your own client easily
* A built in client written in form of a web interface
* Fleet management
* Pilot management
* PIREP filing
* Tenant aware (multiple VAs on one system)
* Authentification system with user groups
* A multi platform ACARS (for Linux, Windows & Mac OS) - Help needed!
* SaaS hosted for a small fee, when it's stable

## Current status

The main priority, in order, as of now is the following:

* Ease up the dev environment (including better documentation)
* Rework the database model
* Get the API working for read and write access

## Setting up dev environment

### Using containers (Docker, Podman, etc.)

* Install Docker / Podman
* Build the YAAMS docker image, which is located under `Docker/Dockerfile` using `cd Docker; docker build . -t yaams-app:dev`
* Create the docker network, which is needed for internal container communication: `docker network create yaams`
* In the main folder, copy the `.env.example` to `.env`
* Run the docker-compose.yml which is also located in the Docker folder using `cd Docker; docker-compose up -d`. These containers are the database and a GUI using phpMyAdmin.
* Run php artisan commands using the newly built container: `docker run -it --rm --network yaams -v $(pwd):/app -p 8000:8000 yaams-app:dev bash`
* Run the migrations and seed the db with example data: `php artisan migrate && php artisan db:seed`

Notice: When you run a dev container, please use `php artisan serve --host="0.0.0.0"` as command.

### Native
* Install a Laravel development environment (with a DB, composer and PHP)
* `git clone https://github.com/flymia/YAAMS.git` in your directory
* Run a `composer update` to install the components
* Done. Help by adding new features as you like and do a PR!

#### Example users

The default users and password for testing are: 
* test@test.com / start
* homer@test.com / start

## Open Source

This software is open source with a reason: Because we want you to commit to the project!
