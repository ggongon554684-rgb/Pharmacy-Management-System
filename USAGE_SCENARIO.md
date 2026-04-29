# Pharmacy Management System - Usage Scenario

## Task Analysis: POS (Point of Sale) Transaction Flow

### Step 1: Login and Dashboard Access

**User Action:**
- User navigates to `/login` page
- Enters email and password credentials
- Clicks "Login" button

**System Response:**
1. **Validation Phase:**
   - Laravel's `Auth::attempt()` validates credentials against hashed passwords in users table
   - Session is regenerated for security (`regenerate()`)
   - Role-based middleware redirects to appropriate dashboard

2. **Database Updates:**
   - User session record created in `sessions` table
   - Last login timestamp updated in `users` table

3. **Notifications:**
   - Success: Redirect to role-specific dashboard (`/dashboard`)
   - Failure: Redirect back with error message "These credentials do not match our records"

**Laravel Features Used:**
- `Auth` facade for authentication
- Session middleware for state management
- Role-based middleware (`RedirectIfAuthenticated`)

---

### Step 2: Accessing the POS Transaction Page

**User Action:**
- From dashboard, pharmacist clicks "New Sale" or "POS" menu item
- Navigates to `/sales/create`

**System Response:**
1. **Data Loading:**
   - SalesController::create() queries available patients, products, and active prescriptions
   - Products loaded with computed sellable_stock, front_stock, and back_stock via Eloquent subqueries
   - Stock levels calculated using `releasable()` and `forLocationCode()` scopes

2. **Page Render:**
   - Blade view `sales.create` rendered with compact data
   - Product search autocomplete initialized with first 500 products

3. **Notifications:**
   - Page loads with available inventory displayed per product

**Laravel Features Used:**
- Eloquent relationships with aggregate queries
- Blade templates with `@compact()`
- Query scopes for stock filtering

---

### Step 3: Entering Transaction Data

**User Action:**
- **Patient Selection:** Choose "Existing Patient" or "New Patient" mode
  - If existing: Select patient from dropdown
  - If new: Enter name, birthdate, contact info, allergies
- **Product Selection:** Search and add products to sale
  - System displays real-time stock availability per product
  - Enter quantity for each product
- **Prescription Linking (Optional):** Select active prescription to link
- **Payment Method:** Choose cash, card, or insurance
  - If cash: Enter amount tendered
  - If card: Enter reference number
  - If insurance: Enter provider, policy number, authorization code

**System Response:**
1. **Client-Side Validation (JavaScript):**
   - Quantity must be positive integer
   - Product must have available stock
   - Payment fields required based on method selected

2. **Real-Time Stock Check:**
   - AJAX validates stock availability before adding to cart
   - Displays "Insufficient stock" warning if quantity exceeds available

3. **Form Display:**
   - Dynamic form fields show/hide based on payment method selection
   - Prescription linking only available for existing patients

**Laravel Features Used:**
- Blade conditional directives (`@if`, `@show`)
- JavaScript validation with Laravel validation rules exposed via JSON
- Dynamic form partials for payment method fields

---

### Step 4: Submitting the Transaction

**User Action:**
- Review transaction summary
- Click "Complete Sale" button

**System Response:**
1. **Server-Side Validation (Pre-Transaction):**
   ```
   Validation Rules Applied:
   - patient_mode: required|in:existing,new
   - patient_id: nullable|exists:patients,id (if existing)
   - patient_name/birthdate/contact: required (if new patient)
   - prescription_id: nullable|exists:prescriptions,id
   - payment_method: required|in:cash,card,insurance
   - product_ids: required|array|min:1
   - product_ids.*: required|exists:products,id
   - quantities: required|array|min:1
   - quantities.*: required|integer|min:1
   ```

2. **Business Logic Validation:**
   - New patient requires name, birthdate, and contact info
   - Prescription can only link to existing patient
   - Card payments require reference number
   - Insurance payments require provider and policy number
   - Active prescriptions only (status check)

3. **Payment Validation:**
   - Cash: tendered amount must be >= total
   - Card: tendered amount must be >= total
   - Insurance: covered externally (tendered = 0)

**Database Updates (Inside Transaction):**

