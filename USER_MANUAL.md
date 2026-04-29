# Pharmacy Management System User Manual

## Overview

The Pharmacy Management System is a comprehensive web-based application built with Laravel that streamlines pharmacy operations through role-based access control and automated inventory management. The system focuses on inventory flow, patient records, purchase orders, stock movements, sales/POS functionality, and audit logging.

## System Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or SQLite database

### Installation Steps

1. **Install Dependencies**

    ```bash
    composer install
    npm install
    ```

2. **Configure Environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Configure your database settings in `.env`

3. **Database Setup**

    ```bash
    php artisan migrate --seed
    ```

4. **Start the Application**
    ```bash
    php artisan serve
    npm run dev
    ```

### Default User Accounts

After seeding, use these credentials:

- **Admin**: admin@pharmacy.com / password
- **Pharmacist**: pharmacist@pharmacy.com / password
- **Staff**: staff@pharmacy.com / password

## User Roles and Responsibilities

### Admin

- Approve purchase orders
- View incoming deliveries and stock movements
- Perform stock overrides (with PIN verification)
- View patients and audit logs
- Access financial dashboard (revenue, costs, estimates)

### Pharmacist

- Manage patient records
- Record sales through POS system
- Create stock requests for low front-shop inventory
- View patient-related reports
- Process prescriptions

### Staff

- Manage products and inventory
- Create purchase orders
- Receive incoming deliveries
- Approve and fulfill stock requests
- View reports and stock movements

## Core Workflows

### 1. Inventory Management Workflow

#### Adding Products

1. Navigate to Products → Create Product
2. Enter product details: name, generic name, SKU, price, reorder level
3. Save the product

#### Purchase Order Process

1. **Staff**: Create purchase order for needed products
2. **Admin**: Review and approve the purchase order
3. **Staff**: Receive delivery, creating inventory batches with expiry dates and costs

### 2. Stock Management Workflow

#### Stock Request Process

1. **Pharmacist**: Check front-shop stock levels
2. **Pharmacist**: Create stock request for products needed in front shop
3. **Staff**: Review request and current stock levels
4. **Staff**: Fulfill request by transferring stock from back inventory to front shop (FEFO method)

### 3. Sales/POS Workflow

#### Recording a Sale

1. **Pharmacist**: Go to Sales → Create Sale
2. Select patient (or create new patient)
3. Add products to sale (system shows available front-shop stock)
4. Optionally link to prescription
5. Select payment method
6. Complete sale (stock automatically deducted from front inventory)

### 4. Patient Management

#### Adding Patients

1. Navigate to Patients → Create Patient
2. Enter patient details: name, date of birth, contact information
3. Save patient record

#### Viewing Patient History

1. Go to Patients → View All Patients
2. Click on patient to see purchase history and linked prescriptions

### 5. Prescription Management

#### Creating Prescriptions

1. Navigate to Prescriptions → Create Prescription
2. Select patient and prescriber
3. Add prescription items with dosages and instructions
4. Link to specific products if available

### 6. Reporting

#### Available Reports

- **Inventory Report**: Current stock levels, batch details, expiry tracking
- **Patient Purchase Report**: Purchase history by patient
- **Prescription Report**: Active and filled prescriptions

Reports can be viewed on-screen or exported as PDF.

### 7. Audit and Compliance

#### Audit Logging

- All key actions are logged: sales, inventory changes, approvals
- Admin can view audit logs for compliance and troubleshooting
- Stock movements are tracked immutably

#### Stock Overrides

- Admin can override stock levels with PIN verification
- All overrides are audited for accountability

## Public Kiosk Feature

### Customer Self-Service

1. Customers can access `/kiosk-order` to place orders
2. Select products and quantities
3. Receive QR code ticket
4. Pharmacist scans QR code to process sale

## Key Features and Best Practices

### Inventory Control

- **Two-Tier System**: Separate front (sales) and back (reserve) inventory
- **FEFO Method**: First Expired First Out ensures product safety
- **Batch Tracking**: Each inventory batch tracked with expiry and cost
- **Reorder Alerts**: Automatic alerts when stock falls below reorder level

### Security and Access Control

- Role-based permissions prevent unauthorized access
- All actions logged for audit trails
- PIN verification for critical overrides

### Data Integrity

- Database transactions ensure data consistency
- Stock movements are immutable records
- Automatic stock validation prevents overselling

## Troubleshooting

### Common Issues

**Login Problems**

- Clear cache: `php artisan optimize:clear`
- Reseed users: `php artisan db:seed --class=UserSeeder`

**Stock Discrepancies**

- Check stock movements log
- Verify batch allocations
- Review audit logs for manual overrides

**Performance Issues**

- Run migrations: `php artisan migrate`
- Clear cache: `php artisan optimize:clear`
- Rebuild assets: `npm run build`

## Maintenance

### Regular Tasks

- Monitor expiry dates and reorder levels
- Review audit logs weekly
- Backup database regularly
- Update dependencies: `composer update`

### System Reset (Development)

```bash
php artisan migrate:fresh --seed
```

