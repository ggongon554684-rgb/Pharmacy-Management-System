# DEV NOTES

## Current State (Completed)

- Two-tier inventory is active:
  - `back` = reserve stock
  - `front` = release stock for POS/scan
- Stock request fulfillment is transfer-based (`back -> front`), not generic release.
- Staff can adjust approved quantity before fulfilling stock requests.
- Stock request approval table now shows current `Front Shop` and `Back Inventory` stock.
- POS and QR scan sale fulfillment consume **front-shop stock only**.
- Staff dashboard was redesigned to match admin design language and uses shared dashboard CSS.
- Shared styling extracted to:
  - `resources/css/app.css`
  - `resources/css/dashboard.css`
- Pagination renderer fixed globally with Bootstrap 5:
  - `Paginator::useBootstrapFive()` in `app/Providers/AppServiceProvider.php`
- Kiosk ticket now has a `Print QR` button.

## Recent Stability/Cleanup Fixes

- Removed temporary debug agent script from `resources/views/layouts/app.blade.php`.
- Removed duplicate Bootstrap Icons `<link>` from `resources/views/admin/dashboard.blade.php`.
- Moved app shell/root variable styles from inline body style to compiled CSS in `resources/css/app.css`.
- Rebuilt frontend assets successfully.

## Important URLs

- Kiosk order page: `/kiosk-order`
- Local example: `http://127.0.0.1:8000/kiosk-order`

## Seed / Run Commands

```bash
php artisan db:seed
php artisan serve
npm run dev
```

For clean reset:

```bash
php artisan migrate:fresh --seed
```

## Key Architecture Decisions To Keep

- FEFO release logic is centralized in `app/Services/InventoryReleaseService.php`.
- Location-aware release rules:
  - Sales / scan release from `front`
  - Stock request fulfillment from `back`
- Dashboard/UI consistency should go through shared classes in `resources/css/dashboard.css`.
- Shared module surfaces/tables/badges should reuse `.module-*` and `.status-*` CSS.

## Key Files To Know

- Inventory flow
  - `app/Http/Controllers/StockRequestController.php`
  - `app/Http/Controllers/SalesController.php`
  - `app/Services/InventoryReleaseService.php`
  - `app/Models/InventoryBatch.php`
- Dashboards / shared UI
  - `resources/views/admin/dashboard.blade.php`
  - `resources/views/staff/dashboard.blade.php`
  - `resources/css/app.css`
  - `resources/css/dashboard.css`
- Kiosk
  - `app/Http/Controllers/PreOrderController.php`
  - `resources/views/public/kiosk-ticket.blade.php`
- Layout / navigation
  - `resources/views/layouts/app.blade.php`
  - `resources/views/layouts/navigation.blade.php`
- Routes / tests
  - `routes/web.php`
  - `tests/Feature/SystemFlowTest.php`

## Deferred / Next Work (Not Completed)

- Full Prescriber + Prescription roadmap is planned but **paused**.
- Planned phases (paused):
  1. RX link hardening
  2. RX management UI
  3. Optional dispensing enforcement
  4. RX reporting + audit

## Development Guardrails

- After CSS/layout edits, always rebuild/restart frontend assets:
  - `npm run dev` (watch mode) or `npm run build` (compiled)
- Keep route access controlled with permissions (`can:*`) and role-aware navigation.
- Add/adjust feature tests when changing inventory transfer or release rules.
