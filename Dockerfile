FROM drupal:11-php8.3-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libavif-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libwebp-dev \
        openssl; \
    docker-php-ext-configure gd \
        --with-avif \
        --with-freetype \
        --with-jpeg \
        --with-webp; \
    docker-php-ext-install -j"$(nproc)" gd pdo_pgsql pgsql; \
    mkdir -p /etc/apache2/ssl; \
    openssl req -x509 -nodes -days 3650 \
        -newkey rsa:2048 \
        -keyout /etc/apache2/ssl/localhost.key \
        -out /etc/apache2/ssl/localhost.crt \
        -subj "/C=BR/ST=SP/L=SaoPaulo/O=LocalDev/OU=Docker/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"; \
    a2enmod ssl headers rewrite; \
    a2ensite default-ssl; \
    apt-get purge -y --auto-remove; \
    rm -rf /var/lib/apt/lists/*

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./docker/apache/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