This resets the database to clean demo state.

---

## X. User Interface Manual

### Navigation Overview

| Menu            | Purpose                                 |
| --------------- | --------------------------------------- |
| Dashboard       | Role-specific overview (KPIs, alerts)   |
| Sales           | POS, create sale, view history          |
| Products        | Product list, add new, batch management |
| Patients        | Patient records, add new, history       |
| Prescriptions   | Create, view, link to sales             |
| Prescribers     | Doctor/prescriber management            |
| Purchase Orders | Create PO, receive delivery             |
| Stock Requests  | Request transfer, approve               |
| Audit Logs      | View all system activities              |
| Settings        | System configuration                    |

### Step-by-Step Guide

#### 1. Login and Dashboard Access

1. Open browser to application URL
2. Enter email and password
3. Click "Login" button
4. System redirects to role-specific dashboard

**Interface Components:**

- Login form with email, password fields
- Error notifications for invalid credentials

#### 2. Recording a Sale (POS)

1. Click **Sales** → **New Sale**
2. Select patient mode: "Existing" or "New"
3. Search and add products (quantity validated against stock)
4. Optionally link prescription (existing patients only)
5. Select payment method (cash/card/insurance)
6. Enter payment details based on method
7. Click **Complete Sale**
8. View receipt with print option

**Form Elements:**

- Patient selector dropdown
- Product search with stock display
- Quantity input (validated: positive integer)
- Payment method radio buttons

**Notifications:**

- Success: "Sale recorded and stock released using FEFO."
- Error: Inline validation messages below fields

#### 3. Creating a Purchase Order

1. Click **Purchase Orders** → **Create New**
2. Select products and quantities
3. Set expected delivery date
4. Add notes (optional)
5. Click **Submit Order**
6. Status: "pending" until admin approval

#### 4. Receiving Delivery

1. Click **Incoming Deliveries**
2. Select pending approved PO
3. Enter batch numbers and expiry dates
4. Verify quantities match delivery
5. Click **Receive Stock**
6. Inventory batches created automatically

#### 5. Stock Request (Front-Shop Replenishment)

1. Click **Stock Requests** → **Create New**
2. Select product needing replenishment
3. Enter quantity needed
4. Click **Submit Request**
5. Staff reviews and approves
6. Stock transferred using FEFO

#### 6. Kiosk Order Fulfillment

1. Customer places order at `/kiosk-order`
2. Customer receives QR code ticket
3. Pharmacist scans QR or visits link
4. System pre-fills sales/create
5. Complete normal sale process

### Key Interface Components

| Component     | Location       | Purpose                           |
| ------------- | -------------- | --------------------------------- |
| Sidebar       | Left           | Main navigation menu              |
| Top Bar       | Top            | User info, role indicator, logout |
| KPI Cards     | Dashboard      | Quick metrics display             |
| Data Tables   | All list views | Paginated data with search        |
| Form Buttons  | Forms          | Submit, cancel actions            |
| Alert Boxes   | Top of pages   | Success/error notifications       |
| Status Badges | Tables         | Visual status indicators          |

---

## XI. Feature Highlights

### Core Features

| Feature               | User Need                     | Workflow Enhancement                    |
| --------------------- | ----------------------------- | --------------------------------------- |
| Role-Based Dashboard  | Quick overview of daily tasks | KPIs and alerts in one view             |
| POS System            | Fast, accurate sales          | Real-time stock validation              |
| Inventory Management  | Track stock by batch/expiry   | FEFO prevents expired stock             |
| Purchase Orders       | Restock efficiently           | Approval workflow ensures authorization |
| Prescription Tracking | Link Rx to dispensing         | Patient safety with quantity limits     |
| Audit Logging         | Compliance and accountability | Complete action history                 |
| Kiosk Ordering        | Self-service option           | Reduces counter congestion              |
| Stock Requests        | Internal transfers            | Front/back inventory sync               |

### Laravel Features Applied

| Feature         | Implementation                                          |
| --------------- | ------------------------------------------------------- |
| Authentication  | `Auth` facade, session management, role-based redirects |
| Eloquent ORM    | Models with relationships and scopes                    |
| Blade Templates | Layouts, components, directives                         |
| Validation      | Form Request classes, controller validation             |
| Middleware      | `auth`, `role`, `can` for access control                |
| Database        | Transactions, migrations, seeders                       |

### Quick Reference

| Task          | Menu Path                | Key Form Fields               |
| ------------- | ------------------------ | ----------------------------- | ---------- |
| New Sale      | Sales → New Sale         | Patient, Products, Payment    |
| Add Patient   | Patients → Create        | Name, DOB, Contact, Allergies |
| Create PO     | Purchase Orders → Create | Products, Quantities, Date    |
| Receive Stock | Incoming → Receive       | Batch, Expiry, Qty            |
| Stock Request | Stock Requests → Create  | Product, Quantity             |
| View Reports  | Dashboard                | Auto-populated KPIs           | </content> |

<parameter name="filePath">c:\Users\gabgab8608\pharmacy\USER_MANUAL.md
