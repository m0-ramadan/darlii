FROM php:8.3-cli-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip zip \
    libzip-dev libicu-dev libonig-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql pdo_sqlite mbstring bcmath intl zip pcntl dom xml xmlwriter \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g ${GID} laravel 2>/dev/null || true \
    && useradd -m -u ${UID} -g ${GID} -s /bin/bash laravel 2>/dev/null || true

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/99-homzen.ini
COPY docker/entrypoint.sh /usr/local/bin/homzen-entrypoint
RUN chmod +x /usr/local/bin/homzen-entrypoint

EXPOSE 8000

ENTRYPOINT ["homzen-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
