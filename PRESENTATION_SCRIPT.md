# Final presentation script (~5 minutes)

Open **architecture-flow.html** in your browser (double-click). It opens on the **Overview** tab (database + data path); other tabs are the detailed SVG flows. Keep **phpMyAdmin** and **`php artisan serve`** tabs ready. Replace `(your-url)` with your local app URL.

**Tab names in the HTML (exact labels):** Overview | POS sale | Purchase order | Stock request | Kiosk / pre-order | Inventory core | Admin

---

## 1. Introduction (~30 seconds)

- **Title:** Pharmacy management system (Laravel web app).
- **Purpose:** Support daily pharmacy operations—inventory, procurement, point-of-sale, prescriptions, and audit visibility.
- **Target users:** Pharmacists (sales and clinical workflows), staff (stock and purchase orders), administrators (oversight, reports, protected adjustments).

---

## 2. Database connection (~45 seconds)

- Switch to **phpMyAdmin** (or MySQL Workbench).
- Say: *“The app uses MySQL via Laravel’s `DB_*` settings in `.env`. My database is named **[say your `DB_DATABASE` value]**.”*
- **Key tables (name 5–8):** `users`, `patients`, `products`, `inventory_batches`, `stock_movements`, `purchase_orders`, `sales`, `audit_logs` (add `sale_line_items` or `prescriptions` if you have time).
- **Prove read/write:** Run the sample **SELECT** from [PRESENTATION_PHPMYADMIN.md](PRESENTATION_PHPMYADMIN.md) (latest purchase order or latest stock movement). Then say you will create or update related data in the app and refresh the query in the demo segment.

---

## 3. System process flow (~1 minute 30 seconds)

- Return to **architecture-flow.html**.
- Click tab **Overview** (if you added it): point at **Input → Laravel → MySQL → Output** in one sentence each.
- Click tab **Purchase order** (primary depth): walk the diagram top to bottom—*staff creates PO → pending → approver approves → receiving posts batches and stock movements → status received*.
- Optionally click **POS sale** for breadth: *login → patient → cart → sale lines → inventory deduction / Rx linkage*.
- Tie one box on the diagram to the database: *“This step persists rows in `purchase_orders` and later `inventory_batches` and `stock_movements`.”*

---

## 4. Key features (~1 minute)

Pick **3–4** (functionality only, not “we have CRUD”):

1. **FEFO / location-aware stock** for dispensing so releases use the right batches and shop locations.
2. **Approval gates** (e.g. purchase orders, stock requests) so sensitive actions are role-controlled.
3. **Audit trail** (`audit_logs` and movement history) for accountability.
4. **Public kiosk / pre-order** path separate from authenticated staff flows.

---

## 5. Live demo (~1 minute)

**Path (matches Purchase order tab in the HTML):**

1. Browser: `(your-url)` — log in as a user who can **view** and **approve** purchase orders (and **receive** if you demo receiving).
2. **Purchase Orders** → open a **pending** PO or create one (if your role can create).
3. **Approve** the PO (button on index or your UI path).
4. **Receive delivery** → submit receive form (batch prefix, dates). Confirm success message on PO detail.
5. **phpMyAdmin:** Re-run the same **SELECT** from [PRESENTATION_PHPMYADMIN.md](PRESENTATION_PHPMYADMIN.md) (or browse `stock_movements` filtered by `reference_type` = `App\\Models\\PurchaseOrder` if you prefer). Point at the **new or updated** rows.

If time is short, stop after showing **one** new `inventory_batches` row or **one** incoming `stock_movements` row.

---

## 6. Challenges and conclusion (~30 seconds)

**Example challenges (pick 1–2):**

- **Redirect / session after multi-step flows:** Ensuring success feedback matches the real DB state after POST (e.g. receive then navigate to detail instead of reloading an invalid step).
- **SQL differences** between local SQLite (tests) and MySQL (production)—views or syntax had to stay portable.

**Closing line:** *“The system keeps operations in the browser, business rules in Laravel, and the authoritative record in MySQL—with diagrams in architecture-flow.html matching what we just ran live.”*

---

## Quick timing checklist

| Segment        | ~Time | Asset / action                                      |
|----------------|-------|-----------------------------------------------------|
| Intro          | 30s   | Speak                                               |
| Database       | 45s   | phpMyAdmin + SELECT from cheatsheet                 |
| Process flow   | 90s   | architecture-flow.html → Overview + Purchase order  |
| Key features   | 60s   | Speak (3–4 bullets)                                 |
| Live demo      | 60s   | Laravel app → PO approve/receive → phpMyAdmin       |
| Challenges     | 30s   | Speak                                               |
