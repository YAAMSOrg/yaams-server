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

What works already:
* Add aircrafts
* Add airlines via API
* Create users and login
* Add flights

### Showcase

For example, this is the file PIREP form. This is all a WIP.

![PIREP filing demo](https://raw.githubusercontent.com/YAAMSOrg/yaams-sever/main/Docs/res/file_pirep_showcase.gif)

## Setting up dev environment

### Using containers (Docker, Podman, etc.)

* Install Docker / Podman
* Build the YAAMS docker image, which is located under `Docker/Dockerfile` using `$ cd Docker; docker build . -t yaams-app:dev`
* Create the docker network, which is needed for internal container communication: `$ docker network create yaams`
* In the main folder, copy the `.env.example` to `.env`
* Run the docker-compose.yml which is also located in the Docker folder using `$ cd Docker; docker-compose up -d`. These containers are the database and a GUI using phpMyAdmin.
* Run php artisan commands using the newly built container: `$ docker run -it --rm --network yaams -u $(id -u):$(id -g) -v $(pwd):/app -p 8000:8000 yaams-app:dev bash`
* Install the composer components: `$ composer install`
* Run the migrations and seed the db with example data: `$ php artisan migrate && php artisan db:seed`

Notice: When you run a dev container, please use `$ php artisan serve --host="0.0.0.0"` as command.

### Using NixOS flakes

If you are running NixOS on your machine, you can enter a dev shell using the provided NixOS flake by running `nix develop`. This will provide composer and php in a temporary dev shell. However, as of right now, you still need to use Docker for the db and phpMyAdmin.

### Native
* Install a Laravel development environment (with a DB, composer and PHP)
* `git clone https://github.com/YAAMSOrg/yaams-server.git` in your directory
* Run a `composer install` to install the components
* Done. Help by adding new features as you like and do a PR!

## Developing

Since this project is at a very early development stage, it can happen, that Laravel migrations are changed. So I recommend, that, before you start working on your tasks, you run a `php artisan migrate:fresh; php artisan db:seed` before starting your development.

This will change in the future, since we then start migrations the way they should be used, but at this time it is way more practical to just edit the migrations that are already there.

#### Example users

The default users and password for testing are: 
* homer@test.com / start (Role: Pilot)
* test@test.com / start (Role: Manager)
* admin@test.com / start (Role: Admin)

The auth tokens for developing the API are issued on the db:seed command and printed out. 

**Please store them somewhere, they are not shown again!**

## Used libraries

* [laravel-permission](https://github.com/spatie/laravel-permission)
* [laravel-sanctum](https://laravel.com/docs/11.x/sanctum)
* [laravel-maps](https://github.com/LarsWiegers/laravel-maps)

## Open Source

This software is open source with a reason: Because we want you to commit to the project!
