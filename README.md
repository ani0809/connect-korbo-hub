# MyShop — Final Production Build

MyShop is a complete self-hosted Laravel eCommerce platform for Bangladesh and global stores, with multi-vendor support, advanced checkout, POS, accounting, migration tools, and production-grade automation.

## Quick Install

1. Copy project to server and point web root to `public/`.
2. Copy `.env.example` to `.env` and fill DB, app URL, mail, and queue settings.
3. Run:
   - `composer install`
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan storage:link`
4. Build frontend assets:
   - `npm install`
   - `npm run build`
5. Open `/install` and complete installer + license activation.

## Required Runtime

- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Composer 2+
- Node.js 18+

## Queue & Scheduler (Production)

Set cron:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Run queue workers (recommended split):

```bash
php artisan queue:work --queue=default,imports --tries=3
php artisan queue:work --queue=accounting,migrations --tries=3
php artisan queue:work --queue=newsletters --tries=3
```

## Key Modules

- Storefront, checkout, order lifecycle, guest checkout + order lookup
- Multi-vendor seller panel, commissions, withdrawals
- Payment gateways (10+), wallet, club points, referral
- Promotions engine (buy X get Y, bundles, quantity tiers, free shipping)
- Fraud detection and courier integrations (Pathao, Steadfast, RedX)
- POS terminal with cash/session controls
- Accounting (double-entry, journals, P&L, balance sheet, cash flow, tax)
- WooCommerce migration wizard + queued importer
- Server-side tracking (Facebook CAPI + GA4 Measurement Protocol)
- Advanced AJAX live search + search analytics
- PDF invoices (premium + thermal)
- CMS, blog, FAQ, builder, newsletter, analytics, translations, addons

## API

- Base: `/api/v1`
- Auth: Laravel Sanctum bearer tokens
- API docs: `/api-docs`

## Pre-Go-Live Checklist

```bash
php artisan shop:production-check
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan queue:restart
```

## License

Usage and distribution are governed by your commercial license terms.
