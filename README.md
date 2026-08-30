# Homzen Laravel — Exact Frontend / Laravel Backend

> **Important:** The uploaded Homzen HTML/CSS/JS is the visual source of truth. Public pages are served from the original mirror files; Laravel supplies the backend. Do not redesign or replace the public frontend with the scaffold Blade views.

See `docs/EXACT_FRONTEND_MODE.md` for details.

# Homzen Laravel Rebuild

A Laravel 13 modular-monolith foundation reconstructed from the provided Homzen static mirror. It includes a normalized multilingual database, recovered catalog seeder, property/project/agent/content flows, lead capture, moderation, RBAC foundation, API endpoints, RTL support, and legacy theme assets.

## Important

This archive is **source code**, so `vendor/` and `node_modules/` are intentionally excluded. Run Composer/NPM after extraction.

### Recovered content included
- 62 properties
- 18 projects
- 12 agents
- 24 posts
- 6 careers
- English, Arabic, French, Indonesian, Vietnamese translations where recoverable

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
# create MySQL DB and update .env
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Then open `http://127.0.0.1:8000`.

Demo admin seeded for local development only:
- Email: `admin@example.com`
- Password: `ChangeMe123!`

**Change/delete this account before production.**

## Queue

```bash
php artisan queue:work
```

## Scheduler

Cron:
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Tests

```bash
php artisan test
```

See `docs/IMPLEMENTATION_STEPS.md` and `docs/PROJECT_STATUS.md` before production deployment.

## Docker (recommended on PHP < 8.3 hosts)

```bash
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Open `http://localhost:8000`. Full Arabic guide: `docs/DOCKER_AR.md`.
