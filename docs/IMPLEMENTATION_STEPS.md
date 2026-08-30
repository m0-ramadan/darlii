# Implementation Steps

## 1. Requirements
PHP 8.3+, Composer 2, MySQL 8+, Node.js 20+, NPM, PHP extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, curl.

## 2. Extract
Extract the ZIP to your server/project directory.

## 3. Install backend dependencies
`composer install`

## 4. Configure environment
Copy `.env.example` to `.env`, set APP_URL and MySQL credentials, then run `php artisan key:generate`.

## 5. Database
Create an empty UTF8MB4 database, then run `php artisan migrate --seed`.

## 6. Storage
Run `php artisan storage:link` and ensure `storage/` and `bootstrap/cache/` are writable by PHP-FPM/web server.

## 7. Frontend
Run `npm install && npm run build`.

## 8. Local validation
Run `php artisan test`, `php artisan route:list`, then `php artisan serve`.

## 9. Queue
Production: Supervisor/systemd must keep `php artisan queue:work --sleep=3 --tries=3` alive.

## 10. Scheduler
Add one cron entry calling `php artisan schedule:run` every minute.

## 11. Web server
Nginx/Apache document root MUST point to `/public`, never the project root.

## 12. Production flags
APP_ENV=production, APP_DEBUG=false, strong database password, HTTPS, secure mail settings, real queue/cache (Redis recommended), backups and monitoring.

## 13. Production caches
Run `php artisan optimize` after deployment. Restart queue workers after each release.

## 14. Security cleanup
Change/remove the seeded `admin@example.com` account, configure real email verification, audit upload limits, configure CSP/secure headers and payment/WhatsApp integrations only with server-side verification.
