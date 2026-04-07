<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
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

        $this->post(route('purchase-orders.store'), [
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_cost' => 9.50,
            'expected_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Test PO',
        ])->assertRedirect(route('purchase-orders.index'));

        $po = \App\Models\PurchaseOrder::firstOrFail();

        $this->post('/logout')->assertRedirect(route('login'));

        // Pharmacist manages patients and requests medicine.
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
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => 'App\Models\Patient',
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
}