```
DB::transaction(function() {
    // Step A: Stock Release (FEFO - First Expired First Out)
    - InventoryReleaseService::releaseProduct() called
    - SELECT ... FOR UPDATE locks relevant batches
    - Atomic conditional UPDATE: quantity = quantity - deduct
    - Verifies affected row count (prevents negative stock)
    
    // Step B: Patient Creation (if new patient)
    - INSERT into patients table
    - Returns patient_id for sale record
    
    // Step C: Prescription Validation (if linked)
    - Verify prescription belongs to selected patient
    - Check remaining quantity by product
    - Block or warn based on rx.dispense_enforcement config
    
    // Step D: Sale Record Creation
    - INSERT into sales table with total_amount, payment details
    
    // Step E: Line Items Creation
    - INSERT into sale_line_items (one per batch used)
    - Each links to inventory_batch_id
    
    // Step F: Stock Movements
    - INSERT into stock_movements (type: 'release')
    - Records product_id, batch_id, quantity, reference
    
    // Step G: Audit Log
    - INSERT into audit_logs (action: 'sale_created')
    - Records old_values: null, new_values: sale data
})
```

4. **Error Handling:**
   - InsufficientStockException: Rollback, redirect with error
   - ValidationException: Rollback, redirect with errors
   - Concurrent transaction conflict: Retry prompt

**Notifications:**
- Success: `redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock released using FEFO.')`
- Failure: `back()->withErrors([...])->withInput()`

**Laravel Features Used:**
- `DB::transaction()` for atomic operations
- `lockForUpdate()` for row-level locking
- `ValidationException` for form validation errors
- `InsufficientStockException` custom exception
- Eloquent model events for audit logging
- `AuditLog` model for compliance tracking

---

### Step 5: Receiving Confirmation

**User Action:**
- View sale receipt/confirmation page

**System Response:**
1. **Receipt Display:**
   - Sale details loaded with patient, line items, payment info
   - Inventory batches shown with batch numbers and expiry dates
   - Total amount, payment method, change due (if cash)

2. **Print Option:**
   - "Print Receipt" button available
   - Optional: `?print=1` query parameter for direct print

3. **Stock Updated:**
   - Front inventory quantity reduced
   - Stock movements recorded for audit trail
   - Audit log entry created

**Notifications:**
- Success message displayed at top: "Sale recorded and stock released using FEFO."

**Laravel Features Used:**
- Blade view `sales.show` with loaded relationships
- Route model binding for Sale $sale
- Eloquent lazy loading for related data

---

## Alternative Transaction Flows

### Kiosk Self-Service Order (No Authentication)

**Step 1:** Customer accesses `/kiosk-order` (public route)
- No login required
- Product search and selection interface

**Step 2:** Customer submits order
- Customer name (optional), payment method required
- Product IDs and quantities array

**Step 3:** System creates PreOrder
- Generates unique scan_token (10-character alphanumeric)
- Creates PreOrderItem records with pricing

**Step 4:** Ticket generation
- Redirects to `/kiosk-order/ticket/{id}`
- Displays order summary with QR code
- Generates 24-hour signed URL for fulfillment

**Step 5:** Staff fulfillment
- Staff scans QR code or visits signed URL
- PreOrder scanned status updated
- Redirects to sales/create with pre-filled items
- Completes POS transaction

---

### Stock Request (Internal Transfer)

**Step 1:** Staff creates stock request
- Navigate to `/stock-requests/create`
- Select product, enter requested quantity

**Step 2:** System validates
- Checks back inventory availability
- Creates pending StockRequest record

**Step 3:** Admin approval
- Admin reviews at `/stock-requests`
- Approves with optional quantity adjustment
- Inventory released from back to front location

**Step 4:** Stock movement recorded
- StockMovement records created for audit trail

---

## System Response Summary Table

| Transaction Phase | Validation | Database Updates | Notifications |
|-------------------|------------|-------------------|---------------|
| Login | Email/password check | Session, user last_login | Redirect to dashboard |
| POS Create | - | Load patients/products/prescriptions | Display form |
| POS Submit | Form rules, business logic | Sale, line items, stock movements, audit log | Success/retry message |
| Kiosk Order | Product availability | PreOrder, PreOrderItems | Ticket with QR |
| Stock Request | Back inventory check | StockRequest, StockMovement | Pending approval |

---

## Scenario: A Day in the Life of a Community Pharmacy

### Setting

Sunnyvale Community Pharmacy is a busy retail pharmacy serving 200+ customers daily. They use the Pharmacy Management System to handle prescriptions, OTC sales, inventory management, and regulatory compliance.

