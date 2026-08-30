#!/usr/bin/env bash
set -e

cd /var/www/html

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    echo "[Homzen] Installing Composer dependencies inside PHP 8.3 container..."
    composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
    if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        echo "[Homzen] Generating APP_KEY..."
        php artisan key:generate --force
    fi
fi

exec "$@"
