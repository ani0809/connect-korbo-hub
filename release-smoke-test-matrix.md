# Release Checklist and Smoke Test Matrix

This checklist is for fast, repeatable release validation across frontend, backend, and API.

## 1) Pre-Release Checklist

- [ ] Confirm app is up in staging/production environment and `.env` is correct.
- [ ] Run cache clear steps: `php artisan optimize:clear`.
- [ ] Confirm storage symlink and upload paths are healthy.
- [ ] Confirm DB migration state is correct (no pending required migration).
- [ ] Confirm homepage, checkout, and auth endpoints are reachable.
- [ ] Confirm rate limit and auth middleware changes are active.
- [ ] Prepare rollback zip for immediate fallback.

## 2) Frontend Critical Smoke Tests

| Priority | Route | Test | Expected Result |
|---|---|---|---|
| P0 | `/` | Home render and dynamic sections load | Page loads without 500, sections render, no broken layout |
| P0 | `/search?keyword=test` | Search + skeleton/loader behavior | Results load, no JS crash, loader clears properly |
| P0 | `/product/{slug}` | Product details + related/frequently bought cards | Product page loads, cards render consistently on desktop/mobile |
| P0 | `/categories` | Category listing + navigation | Category page loads with consistent card spacing/layout |
| P0 | `/cart` | Cart summary + update quantity/remove | Cart updates without console/runtime error |
| P0 | `/checkout` | Checkout steps and payment options | Address/delivery/payment blocks visible, no 500 on submit flow |
| P1 | Footer (mobile + desktop) | Footer panel layout + mobile collapse | Collapse toggles work, dark background fills correctly |
| P1 | Mobile bottom nav | Tap home/categories/cart/account | All 4 actions work, active state correct, no overlap issues |
| P1 | Keyboard accessibility | Tab through links/buttons | Visible focus ring appears on interactive elements |

## 3) Backend/Admin Critical Smoke Tests

| Priority | Route | Test | Expected Result |
|---|---|---|---|
| P0 | `/admin` login flow | Admin authentication | Login success, dashboard loads |
| P0 | `/admin/cities` | Cities list/create/edit | No 500, CRUD pages render and save |
| P0 | `/admin/products` | Product list and create/edit screen | Screens load with stable UI and no fatal errors |
| P0 | `/admin/orders` | Order listing/details | Order detail opens, invoice view works |
| P1 | Admin sidebar | Navigation and active menu states | Sidebar renders correctly, no missing menu crash |
| P1 | Error pages | Trigger/visit 403/404/500/503 pages | Custom error pages load with custom image fallback |

## 4) Seller/Auth Panel Smoke Tests

| Priority | Route | Test | Expected Result |
|---|---|---|---|
| P0 | `/seller/login` | Seller login | Login flow works, seller dashboard loads |
| P1 | `/users/login` and `/users/registration` | Customer auth pages | Auth pages render without layout/script errors |
| P1 | Auth page navigation | Page transition skeleton behavior | Skeleton shows/hides cleanly between auth pages |

## 5) API Critical Smoke Tests (v2)

Use Postman/cURL with real sample payloads.

| Priority | Endpoint | Method | Test | Expected Result |
|---|---|---|---|---|
| P0 | `/api/v2/auth/login` | POST | Valid + invalid login | Valid returns token/user, invalid returns standardized error |
| P0 | `/api/v2/auth/signup` | POST | Signup validation | Validation returns consistent 422 shape on bad payload |
| P0 | `/api/v2/auth/social-login` | POST | Provider/token validation | Invalid provider/token handled safely with clear error |
| P0 | `/api/v2/auth/password/forget_request` | POST | Reset request throttling | Too many attempts returns 429 |
| P0 | `/api/v2/auth/password/confirm_reset` | POST | Reset confirm success/failure | Success resets password; invalid code returns safe error |
| P0 | `/api/v2/products` | GET | Product listing | Response success with expected data shape |
| P0 | `/api/v2/products/search` | GET | Search endpoint behavior | Returns results without runtime error |
| P1 | `/api/v2/carts` | POST | Cart list flow | Returns cart list with stable response structure |
| P1 | `/api/v2/order/store` | POST | Order store transaction safety | Failure cases rollback cleanly; no partial corrupted state |

## 6) Browser and Device Matrix

- [ ] Chrome latest (desktop)
- [ ] Edge latest (desktop)
- [ ] Android Chrome (mobile)
- [ ] iOS Safari (mobile)
- [ ] At least one low-width device (~360px) and one tablet width (~768px)

## 7) Pass/Fail Gate

Release is **GO** only if:

- [ ] All P0 checks passed.
- [ ] No new 500/JS fatal errors found in logs/console.
- [ ] No checkout/auth/API regression in core routes.

Release is **NO-GO** if any of these fail:

- [ ] Home, checkout, or auth pages fail to load.
- [ ] Any P0 API endpoint returns unexpected 500 or malformed response.
- [ ] Admin critical sections fail to open/save.

## 8) Fast Rollback Plan

- Keep previous stable zip ready on server.
- If NO-GO condition appears, deploy previous stable zip immediately.
- Run `php artisan optimize:clear` after rollback.
- Re-run only P0 smoke tests to confirm service restoration.