### Morning Routine (8:00 AM - 9:00 AM)

**Pharmacist Maria arrives and logs in as "pharmacist" role.**

1. **Check Inventory Levels**
    - Maria reviews the dashboard showing current front-shop stock
    - System alerts show products below reorder level: Amoxicillin, Ibuprofen
    - She creates stock requests for these items to replenish from back inventory

2. **Review Patient Records**
    - Checks for patients with upcoming prescription refills
    - Reviews allergy information for scheduled consultations

### Mid-Morning Operations (9:00 AM - 12:00 PM)

**Staff member John handles inventory and ordering.**

1. **Process Stock Requests**
    - John receives Maria's stock requests
    - Reviews current back inventory levels
    - Uses FEFO (First Expired First Out) to transfer non-expired batches from back to front
    - System automatically creates stock movement records

2. **Manage Purchase Orders**
    - Identifies products running low across all locations
    - Creates purchase order for 50 units of Amoxicillin, 100 units of Ibuprofen
    - Submits for admin approval

**Admin Sarah reviews and approves orders.**

1. **Approve Purchase Orders**
    - Sarah logs in with admin credentials
    - Reviews pending purchase orders
    - Checks budget and supplier terms
    - Approves John's purchase order
    - Monitors financial dashboard for revenue tracking

### Customer Service Hours (12:00 PM - 4:00 PM)

**Peak customer service time.**

1. **Walk-in Customer - OTC Purchase**
    - Customer approaches counter requesting pain relief medication
    - Maria selects patient record (or creates new one)
    - Adds Ibuprofen to sale, system shows available stock
    - Completes transaction, stock automatically deducted from front inventory

2. **Prescription Processing**
    - Customer presents prescription for antibiotics
    - Maria verifies prescriber information
    - Links prescription to patient record
    - Dispenses medication, system tracks prescription status
    - Generates receipt with medication instructions

3. **Kiosk Order Processing**
    - Customer uses self-service kiosk to order vitamins
    - Receives QR code ticket
    - Maria scans QR code, system creates sale record
    - Processes payment and dispenses products

### Afternoon Operations (4:00 PM - 6:00 PM)

**Receiving Deliveries**

1. **Delivery Arrival**
    - Supplier delivers approved purchase order
    - John receives delivery, verifies quantities and expiry dates
    - System creates inventory batches with cost tracking
    - Generates stock movement records for audit trail

2. **Inventory Reconciliation**
    - John reviews stock movements for the day
    - Compares physical counts with system records
    - Addresses any discrepancies through audit logs

### End of Day (6:00 PM - 7:00 PM)

**Closing Procedures**

1. **Generate Reports**
    - Maria runs daily sales report
    - Reviews inventory status report
    - Checks patient purchase history for high-volume customers

2. **Audit Review**
    - Sarah reviews audit logs for the day
    - Verifies all stock movements are accounted for
    - Checks for any admin overrides or unusual activities

3. **System Maintenance**
    - All users log out
    - System automatically backs up transaction data
    - Prepares for next day's operations

### Emergency Scenario: Stock Shortage

**During peak hours, system alerts insufficient stock for high-demand medication.**

1. **Alert Trigger**
    - Customer requests medication with only 2 units in front stock
    - System prevents sale and alerts pharmacist

2. **Quick Resolution**
    - Maria creates urgent stock request
    - John immediately fulfills from back inventory
    - System uses FEFO to ensure oldest non-expired stock is used
    - Sale completed without delay

### Compliance and Regulatory Benefits

Throughout the day, the system ensures:

- All prescriptions are properly documented and tracked
- Inventory movements follow regulatory guidelines
- Audit trails provide complete transaction history
- Patient privacy is maintained through role-based access
- Financial records are accurate for insurance billing

### Business Impact

By using this system, Sunnyvale Pharmacy:

- Reduces inventory carrying costs through precise stock management
- Minimizes expired medication waste with FEFO tracking
- Improves customer service with real-time stock visibility
- Ensures regulatory compliance with comprehensive audit trails
- Increases operational efficiency through automated workflows
- Provides data-driven insights for business decisions

This scenario demonstrates how the Pharmacy Management System transforms daily pharmacy operations from manual, error-prone processes to efficient, compliant, and customer-focused workflows.</content>
<parameter name="filePath">c:\Users\gabgab8608\pharmacy\USAGE_SCENARIO.md
