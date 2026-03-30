# CI runner image for PHP-SF Template
#
# PHP 8.3 + all required extensions + Composer + Node 20 LTS
# Built once, pushed to a registry, referenced in workflow `container:` blocks.
#
# Build:
#   docker build -t php-sf-ci docker/
# Push example:
#   docker tag php-sf-ci ghcr.io/<owner>/php-sf-ci:latest
#   docker push ghcr.io/<owner>/php-sf-ci:latest

FROM php:8.3-cli-bookworm

# ── System packages ───────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        # build toolchain (needed for PECL compilation)
        build-essential pkg-config \
        # common utilities
        git unzip zip curl ca-certificates gnupg \
        # AMQP extension (librabbitmq)
        librabbitmq-dev libssl-dev \
        # Memcached extension
        libmemcached-dev \
        # YAML extension
        libyaml-dev \
        # Intl extension (ICU)
        libicu-dev \
        # GD extension
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        # cURL / HTTP (pecl_http) extension
        libcurl4-openssl-dev \
        # PostgreSQL driver
        libpq-dev \
        # MySQL / MariaDB driver + CLI client (useful for healthchecks / fixtures)
        default-mysql-client \
        # Zip extension
        libzip-dev \
        # zlib (needed by memcached, zlib ext)
        zlib1g-dev \
        # mbstring extension
        libonig-dev \
        # xml extension
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions — compiled from source ─────────────────────────────────────
# Note: curl, iconv, json, zlib are already compiled into the base image.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        ctype \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        posix \
        sockets \
        xml \
        zip

# ── PHP extensions — PECL ─────────────────────────────────────────────────────
# raphf must be installed before pecl_http (it is a hard dependency)
RUN pecl install raphf \
    && docker-php-ext-enable raphf \
    \
    && pecl install amqp \
    && pecl install apcu \
    && pecl install redis \
    && pecl install memcached \
    && pecl install yaml \
    && pecl install pecl_http \
    \
    && docker-php-ext-enable amqp apcu redis memcached yaml http \
    \
    && rm -rf /tmp/pear

# ── pcov — fast code-coverage driver for PHPUnit ──────────────────────────────
RUN pecl install pcov && docker-php-ext-enable pcov

# ── Composer 2 ────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Node.js 20 LTS ────────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# ── Misc CI utilities ─────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        jq \
        postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# ── Sanity check (prints versions at build time, fails fast on bad installs) ──
RUN php -v \
    && php -m \
    && composer --version \
    && node --version \
    && npm --version

WORKDIR /app
