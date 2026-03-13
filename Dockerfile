FROM drupal:11-php8.3-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libpq-dev; \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql; \
    apt-get purge -y --auto-remove; \
    rm -rf /var/lib/apt/lists/*

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
