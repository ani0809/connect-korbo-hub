# MyShop — Installation guide

## Before you start

- PHP 8.2 or newer with extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip` (recommended for imports/PDF)
- MySQL 8+ or MariaDB 10.6+
- Composer 2
- Node 18+ (to build frontend/admin assets)

## Step 1 — Upload

Upload the project to your hosting (e.g. `public_html`). Point the web server document root to the `public` directory.

## Step 2 — Environment

1. Copy `.env.example` to `.env` (if `.env` is not present).
2. Set `APP_URL`, database credentials, and mail settings.
3. Run:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
npm install
npm run build
```

## Step 3 — Web installer

Visit `https://yourdomain.com/install` and complete the wizard (database check, admin user, license if required).

## Step 4 — Scheduler (required)

Cron (every minute):

```bash
* * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1
```

This runs maintenance: points expiry, cart cleanup, newsletter schedules, price alerts, license ping, and other scheduled tasks.

## Step 5 — Queue worker (recommended)

For emails, newsletters, and queued jobs:

```bash
php artisan queue:work --tries=3
```

On shared hosting you can use a cron every 5 minutes:

```bash
*/5 * * * * php /path/to/artisan queue:work --stop-when-empty
```

## Step 6 — Mobile API

- Base URL: `https://yourdomain.com/api/v1`
- Authentication: `Authorization: Bearer {token}` (Sanctum)
- Optional docs: `https://yourdomain.com/api-docs/`

## Step 7 — Production check

```bash
php artisan shop:production-check
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Resolve any failed checks before going live.

## SSL

Use HTTPS in production. Set `APP_URL` with `https://` and configure your server or reverse proxy accordingly.
