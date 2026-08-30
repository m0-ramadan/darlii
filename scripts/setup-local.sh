#!/usr/bin/env bash
set -euo pipefail
[ -f .env ] || cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link || true
npm install
npm run build
php artisan test
printf '\nReady. Run: php artisan serve\n'
