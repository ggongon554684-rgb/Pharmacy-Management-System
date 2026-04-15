# Pharmacy Management System

Role-based pharmacy web app built with Laravel.  
Focus areas: inventory flow, patient records, purchase orders, stock movements, sales/POS, and audit logging.

## Core Features

- Product and inventory batch management
- Patient management and patient purchase history
- Purchase order creation, approval, and receiving
- Stock request creation and fulfillment (FEFO deduction)
- POS / medicine release flow
- Optional prescription linkage for sales
- Immutable stock movement tracking
- Audit trail for key CRUD and flow actions
- Role-based access control using Spatie Permission
- Admin finance status board (revenue, purchase costs, gross estimate)

## Tech Stack

- Laravel (PHP)
- Blade templates + Bootstrap
- MySQL/SQLite
- Spatie Laravel Permission
- PHPUnit feature testing

## Role Responsibilities

- `staff`
  - Manage products/inventory (including **Add Product**)
  - Create purchase orders
  - Receive incoming deliveries
  - Approve and fulfill stock release requests
  - View reports and stock movements
- `pharmacist`
  - Manage patients
  - Record sales / release medicine via POS
  - Create stock requests for low front-shop stock
  - View patient-related reports
- `admin`
  - Approve purchase orders
  - View incoming deliveries and stock movements
  - Perform stock override (with admin PIN)
  - View patients and audit logs
  `Customers`
  http://127.0.0.1:8000/kiosk-order
  

## Setup

1. Install dependencies
```bash
composer install
npm install
```

2. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

3. Database and seed
```bash
php artisan migrate --seed
```

4. Run app
```bash
php artisan serve
npm run dev
```

## Reset and Reseed

Use this when you want a clean demo database:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```

## Default User Credentials

After seeding:

- Admin: `admin@pharmacy.com` / `password`
- Pharmacist: `pharmacist@pharmacy.com` / `password`
- Staff: `staff@pharmacy.com` / `password`

If credentials fail after updates, run:

```bash
php artisan db:seed --class=RolesAndPermissionSeeder
php artisan db:seed --class=UserSeeder
php artisan optimize:clear
```

## Main Workflow

1. Staff creates PO
2. Admin approves PO
3. Staff receives PO (creates batches + incoming stock movement)
4. Pharmacist creates stock request when needed
5. Staff fulfills stock request (FEFO from non-expired batches)
6. Pharmacist records sale (POS), stock is deducted, history is recorded
7. Admin can override stock with PIN (audited)

## Important Paths

- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers`
- Models: `app/Models`
- Migrations: `database/migrations`
- Seeders: `database/seeders`
- Views: `resources/views`
- Tests: `tests/Feature/SystemFlowTest.php`

## Testing

Run full test suite:

```bash
php artisan test
```

Current system flow tests include role-based end-to-end coverage for staff/pharmacist/admin actions.