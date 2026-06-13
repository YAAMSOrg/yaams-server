FROM php:8.3-cli

LABEL org.opencontainers.image.authors="YAAMS Dev Team"

RUN apt update
RUN apt install -y curl git zip unzip
RUN docker-php-ext-install mysqli pdo_mysql
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
