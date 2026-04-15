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

This resets the database to clean demo state.</content>
<parameter name="filePath">c:\Users\gabgab8608\pharmacy\USER_MANUAL.md
