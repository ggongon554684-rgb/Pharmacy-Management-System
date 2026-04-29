<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\InventoryBatch;
use App\Models\InventoryLocation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Sale;
use Database\Seeders\RolesAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_based_core_flow_staff_pharmacist_admin(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        $staff = User::factory()->create([
            'email' => 'flow-staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $pharmacist = User::factory()->create([
            'email' => 'flow-pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $admin = User::factory()->create([
            'email' => 'flow-admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Staff manages inventory and creates PO.
        $this->post('/login', [
            'email' => 'flow-staff@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('products.store'), [
            'name' => 'Flow Product',
            'generic_name' => 'Paracetamol',
            'sku' => 'FLOW-SKU-001',
            'price' => 15.75,
            'reorder_level' => 10,
        ])->assertRedirect();

        $product = Product::where('sku', 'FLOW-SKU-001')->firstOrFail();

        $this->post(route('products.batches.store', $product), [
            'batch_number' => 'FLOW-BATCH-001',
            'quantity' => 30,
            'cost_price' => 10.50,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ])->assertRedirect(route('products.show', $product));
        InventoryBatch::query()
            ->where('product_id', $product->id)
            ->where('batch_number', 'FLOW-BATCH-001')
            ->update(['location_id' => $frontLocation->id]);

        $this->post(route('purchase-orders.store'), [
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_cost' => 9.50,
            'expected_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Test PO',
            'delivery_cost' => 100,
            'insurance_cost' => 25,
            'other_cost' => 5,
        ])->assertRedirect(route('purchase-orders.index'));

        $po = \App\Models\PurchaseOrder::firstOrFail();

        $this->post('/logout')->assertRedirect(route('login'));

        // Pharmacist manages patients, sells medicine, and requests medicine.
        $this->post('/login', [
            'email' => 'flow-pharmacist@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('patients.store'), [
            'name' => 'Flow Patient',
            'birthdate' => '1991-01-10',
            'contact_info' => '09123456789',
            'allergies' => 'None',
        ])->assertRedirect(route('patients.index'));

        $this->assertDatabaseHas('patients', ['name' => 'Flow Patient']);

        $patient = \App\Models\Patient::where('name', 'Flow Patient')->firstOrFail();

        $this->post(route('sales.store'), [
            'patient_mode' => 'existing',
            'patient_id' => $patient->id,
            'payment_method' => 'cash',
            'payment_tendered' => 9999.99,
            'product_ids' => [$product->id],
            'quantities' => [2],
        ])->assertRedirect();

        $sale = \App\Models\Sale::latest('id')->firstOrFail();
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'patient_id' => $patient->id,
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('sale_line_items', [
            'sale_id' => $sale->id,
            'quantity' => 2,
        ]);

        $this->post(route('stock-requests.store'), [
            'product_id' => $product->id,
            'quantity' => 5,
            'reason' => 'Front shop out of stock',
        ])->assertRedirect(route('stock-requests.index'));

        $stockRequest = \App\Models\StockRequest::firstOrFail();

        $this->post('/logout')->assertRedirect(route('login'));

        // Admin approves PO.
        $this->post('/login', [
            'email' => 'flow-admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('purchase-orders.approve', $po))->assertRedirect();
        $this->post('/logout')->assertRedirect(route('login'));

        // Staff receives approved PO and approves+fulfills stock release.
        $this->post('/login', [
            'email' => 'flow-staff@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('purchase-orders.receive', $po), [
            'receive_date' => now()->toDateString(),
            'batch_number' => 'PO-RCV-001',
            'expiry_date' => now()->addMonths(8)->toDateString(),
        ])->assertRedirect();

        $this->post(route('stock-requests.approve', $stockRequest))
            ->assertRedirect();

        $this->post('/logout')->assertRedirect(route('login'));

        // Admin sees oversight screens.
        $this->post('/login', [
            'email' => 'flow-admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
        $this->get(route('stock-movements.index'))->assertOk();
        $this->get(route('audit-logs.index'))->assertOk();

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'batch_number' => 'FLOW-BATCH-001',
        ]);
        $this->assertDatabaseHas('stock_requests', [
            'id' => $stockRequest->id,
            'status' => 'fulfilled',
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'received',
            'total_cost' => 1080.00,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => 'App\Models\Patient',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sale_created',
            'auditable_type' => 'App\Models\Sale',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => 'App\Models\Product',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stock_received',
            'auditable_type' => 'App\Models\InventoryBatch',
        ]);

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_sale_can_be_linked_to_optional_prescription(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        $pharmacist = User::factory()->create([
            'email' => 'flow-pharmacist-rx@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $patient = \App\Models\Patient::create([
            'name' => 'Rx Patient',
            'birthdate' => '1992-05-10',
            'contact_info' => '09998887777',
            'allergies' => null,
        ]);

        $prescriber = \App\Models\Prescriber::create([
            'name' => 'Dr. Santos',
            'license_number' => 'LIC-2026-001',
            'contact_info' => 'clinic@example.com',
        ]);

        $prescription = \App\Models\Prescription::create([
            'patient_id' => $patient->id,
            'prescriber_id' => $prescriber->id,
            'issued_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $product = Product::create([
            'name' => 'Rx Product',
            'generic_name' => 'Amoxicillin',
            'sku' => 'RX-SKU-001',
            'price' => 12.00,
            'reorder_level' => 5,
        ]);

        \App\Models\InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'RX-BATCH-001',
            'quantity' => 20,
            'cost_price' => 7.00,
            'expiry_date' => now()->addMonths(4)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'flow-pharmacist-rx@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('sales.store'), [
            'patient_mode' => 'existing',
            'patient_id' => $patient->id,
            'prescription_id' => $prescription->id,
            'payment_method' => 'cash',
            'payment_tendered' => 9999.99,
            'product_ids' => [$product->id],
            'quantities' => [1],
        ])->assertRedirect();

        $sale = \App\Models\Sale::latest('id')->firstOrFail();
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'patient_id' => $patient->id,
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_sale_release_uses_fefo_and_accepts_cart_style_arrays(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        $pharmacist = User::factory()->create([
            'email' => 'fefo-pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $patient = Patient::create([
            'name' => 'FEFO Patient',
            'birthdate' => '1993-07-20',
            'contact_info' => '09112223344',
            'allergies' => null,
        ]);

        $product = Product::create([
            'name' => 'FEFO Product',
            'generic_name' => 'Cetirizine',
            'sku' => 'FEFO-SKU-001',
            'price' => 10.00,
            'reorder_level' => 2,
        ]);

        $earliestBatch = InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'FEFO-BATCH-OLD',
            'quantity' => 2,
            'cost_price' => 6.00,
            'expiry_date' => now()->addDays(15)->toDateString(),
        ]);

        $laterBatch = InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'FEFO-BATCH-NEW',
            'quantity' => 5,
            'cost_price' => 6.50,
            'expiry_date' => now()->addDays(45)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'fefo-pharmacist@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('sales.store'), [
            'patient_mode' => 'existing',
            'patient_id' => $patient->id,
            'payment_method' => 'cash',
            'payment_tendered' => 9999.99,
            'product_ids' => [$product->id, $product->id],
            'quantities' => [1, 2],
        ])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertSame(3, (int) $sale->lineItems()->sum('quantity'));
        $this->assertDatabaseHas('inventory_batches', [
            'id' => $earliestBatch->id,
            'quantity' => 0,
        ]);
        $this->assertDatabaseHas('inventory_batches', [
            'id' => $laterBatch->id,
            'quantity' => 4,
        ]);
    }

    public function test_dashboard_low_stock_card_links_to_filtered_inventory(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);

        $admin = User::factory()->create([
            'email' => 'lowstock-admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $lowProduct = Product::create([
            'name' => 'Low Stock Med',
            'generic_name' => 'Loratadine',
            'sku' => 'LOW-SKU-001',
            'price' => 20.00,
            'reorder_level' => 5,
        ]);
        InventoryBatch::create([
            'product_id' => $lowProduct->id,
            'batch_number' => 'LOW-BATCH',
            'quantity' => 3,
            'cost_price' => 12.00,
            'expiry_date' => now()->addMonths(3)->toDateString(),
        ]);

        $okProduct = Product::create([
            'name' => 'Sufficient Med',
            'generic_name' => 'Ibuprofen',
            'sku' => 'OK-SKU-001',
            'price' => 25.00,
            'reorder_level' => 5,
        ]);
        InventoryBatch::create([
            'product_id' => $okProduct->id,
            'batch_number' => 'OK-BATCH',
            'quantity' => 10,
            'cost_price' => 15.00,
            'expiry_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'lowstock-admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(route('products.index', ['stock_status' => 'low']), false);

        $filtered = $this->get(route('products.index', ['stock_status' => 'low']));
        $filtered->assertOk();
        $filtered->assertSee('Showing products with low stock only.');
        $filtered->assertSee('Low Stock Med');
        $filtered->assertDontSee('Sufficient Med');
    }

    public function test_sale_release_uses_front_shop_stock_only(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);

        $pharmacist = User::factory()->create([
            'email' => 'front-only-pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $backLocation = InventoryLocation::query()->where('code', 'back')->firstOrFail();

        $patient = Patient::create([
            'name' => 'Front Shop Patient',
            'birthdate' => '1994-07-20',
            'contact_info' => '09112223311',
            'allergies' => null,
        ]);

        $product = Product::create([
            'name' => 'Front Only Product',
            'generic_name' => 'Losartan',
            'sku' => 'FRONT-SKU-001',
            'price' => 18.00,
            'reorder_level' => 2,
        ]);

        InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $backLocation->id,
            'batch_number' => 'BACK-ONLY-BATCH',
            'quantity' => 5,
            'cost_price' => 12.00,
            'expiry_date' => now()->addDays(60)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'front-only-pharmacist@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'patient_mode' => 'existing',
                'patient_id' => $patient->id,
                'payment_method' => 'cash',
                'payment_tendered' => 9999.99,
                'product_ids' => [$product->id],
                'quantities' => [1],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('product_ids');

        $this->assertDatabaseMissing('sales', [
            'patient_id' => $patient->id,
            'payment_method' => 'cash',
        ]);
    }

    public function test_stock_request_approval_transfers_stock_from_back_to_front(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);

        $pharmacist = User::factory()->create([
            'email' => 'transfer-pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $staff = User::factory()->create([
            'email' => 'transfer-staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $product = Product::create([
            'name' => 'Transfer Product',
            'generic_name' => 'Metformin',
            'sku' => 'TRF-SKU-001',
            'price' => 11.00,
            'reorder_level' => 3,
        ]);

        $backLocation = InventoryLocation::query()->where('code', 'back')->firstOrFail();
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $backLocation->id,
            'batch_number' => 'TRF-BATCH-001',
            'quantity' => 10,
            'cost_price' => 6.00,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'transfer-pharmacist@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('stock-requests.store'), [
            'product_id' => $product->id,
            'quantity' => 4,
            'reason' => 'Front shop refill',
        ])->assertRedirect(route('stock-requests.index'));

        $stockRequest = \App\Models\StockRequest::latest('id')->firstOrFail();
        $this->post('/logout')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => 'transfer-staff@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('stock-requests.approve', $stockRequest), [
            'approved_quantity' => 4,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'location_id' => $backLocation->id,
            'batch_number' => 'TRF-BATCH-001',
            'quantity' => 6,
        ]);
        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'TRF-BATCH-001',
            'quantity' => 4,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'release',
            'reference_type' => 'App\Models\StockRequest',
            'reference_id' => $stockRequest->id,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'incoming',
            'reference_type' => 'App\Models\StockRequest',
            'reference_id' => $stockRequest->id,
        ]);
    }

    public function test_stock_request_approval_can_adjust_requested_quantity_before_fulfill(): void
    {
        $this->seed(RolesAndPermissionSeeder::class);

        $pharmacist = User::factory()->create([
            'email' => 'adjust-pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);
        $pharmacist->assignRole('pharmacist');

        $staff = User::factory()->create([
            'email' => 'adjust-staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $product = Product::create([
            'name' => 'Adjust Product',
            'generic_name' => 'Amlodipine',
            'sku' => 'ADJ-SKU-001',
            'price' => 9.00,
            'reorder_level' => 3,
        ]);

        $backLocation = InventoryLocation::query()->where('code', 'back')->firstOrFail();
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $backLocation->id,
            'batch_number' => 'ADJ-BATCH-001',
            'quantity' => 20,
            'cost_price' => 5.00,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ]);

        $this->post('/login', [
            'email' => 'adjust-pharmacist@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('stock-requests.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'reason' => 'Front refill request',
        ])->assertRedirect(route('stock-requests.index'));

        $stockRequest = \App\Models\StockRequest::latest('id')->firstOrFail();
        $this->post('/logout')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => 'adjust-staff@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post(route('stock-requests.approve', $stockRequest), [
            'approved_quantity' => 6,
            'adjustment_reason' => 'Limited stock for today',
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_requests', [
            'id' => $stockRequest->id,
            'quantity' => 10,
            'requested_quantity' => 10,
            'approved_quantity' => 6,
            'status' => 'fulfilled',
        ]);
        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'location_id' => $backLocation->id,
            'batch_number' => 'ADJ-BATCH-001',
            'quantity' => 14,
        ]);
        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'ADJ-BATCH-001',
            'quantity' => 6,
        ]);
    }

    public function test_sale_blocks_overdispense_when_rx_enforcement_is_block(): void
    {
        config()->set('rx.dispense_enforcement', 'block');
        $this->seed(RolesAndPermissionSeeder::class);
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('pharmacist');

        $patient = Patient::create([
            'name' => 'RX Block Patient',
            'birthdate' => '1990-01-01',
            'contact_info' => '09990001111',
        ]);
        $prescriber = \App\Models\Prescriber::create([
            'name' => 'RX Block Prescriber',
            'license_number' => 'RX-BLOCK-001',
            'contact_info' => 'rx-block@example.com',
        ]);
        $product = Product::create([
            'name' => 'RX Block Product',
            'generic_name' => 'Cefalexin',
            'sku' => 'RX-BLOCK-SKU',
            'price' => 10,
            'reorder_level' => 2,
        ]);
        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'prescriber_id' => $prescriber->id,
            'issued_date' => now()->toDateString(),
            'status' => 'active',
        ]);
        $prescription->prescriptionItems()->create([
            'product_id' => $product->id,
            'dosage' => '1 tab daily',
            'quantity' => 1,
        ]);
        InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'RX-BLOCK-BATCH',
            'quantity' => 10,
            'cost_price' => 2,
            'expiry_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->actingAs($pharmacist)
            ->from(route('sales.create'))
            ->post(route('sales.store'), [
                'patient_mode' => 'existing',
                'patient_id' => $patient->id,
                'prescription_id' => $prescription->id,
                'payment_method' => 'cash',
                'payment_tendered' => 9999.99,
                'product_ids' => [$product->id],
                'quantities' => [2],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('prescription_id');
    }

    public function test_sale_warn_mode_allows_overdispense_with_audit_log(): void
    {
        config()->set('rx.dispense_enforcement', 'warn');
        $this->seed(RolesAndPermissionSeeder::class);
        $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();

        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('pharmacist');
        $patient = Patient::create([
            'name' => 'RX Warn Patient',
            'birthdate' => '1990-01-01',
            'contact_info' => '09990002222',
        ]);
        $prescriber = \App\Models\Prescriber::create([
            'name' => 'RX Warn Prescriber',
            'license_number' => 'RX-WARN-001',
            'contact_info' => 'rx-warn@example.com',
        ]);
        $product = Product::create([
            'name' => 'RX Warn Product',
            'generic_name' => 'Azithromycin',
            'sku' => 'RX-WARN-SKU',
            'price' => 10,
            'reorder_level' => 2,
        ]);
        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'prescriber_id' => $prescriber->id,
            'issued_date' => now()->toDateString(),
            'status' => 'active',
        ]);
        $prescription->prescriptionItems()->create([
            'product_id' => $product->id,
            'dosage' => 'once a day',
            'quantity' => 1,
        ]);
        InventoryBatch::create([
            'product_id' => $product->id,
            'location_id' => $frontLocation->id,
            'batch_number' => 'RX-WARN-BATCH',
            'quantity' => 10,
            'cost_price' => 3,
            'expiry_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->actingAs($pharmacist)->post(route('sales.store'), [
            'patient_mode' => 'existing',
            'patient_id' => $patient->id,
            'prescription_id' => $prescription->id,
            'payment_method' => 'cash',
            'payment_tendered' => 9999.99,
            'product_ids' => [$product->id],
            'quantities' => [2],
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rx_dispense_warning_override',
            'auditable_type' => Prescription::class,
            'auditable_id' => $prescription->id,
        ]);
    }
}
