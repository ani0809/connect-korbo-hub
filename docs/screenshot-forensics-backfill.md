# Screenshot Forensics and Backfill

## Folder forensics

- Expected by docs: `/screenshots/` and `/components/`
- Observed in extracted handoff: no image files present
- Result: all screenshot checks are `blocked` until backfill capture set is produced

## Evidence status

| Source | Expected | Found | Status |
|---|---:|---:|---|
| FULL-SYSTEM screenshot references | 18+ | 0 | blocked |
| UI-SYSTEM screenshot table | 11 | 0 | blocked |
| notes naming conventions | 40+ | 0 | blocked |

## Backfill capture checklist

### Store pages
- `01-homepage-desktop.png` (1440x900)
- `01b-homepage-mobile.png` (390x844)
- `02-shop-listing.png` (1440x900)
- `02b-shop-mobile.png` (390x844)
- `03-product-detail.png` (1440x900)
- `04-cart.png` (1440x900)
- `05-checkout.png` (1440x900)
- `store/16-order-success.png` (1440x900)

### Admin/POS
- `06-admin-dashboard.png` (1440x900)
- `07-pos-terminal.png` (1440x900)

### Component/state captures
- `components/header-sticky-and-cards.png`
- `components/footer-newsletter.png`
- `buttons-all-states.png`
- `inputs-all-states.png`
- `tables-standard.png`
- `cards-variants.png`
- `badges-status.png`
- `modals-standard.png`
- `toasts-types.png`
- `sidebar-expanded.png`
- `sidebar-collapsed.png`
- `topbar-standard.png`
- `empty-states.png`
- `loading-skeletons.png`
- `error-states.png`

## Diff classification protocol

- `critical`: breaks layout/flow/interaction requirement
- `major`: clear visual mismatch in spacing/color/component hierarchy
- `minor`: polish-level mismatch only

