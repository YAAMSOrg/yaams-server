# YAAMS - Yet Another Airline Management System

YAAMS is an alternative to PHPvms or Virtual Airline Manager. YAAMS takes a more modern approach to virtual airlines software by using new technologies and going by the standard of "API first". It is at a very early development stage and not ready for production use.

## Current status

We've decided to start the coding now with Laravel. It seems to be the excellent system for this kind of project.

Since Laravel also includes builtin support for building REST APIs it suits perfectly.

## Setting up dev environment

### Docker
* Install Docker
* Build the YAAMS docker image, which is located under Docker/Dockerfile using `docker build . -t yaams-app:dev`
* Create the docker network, which is needed for internal container communication: `docker network create yaams`
* Run the docker-compose.yml which is also located in the Docker folder
* Run php artisan commands using the newly built container: `docker run -it --rm -v $(pwd):/app -p 8000:8000 yaams-app:dev bash`
* Run the migrations and seed the db with example data: `php artisan migrate && php artisan db:seed` 

Notice: When you run a dev server, please use `php artisan serve --host 0.0.0.0` as command!

### Native
* Install a Laravel development environment (with a DB, composer and PHP)
* `git clone https://github.com/flymia/YAAMS.git` in your directory
* Run a `composer update` to install the components
* Done. Help by adding new features as you like and do a PR!

## Planned features

* API first, so you can build your own client easily
* A client written in form of a web interface
* Fleet management
* Pilot management
* PIREP filing
* Authentification system
* A multi platform ACARS (for Linux, Windows & Mac OS)
* SaaS hosted

## Current TODOs

* Get base authentification working ✅
* Create the PIREP filing process

## Open Source

This software is open source with a reason: Because we want you to commit to the project!
