FROM php:8.3-cli

LABEL org.opencontainers.image.authors="YAAMS Dev Team"

RUN apt update
RUN apt install -y curl git zip unzip libpng-dev libjpeg-dev libfreetype-dev libwebp-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install mysqli pdo_mysql gd
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
