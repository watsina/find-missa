# Build Stage - Use Node.js for frontend build (Debian-slim for npm compatibility)
FROM node:22-bookworm-slim AS frontend-builder

WORKDIR /build

# Copy frontend dependencies and install
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline --no-audit

# Copy only necessary files for Vite build
COPY vite.config.js ./
COPY ui ./ui
COPY resources ./resources
COPY public ./public

# Vite statically replaces import.meta.env.VITE_* at build time.
# These must be passed via --build-arg during docker build.
ARG VITE_APP_NAME
ARG VITE_POSTHOG_ENABLED
ARG VITE_POSTHOG_KEY
ARG VITE_POSTHOG_HOST

RUN npm run build

# PHP Dependencies Stage
FROM composer:2 AS php-dependencies

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs

COPY . .
RUN rm -f bootstrap/cache/*.php && \
    composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# Final Production Stage - Minimal runtime
FROM php:8.4-cli-alpine

LABEL maintainer="Eugene Owak"

ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/local/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="findMissa"

# Install system dependencies
RUN apk add --no-cache \
    supervisor \
    dcron \
    bash \
    libcap \
    shadow \
    gosu \
    sqlite \
    sqlite-libs \
    libpng \
    libjpeg-turbo \
    libzip \
    icu-libs \
    oniguruma \
    curl

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Allow PHP to bind to port 80
RUN setcap "cap_net_bind_service=+ep" /usr/local/bin/php

# Create application user and group
RUN addgroup -g $WWWGROUP findMissa \
    && adduser -D -u 1337 -G findMissa -s /bin/bash findMissa

# Copy application from build stages
COPY --from=php-dependencies --chown=findMissa:findMissa /app /var/www/html
COPY --from=frontend-builder --chown=findMissa:findMissa /build/public/ /var/www/html/public/

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    /var/log/supervisor \
    && chown -R findMissa:findMissa storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configure Supervisor for PHP, Queue, and Cron
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configure cron for Laravel scheduler
RUN echo "* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/findMissa \
    && chown findMissa:findMissa /etc/crontabs/findMissa

# Copy and configure startup script
COPY docker/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# Configure PHP for production
COPY docker/php.ini /usr/local/etc/php/conf.d/99-findMissa.ini

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
