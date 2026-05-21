# phpMyAdmin cheat sheet (presentation segment)

Use this during the **Database connection** (~45s) and **Live demo** (~1 min) sections. Your real database name is whatever you set as `DB_DATABASE` in `.env` (the Laravel default in [.env.example](.env.example) is `laravel`—replace if yours differs).

---

## Database name

1. In phpMyAdmin, select your schema from the left sidebar—the name is your **`DB_DATABASE`**.
2. Say aloud: *“Laravel connects through `config/database.php` using the `mysql` driver and the credentials in `.env`.”*

---

## Key tables (say 5–8)

| Table | One-line purpose |
|-------|------------------|
| `users` | Staff accounts; linked to roles/permissions |
| `patients` | Patient master records for POS |
| `products` | Drug / SKU catalog |
| `inventory_batches` | Lot-level quantity, expiry, cost |
| `stock_movements` | Every in/out/adjustment; references POs, sales, etc. |
| `purchase_orders` | PO header and status (`pending` → `approved` → `received`) |
| `sales` | Sale header (totals, patient link) |
| `audit_logs` | Who changed what (high-level audit trail) |

Optional extras if asked: `sale_line_items`, `prescriptions`, `stock_requests`, `pre_orders`.

---

## Sample SELECT (prove data is stored and retrieved)

**Latest purchase order (header):**

```sql
SELECT id, po_number, status, total_cost, created_at
FROM purchase_orders
ORDER BY id DESC
LIMIT 5;
```

**After your live demo (receive a PO), show new stock tied to that PO:**

```sql
SELECT sm.id, sm.type, sm.quantity, sm.reference_type, sm.reference_id, sm.moved_at, ib.batch_number
FROM stock_movements sm
LEFT JOIN inventory_batches ib ON ib.id = sm.inventory_batch_id
WHERE sm.reference_type = 'App\\Models\\PurchaseOrder'
ORDER BY sm.id DESC
LIMIT 10;
```

**Tip:** If `reference_type` is stored without backslashes in your DB, use:

```sql
WHERE sm.reference_type LIKE '%PurchaseOrder%'
```

Adjust the `LIKE` pattern to match what you actually see in one sample row.

---

## Before you present

- [ ] Confirm `DB_DATABASE` in `.env` matches the schema you open in phpMyAdmin.
- [ ] Run each SELECT once so the result grid is not empty (seed or manual test data).
- [ ] Know which **user role** you will use for approve vs receive (may be two accounts).
