<!-- 
  Save as README.md – GitHub, GitLab, etc. will render the HTML/CSS inside.
  For local preview, use any Markdown viewer that supports raw HTML.
-->


# 💊 Pharmacy Management System

> **Secure · Role‑based · Immutable audit trail**  
> Tracks patients, inventory batches, purchase orders, stock movements, and sales.

---

## 📋 System Overview

This application handles core pharmacy operations with **strict role permissions** and a **complete audit log**. Every stock change is recorded as an immutable `StockMovement` and sensitive actions are snapshotted in `AuditLog`.

### 🧩 Core domains

| Domain | Purpose |
|--------|---------|
| 👥 Patient Management | Profiles + purchase histories |
| 📦 Inventory Management | Product + batch‑level stock control |
| 🛒 Purchase Orders & Receiving | Incoming stock from suppliers |
| 📤 Stock Requests & Fulfillment | Moving stock to front‑shop (FIFO) |
| 📊 Stock Movement Tracking | Immutable ledger of all changes |
| 🔐 Audit Trail | Snapshots of who did what & before/after values |
| 🛡️ Role‑Based Access (RBAC) | Spatie permissions applied via middleware |

---

## 👤 Role‑Based Access Control

Users are routed to role‑specific dashboards after login. Permissions are enforced at the route level (e.g., `can:view_reports`).

<div class="grid-3">
  <div class="role-card admin">
    <strong>🧑‍💼 Admin</strong><br>
    Approve purchase orders, view deliveries, PIN‑protected stock overrides, view audit trails, oversee patient records.
  </div>
  <div class="role-card pharmacist">
    <strong>🧑‍⚕️ Pharmacist</strong><br>
    Manage patients, handle sales, create front‑shop stock requests, view patient purchase reports.
  </div>
  <div class="role-card staff">
    <strong>🧑‍💻 Staff</strong><br>
    Manage products, create/receive purchase orders, approve/fulfill stock requests (FIFO), view stock movement reports.
  </div>
</div>

---

## 🔄 Core Workflows (visual steps)

<div class="workflow-step">
  <span class="flow-icon">📦</span> <strong>A. Inventory Receive (PO → Stock)</strong><br>
  1️⃣ Staff creates Purchase Order (PO) &nbsp;→&nbsp; 
  2️⃣ Admin approves PO &nbsp;→&nbsp; 
  3️⃣ Staff receives delivery → auto‑creates batches, logs incoming movement, marks PO received, writes audit log.
</div>

<div class="workflow-step">
  <span class="flow-icon">🏪</span> <strong>B. Front‑Shop Shortage (Stock Request)</strong><br>
  1️⃣ Pharmacist creates stock request &nbsp;→&nbsp; 
  2️⃣ Staff approves & fulfills → checks available non‑expired stock, deducts using <strong>FIFO</strong>, logs release movement, marks request fulfilled.
</div>

<div class="workflow-step">
  <span class="flow-icon">🔒</span> <strong>C. Admin Stock Override</strong><br>
  1️⃣ Admin selects product batch, submits override quantity &nbsp;→&nbsp; 
  2️⃣ System asks for Secure PIN &nbsp;→&nbsp; 
  3️⃣ Valid PIN → batch updated, adjustment movement logged, audit log captures before/after.
</div>

<div class="workflow-step">
  <span class="flow-icon">👤</span> <strong>D. Patient & Purchase History</strong><br>
  Pharmacist manages patient profiles, logs sales → sales linked via <code>Sale</code> + <code>SaleLineItem</code> → system generates patient purchase reports.
</div>

---

## 🏗️ High‑Level Architecture (MVC)

The system follows a strict MVC pattern to separate concerns and enforce business logic.

| Layer | Key components |
|-------|----------------|
| **Routes** | `routes/web.php` – middleware `can:permission` for entry‑point control |
| **Controllers** | `PatientController`, `InventoryBatchController`, `StockRequestController`, `PurchaseOrderController`, `AdminStockOverrideController`, `ReportController`, `AuditLogController`, `DashboardController` |
| **Models** | `Product`, `InventoryBatch`, `StockMovement` (immutable), `Patient`, `Sale`, `PurchaseOrder`, `StockRequest`, `AuditLog`, `User` (with Spatie roles) |
| **Migrations** | Foreign keys + constraints for data integrity – products, batches, sales, audit logs, stock movements, purchase orders, stock requests |
| **Seeders** | `RolesAndPermissionSeeder`, `UserSeeder`, `InventorySeeder` – run via `DatabaseSeeder.php` |
| **Views** | Blade + Bootstrap – `patients/`, `products/`, `purchase-orders/`, `stock-requests/`, `reports/`, `audit-logs/`, `stock-movements/` |

### 🔀 Data request lifecycle

```text
[User clicks] → [Route + Middleware] → [Controller validation] 
    → [Model update] → [StockMovement / AuditLog recorded] 
    → [Redirect with flash message] → [Blade view renders]