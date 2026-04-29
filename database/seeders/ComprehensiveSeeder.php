<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\InventoryLocation;
use App\Models\Patient;
use App\Models\PreOrder;
use App\Models\PreOrderItem;
use App\Models\Prescriber;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RxItem;
use App\Models\Sale;
use App\Models\SaleLineItem;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding comprehensive data for 1 year...');

        $admin = User::where('email', 'admin@pharmacy.local')->first();
        $pharmacist = User::where('email', 'pharmacist@pharmacy.local')->first();
        $staff = User::where('email', 'staff@pharmacy.local')->first();

        $users = [$admin, $pharmacist, $staff];
        $locations = $this->seedInventoryLocations();
        $products = Product::with('inventoryBatches')->get();
        
        // Seed patients and prescribers
        $patients = $this->seedPatients();
        $prescribers = $this->seedPrescribers();
        
        // Seed prescriptions
        $prescriptions = $this->seedPrescriptions($patients, $prescribers);
        
        // Seed sales over 12 months
        $this->seedSales($users, $products, $patients, $prescriptions);
        
        // Seed purchase orders
        $this->seedPurchaseOrders($users, $products);
        
        // Seed stock movements
        $this->seedStockMovements($users, $products);
        
        // Seed stock requests
        $this->seedStockRequests($users, $products);
        
        // Seed pre-orders
        $this->seedPreOrders($users, $products);
        
        // Seed audit logs
        $this->seedAuditLogs($users);
        
        $this->command->info('Comprehensive seeding complete!');
    }

    protected function seedInventoryLocations(): array
    {
        $locationsData = [
            ['name' => 'Main Shelf A1', 'code' => 'SHELF-A1', 'is_front_shop' => true],
            ['name' => 'Main Shelf A2', 'code' => 'SHELF-A2', 'is_front_shop' => true],
            ['name' => 'Main Shelf B1', 'code' => 'SHELF-B1', 'is_front_shop' => true],
            ['name' => 'Main Shelf B2', 'code' => 'SHELF-B2', 'is_front_shop' => true],
            ['name' => 'Cold Storage', 'code' => 'COLD-01', 'is_front_shop' => false],
            ['name' => 'Controlled Cabinet', 'code' => 'CTRL-01', 'is_front_shop' => false],
            ['name' => 'Back Storage', 'code' => 'BACK-01', 'is_front_shop' => false],
            ['name' => 'Receiving Area', 'code' => 'RECV-01', 'is_front_shop' => false],
        ];

        $locations = [];
        foreach ($locationsData as $loc) {
            $locations[] = InventoryLocation::updateOrCreate(
                ['code' => $loc['code']],
                $loc
            );
        }
        return $locations;
    }

    protected function seedPatients(): array
    {
        $patientData = [
            ['name' => 'John Smith', 'birthdate' => '1985-03-15', 'contact_info' => '555-0101|123 Main St', 'allergies' => 'None'],
            ['name' => 'Mary Johnson', 'birthdate' => '1990-07-22', 'contact_info' => '555-0102|456 Oak Ave', 'allergies' => 'Penicillin'],
            ['name' => 'Robert Williams', 'birthdate' => '1978-11-08', 'contact_info' => '555-0103|789 Pine Rd', 'allergies' => 'None'],
            ['name' => 'Patricia Brown', 'birthdate' => '1995-01-30', 'contact_info' => '555-0104|321 Elm St', 'allergies' => 'Sulfa'],
            ['name' => 'Michael Davis', 'birthdate' => '1982-09-12', 'contact_info' => '555-0105|654 Maple Dr', 'allergies' => 'None'],
            ['name' => 'Linda Miller', 'birthdate' => '1988-05-25', 'contact_info' => '555-0106|987 Cedar Ln', 'allergies' => 'Aspirin'],
            ['name' => 'James Wilson', 'birthdate' => '1975-12-03', 'contact_info' => '555-0107|147 Birch Ct', 'allergies' => 'None'],
            ['name' => 'Barbara Moore', 'birthdate' => '1992-08-17', 'contact_info' => '555-0108|258 Walnut Way', 'allergies' => 'None'],
            ['name' => 'David Taylor', 'birthdate' => '1980-04-09', 'contact_info' => '555-0109|369 Spruce St', 'allergies' => 'Ibuprofen'],
            ['name' => 'Susan Anderson', 'birthdate' => '1987-06-28', 'contact_info' => '555-0110|741 Ash Ave', 'allergies' => 'None'],
            ['name' => 'Richard Thomas', 'birthdate' => '1972-10-14', 'contact_info' => '555-0111|852 Cherry Rd', 'allergies' => 'None'],
            ['name' => 'Jessica Martinez', 'birthdate' => '1998-02-05', 'contact_info' => '555-0112|963 Poplar Dr', 'allergies' => 'Codeine'],
            ['name' => 'Charles Garcia', 'birthdate' => '1983-07-19', 'contact_info' => '555-0113|159 Hickory Ln', 'allergies' => 'None'],
            ['name' => 'Nancy Rodriguez', 'birthdate' => '1991-11-23', 'contact_info' => '555-0114|357 Sycamore Ct', 'allergies' => 'None'],
            ['name' => 'Joseph Lee', 'birthdate' => '1977-03-11', 'contact_info' => '555-0115|468 Beech Way', 'allergies' => 'Penicillin'],
        ];

        $patients = [];
        foreach ($patientData as $data) {
            $patients[] = Patient::updateOrCreate(
                ['name' => $data['name'], 'birthdate' => $data['birthdate']],
                $data
            );
        }
        return $patients;
    }

    protected function seedPrescribers(): array
    {
        $prescriberData = [
            ['name' => 'Dr. Sarah Chen', 'license_number' => 'MD-12345', 'contact_info' => '555-1001|General Practice'],
            ['name' => 'Dr. Mark Thompson', 'license_number' => 'MD-23456', 'contact_info' => '555-1002|Internal Medicine'],
            ['name' => 'Dr. Emily White', 'license_number' => 'MD-34567', 'contact_info' => '555-1003|Pediatrics'],
            ['name' => 'Dr. Robert Harris', 'license_number' => 'MD-45678', 'contact_info' => '555-1004|Cardiology'],
            ['name' => 'Dr. Lisa Clark', 'license_number' => 'MD-56789', 'contact_info' => '555-1005|Dermatology'],
            ['name' => 'Dr. John Lewis', 'license_number' => 'MD-67890', 'contact_info' => '555-1006|Orthopedics'],
            ['name' => 'Dr. Amanda Walker', 'license_number' => 'MD-78901', 'contact_info' => '555-1007|Neurology'],
            ['name' => 'Dr. Kevin Hall', 'license_number' => 'MD-89012', 'contact_info' => '555-1008|Psychiatry'],
        ];

        $prescribers = [];
        foreach ($prescriberData as $data) {
            $prescribers[] = Prescriber::updateOrCreate(
                ['license_number' => $data['license_number']],
                $data
            );
        }
        return $prescribers;
    }

    protected function seedPrescriptions(array $patients, array $prescribers): array
    {
        $prescriptions = [];
        $statuses = ['active', 'completed', 'cancelled'];
        
        // Create 30 prescriptions spread over the past year
        for ($i = 0; $i < 30; $i++) {
            $createdAt = now()->subDays(rand(1, 365));
            $prescription = Prescription::create([
                'patient_id' => $patients[array_rand($patients)]->id,
                'prescriber_id' => $prescribers[array_rand($prescribers)]->id,
                'issued_date' => $createdAt->toDateString(),
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $prescriptions[] = $prescription;
        }
        
        return $prescriptions;
    }

    protected function seedSales(array $users, $products, array $patients, array $prescriptions): void
    {
        $paymentMethods = ['cash', 'card', 'insurance'];
        
        // Create sales over 12 months (roughly 2-3 sales per day)
        for ($i = 0; $i < 600; $i++) {
            $createdAt = now()->subDays(rand(0, 365))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $patient = $patients[array_rand($patients)];
            $prescription = rand(0, 1) ? $prescriptions[array_rand($prescriptions)] : null;
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            
            // Generate random line items
            $lineItems = [];
            $totalAmount = 0;
            $numItems = rand(1, 4);
            
            for ($j = 0; $j < $numItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $lineTotal = $unitPrice * $quantity;
                $totalAmount += $lineTotal;
                
                $lineItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                ];
            }
            
            // Payment details
            $paymentTendered = $totalAmount;
            $paymentChangeDue = 0;
            $paymentReference = null;
            $insuranceProvider = null;
            $insurancePolicyNumber = null;
            $insuranceAuthorizationCode = null;
            
            if ($paymentMethod === 'cash') {
                $paymentTendered = $totalAmount + rand(0, 50);
                $paymentChangeDue = $paymentTendered - $totalAmount;
            } elseif ($paymentMethod === 'card') {
                $paymentReference = 'TXN-' . strtoupper(Str::random(8));
            } else {
                $insuranceProvider = ['BlueCross', 'Aetna', 'UnitedHealth', 'Cigna'][array_rand(['BlueCross', 'Aetna', 'UnitedHealth', 'Cigna'])];
                $insurancePolicyNumber = 'POL-' . rand(100000, 999999);
                $insuranceAuthorizationCode = 'AUTH-' . rand(1000, 9999);
            }
            
            $sale = Sale::create([
                'user_id' => $users[array_rand($users)]->id,
                'patient_id' => $patient->id,
                'prescription_id' => $prescription?->id,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_tendered' => $paymentTendered,
                'payment_change_due' => $paymentChangeDue,
                'payment_reference' => $paymentReference,
                'insurance_provider' => $insuranceProvider,
                'insurance_policy_number' => $insurancePolicyNumber,
                'insurance_authorization_code' => $insuranceAuthorizationCode,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            
            foreach ($lineItems as $item) {
                SaleLineItem::create([
                    'sale_id' => $sale->id,
                    'inventory_batch_id' => $product->inventoryBatches->first()?->id ?? 1,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }
    }

    protected function seedPurchaseOrders(array $users, $products): void
    {
        $statuses = ['pending', 'approved', 'rejected', 'received'];
        
        for ($i = 0; $i < 50; $i++) {
            $createdAt = now()->subDays(rand(1, 300));
            $status = $statuses[array_rand($statuses)];
            
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'created_by' => $users[array_rand($users)]->id,
                'approved_by' => $status !== 'pending' ? $users[array_rand($users)]->id : null,
                'status' => $status,
                'expected_date' => $createdAt->addDays(rand(7, 21))->toDateString(),
                'notes' => 'Bulk order for inventory replenishment',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            
            // Add 3-5 items per PO
            $selectedProducts = $products->random(rand(3, 5));
            $totalCost = 0;
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(50, 200);
                $costPrice = $product->inventoryBatches->first()?->cost_price ?? ($product->price * 0.6);
                $lineTotal = $quantity * $costPrice;
                $totalCost += $lineTotal;
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $costPrice,
                ]);
            }
            
            // Update PO with total cost
            $po->update(['total_cost' => $totalCost]);
        }
    }

    protected function seedStockMovements(array $users, $products): void
    {
        $types = ['incoming', 'release', 'adjustment'];
        
        for ($i = 0; $i < 200; $i++) {
            $createdAt = now()->subDays(rand(1, 300));
            $type = $types[array_rand($types)];
            $product = $products->random();
            $batch = $product->inventoryBatches->first();
            
            StockMovement::create([
                'product_id' => $product->id,
                'inventory_batch_id' => $batch?->id,
                'moved_by' => $users[array_rand($users)]->id,
                'type' => $type,
                'quantity' => rand(5, 50) * ($type === 'release' ? -1 : 1),
                'reference_type' => $type === 'incoming' ? 'PurchaseOrder' : ($type === 'release' ? 'Sale' : 'Adjustment'),
                'reference_id' => rand(1, 100),
                'notes' => 'Stock ' . strtolower($type) . ' operation',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    protected function seedStockRequests(array $users, $products): void
    {
        $statuses = ['pending', 'approved', 'rejected'];
        
        for ($i = 0; $i < 30; $i++) {
            $createdAt = now()->subDays(rand(1, 300));
            $status = $statuses[array_rand($statuses)];
            $product = $products->random();
            $requestedQty = rand(20, 100);
            
            StockRequest::create([
                'product_id' => $product->id,
                'requested_by' => $users[array_rand($users)]->id,
                'approved_by' => $status !== 'pending' ? $users[array_rand($users)]->id : null,
                'quantity' => $requestedQty,
                'status' => $status,
                'reason' => 'Request for ' . $product->name,
                'requested_quantity' => $requestedQty,
                'approved_quantity' => $status === 'approved' ? rand(15, $requestedQty) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    protected function seedPreOrders(array $users, $products): void
    {
        for ($i = 0; $i < 25; $i++) {
            $createdAt = now()->subDays(rand(1, 180));
            $status = ['pending', 'fulfilled', 'cancelled'][array_rand(['pending', 'fulfilled', 'cancelled'])];
            
            $preOrder = PreOrder::create([
                'customer_name' => 'Customer ' . ($i + 1),
                'payment_method' => ['cash', 'card', 'insurance'][array_rand(['cash', 'card', 'insurance'])],
                'scan_token' => 'SCAN-' . Str::random(12),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            
            // Add 1-3 items per pre-order
            $selectedProducts = $products->random(rand(1, 3));
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 5);
                $unitPrice = $product->price;
                $subtotal = $quantity * $unitPrice;
                
                PreOrderItem::create([
                    'pre_order_id' => $preOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }

    protected function seedAuditLogs(array $users): void
    {
        // Audit logs use morphs - skip for now as it requires proper auditable relationships
        // Can be added later with proper polymorphic relationships
    }
}