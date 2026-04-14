<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryBatchController;
use App\Http\Controllers\AdminStockOverrideController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/kiosk-order', [PreOrderController::class, 'createPublic'])->name('public.kiosk-order');
Route::post('/kiosk-order', [PreOrderController::class, 'storePublic'])->name('public.kiosk-order.store');
Route::get('/kiosk-order/ticket/{preOrder}', [PreOrderController::class, 'showTicket'])->name('public.kiosk-order.ticket');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('patients/create', [PatientController::class, 'create'])->middleware('can:create patients')->name('patients.create');
    Route::post('patients', [PatientController::class, 'store'])->middleware('can:create patients')->name('patients.store');
    Route::get('patients', [PatientController::class, 'index'])->middleware('can:view patients')->name('patients.index');
    Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->middleware('can:edit patients')->name('patients.edit');
    Route::patch('patients/{patient}', [PatientController::class, 'update'])->middleware('can:edit patients')->name('patients.update');
    Route::get('patients/{patient}', [PatientController::class, 'show'])->middleware('can:view patients')->name('patients.show');
    Route::delete('patients/{patient}', [PatientController::class, 'destroy'])->middleware('can:delete patients')->name('patients.destroy');

    Route::get('products/create', [ProductController::class, 'create'])->middleware('can:create products')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('can:create products')->name('products.store');
    Route::get('products', [ProductController::class, 'index'])->middleware('can:view products')->name('products.index');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('can:edit products')->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->middleware('can:edit products')->name('products.update');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('can:view products')->name('products.show');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('can:delete products')->name('products.destroy');

    Route::resource('products.batches', InventoryBatchController::class)
        ->only(['create', 'store', 'destroy'])
        ->middleware('can:edit inventory');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])
        ->middleware('can:view purchase orders')
        ->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])
        ->middleware('can:create purchase orders')
        ->name('purchase-orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])
        ->middleware('can:create purchase orders')
        ->name('purchase-orders.store');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
        ->middleware('can:view purchase orders')
        ->name('purchase-orders.show');
    Route::get('incoming-deliveries', [PurchaseOrderController::class, 'incomingDeliveries'])
        ->middleware('can:view incoming deliveries')
        ->name('purchase-orders.incoming');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
        ->middleware('can:approve purchase orders')
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->middleware('can:edit inventory')
        ->name('purchase-orders.receive');

    Route::resource('stock-requests', StockRequestController::class)
        ->only(['index', 'create', 'store'])
        ->middleware('auth');
    Route::post('stock-requests/{stockRequest}/approve', [StockRequestController::class, 'approve'])
        ->middleware('can:approve stock release')
        ->name('stock-requests.approve');

    Route::get('reports/inventory', [ReportController::class, 'inventory'])
        ->middleware('can:view reports')
        ->name('reports.inventory');
    Route::get('reports/inventory/export-pdf', [ReportController::class, 'inventoryPdf'])
        ->middleware('can:view reports')
        ->name('reports.inventory.pdf');
    Route::get('reports/patient-purchases', [ReportController::class, 'patientPurchases'])
        ->middleware('can:view reports')
        ->name('reports.patient-purchases');
    Route::get('reports/patient-purchases/export-pdf', [ReportController::class, 'patientPurchasesPdf'])
        ->middleware('can:view reports')
        ->name('reports.patient-purchases.pdf');

    Route::get('sales', [SalesController::class, 'index'])
        ->middleware('can:view sales')
        ->name('sales.index');
    Route::get('sales/create', [SalesController::class, 'create'])
        ->middleware('can:create sales')
        ->name('sales.create');
    Route::post('sales', [SalesController::class, 'store'])
        ->middleware('can:create sales')
        ->name('sales.store');
    Route::get('sales/{sale}', [SalesController::class, 'show'])
        ->middleware('can:view sales')
        ->name('sales.show');

    Route::get('pre-orders/{preOrder}/scan', [PreOrderController::class, 'scanAndCreateSale'])
        ->middleware(['signed', 'can:create sales'])
        ->name('pre-orders.scan');

    Route::get('stock-movements', [StockMovementController::class, 'index'])
        ->middleware('can:view stock movements')
        ->name('stock-movements.index');

    Route::patch('inventory-batches/{batch}/override', [AdminStockOverrideController::class, 'update'])
        ->middleware('can:override stock')
        ->name('inventory-batches.override');

    Route::resource('audit-logs', AuditLogController::class)
        ->only(['index', 'show'])
        ->middleware('can:view audit logs');
});
