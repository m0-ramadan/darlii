# Shared Hosting / Hostinger deployment

Preferred approach: set the domain document root directly to the Laravel `public/` directory.

If the panel lets you select document root:
1. Upload/extract the project outside the public web root when possible.
2. Point domain document root to `/path/to/HomzenLaravel/public`.
3. Use SSH: `composer install --no-dev --optimize-autoloader`.
4. Configure `.env` with production DB and APP_URL.
5. Run `php artisan key:generate` only for a new installation.
6. Run `php artisan migrate --seed --force` on first deployment; on later deployments use `php artisan migrate --force`.
7. Run `npm run build` locally or on the server if Node is available; upload `public/build` if building locally.
8. Run `php artisan storage:link` if symlinks are supported.
9. Set writable permissions for `storage` and `bootstrap/cache`.
10. Configure cron for `schedule:run` every minute.
11. Configure a persistent queue worker if the hosting plan supports it; otherwise use cron-driven queue processing only as a limited fallback.
12. Set `APP_DEBUG=false`.

Do not expose `.env`, `vendor` internals, `storage/logs`, or the project root as the document root.
