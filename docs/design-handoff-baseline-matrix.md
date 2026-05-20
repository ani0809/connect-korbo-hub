# Design Handoff Baseline Matrix

Status legend: `pass` | `partial` | `fail` | `blocked`

## Route/Layout/Nav

| Area | Requirement | Target | Status | Notes |
|---|---|---|---|---|
| Frontend shell | Store layout with header/footer + overlays | `resources/views/frontend/layouts/app.blade.php` | partial | Core structure present; nav grouping parity not complete |
| Admin shell | Sidebar + topbar + content frame | `resources/views/admin/layouts/app.blade.php` | pass | Shell exists and functional |
| Account shell | Grouped sidebar IA | `resources/views/frontend/account/layouts/app.blade.php` | partial | Sidebar exists; group structure can be closer to handoff |
| Admin nav IA | Grouped hierarchy parity | `resources/views/admin/layouts/sidebar.blade.php` | partial | Permission-safe but not fully handoff taxonomy |
| Admin topbar | Search + actions parity | `resources/views/admin/layouts/topbar.blade.php` | partial | Search/notify/user present; needs minor parity polish |

## Token Contract

| Domain | Requirement | Target | Status | Notes |
|---|---|---|---|---|
| Semantic colors | HSL variable mapping | `resources/css/frontend/tokens.css` | pass | Core light/dark/sidebar tokens mapped |
| Tailwind semantic mapping | `hsl(var(--token) / <alpha>)` | `tailwind.config.js` | partial | Colors mapped; motion/screens/utilities need expansion |
| Typography contract | heading/body + scales | `resources/css/frontend/tokens.css` | partial | Families aligned; scale aliases can be tightened |
| Radius/shadow contract | semantic utilities parity | `resources/css/frontend/tokens.css` | partial | Values present; utility consistency still mixed |

## Component Contract

| Component | Requirement | Owner target | Status | Notes |
|---|---|---|---|---|
| Buttons | Single primitive contract | `resources/css/frontend/design-handoff-v2.css` | partial | Duplicates across frontend/admin/component css |
| Inputs | unified size/focus/error states | `resources/css/frontend/design-handoff-v2.css` | partial | Mixed selectors + hardcoded values remain |
| Cards/Tables/Badges | semantic visual contract | `resources/css/frontend/design-handoff-v2.css` | partial | Mostly aligned, not single-source yet |
| Toast/Skeleton/Modal | semantic + state parity | `resources/css/frontend/components.css` | fail | Heavy hardcoded colors, duplicated behavior styles |

## Critical Flows

| Flow | Requirement | Target | Status | Notes |
|---|---|---|---|---|
| Checkout | clear 3-step high-fidelity UX | `resources/views/frontend/checkout/index.blade.php` | partial | Works but needs handoff-level review-step polish |
| Order success | richer post-order state | `resources/views/frontend/checkout/success.blade.php` | fail | Currently minimal |
| Search | listing parity and visual hierarchy | `resources/views/frontend/search/index.blade.php` | partial | Improved, still limited vs full handoff intent |
| Cart | styled items/summary/progress | `resources/views/frontend/cart/index.blade.php` | partial | Solid baseline, token purity pass pending |
| Guest lookup | premium card/form/detail consistency | `resources/views/frontend/orders/*.blade.php` | partial | Functional, polish needed |

## Screenshot Evidence

| Requirement | Status | Notes |
|---|---|---|
| Handoff screenshot folder exists | fail | No `screenshots` assets present in extracted package |
| Named references from docs available | fail | Names documented, files absent |
| Backfill capture spec | partial | To be generated for missing references |

