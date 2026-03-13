FROM drupal:11-php8.3-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libavif-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libwebp-dev; \
    docker-php-ext-configure gd \
        --with-avif \
        --with-freetype \
        --with-jpeg \
        --with-webp; \
    docker-php-ext-install -j"$(nproc)" gd pdo_pgsql pgsql; \
    apt-get purge -y --auto-remove; \
    rm -rf /var/lib/apt/lists/*

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
