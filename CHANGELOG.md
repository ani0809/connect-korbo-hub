# Changelog

## [1.1.0] - 2026-04-18

### Added
- Accounting module (Chart of Accounts, Journals, Expenses, Bank Accounts, Tax Rates, P&L, Balance Sheet, Trial Balance, Cash Flow, Tax report).
- WooCommerce data migration system with queued migration jobs, progress polling, dry-run mode, and import logs.
- Server-side tracking service for Facebook Conversion API and GA4 Measurement Protocol.
- Advanced AJAX search with query analytics, popular searches, no-result tracking, and cached ranked results.
- Premium PDF invoice redesign plus thermal/simple invoice template.
- Guest order lookup flow (email + order number) and guest checkout account creation option.
- Admin tracking settings UI for Facebook/GA4 tokens.
- Search analytics admin report.

### Improved
- Checkout flow now triggers purchase tracking events after paid and COD order confirmation.
- Product page and add-to-cart flow now dispatch tracking events asynchronously.
- Scheduler updated with additional production tasks (sitemap rebuild, translation rebuild, warehouse stock sync, accounting auto-journal).
- Queue config expanded with dedicated queue names for accounting, migrations, imports, and newsletters.
- Seeder bootstrap hardened with dynamic class checks for safer deployments.

## [1.0.0] - 2026-04-09

### Added
- Full Laravel 11 self-hosted eCommerce stack with installer + license activation.
- Admin/seller/customer panels, product/catalog/order/checkout systems.
- Payment gateways and invoice/export flow.
- Visual builder engine for header/footer/home/product/shop pages.
- Notification system: email templates, SMTP/SMS settings, push, in-app, newsletters.
- Reports + analytics: sales, products, customers, earnings, GA4 dashboard service.
- SEO suite: dynamic meta/schema generation, sitemap.xml, robots.txt.
- Marketing tools: flash deals, abandoned cart, price tracker, waitlist, recently viewed.
- Addon lifecycle manager, one-click update path, demo import tools.
- Frontend polish modules: lazy-load, countdown, lightbox, scroll-to-top, dark mode.

### Improved
- Queue retry/backoff handling for notifications and campaigns.
- Configurable settings bootstrap and dynamic CSS variables.
- Security and performance hardening for production deployments.
