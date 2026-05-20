# Component Owner Map

## Ownership layers

- `tokens`: `resources/css/frontend/tokens.css`
- `base component`: `resources/css/frontend/design-handoff-v2.css`
- `page override / interaction utility`: `resources/css/frontend/components.css`
- `admin shell base`: `resources/css/admin/design-system.css`

## Primitive ownership

| Primitive | Owner | Notes |
|---|---|---|
| Button base (`.btn-primary`, `.btn-secondary`) | `design-handoff-v2.css` | Canonical visual contract |
| Input/select/textarea base | `design-handoff-v2.css` | Shared focus/error baseline |
| Card/table/badge base | `design-handoff-v2.css` | Semantic token styles |
| Toast/skeleton/empty-state utility | `components.css` | Runtime UX behavior utilities |
| Admin card/form/table primitives | `admin/design-system.css` | Admin-only structure rules |

## Compatibility policy

- Legacy selectors remain only as alias/wrapper classes.
- New visual tweaks must be done in owner file, not duplicated across files.
- Page-level files should only add context-specific spacing/layout, not redefine primitives.

