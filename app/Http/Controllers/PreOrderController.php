<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\AuditLog;
use App\Models\PreOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLineItem;
use App\Models\StockMovement;
use App\Services\InventoryReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PreOrderController extends Controller
{
    public function __construct(private readonly InventoryReleaseService $inventoryReleaseService)
    {
    }

    public function createPublic()
    {
        $products = Product::query()
            ->withSum([
                'inventoryBatches as sellable_stock' => fn ($query) => $query->releasable()->forLocationCode('front'),
            ], 'quantity')
            ->orderBy('name')
            ->get();

        return view('public.kiosk-order', compact('products'));
    }

    public function storePublic(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,insurance',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
        ]);

        $preOrder = DB::transaction(function () use ($validated) {
            $preOrder = PreOrder::create([
                'customer_name' => $validated['customer_name'] ?: null,
                'payment_method' => $validated['payment_method'],
                'scan_token' => Str::upper(Str::random(10)),
                'status' => 'pending',
            ]);

            foreach ($validated['product_ids'] as $idx => $productId) {
                $qty = (int) ($validated['quantities'][$idx] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $product = Product::findOrFail($productId);
                $preOrder->items()->create([
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $qty * (float) $product->price,
                ]);
            }

            return $preOrder;
        });

        return redirect()->route('public.kiosk-order.ticket', $preOrder);
    }

    public function showTicket(PreOrder $preOrder)
    {
        $preOrder->load('items.product');
        $scanUrl = URL::temporarySignedRoute(
            'pre-orders.scan',
            now()->addHours(24),
            ['preOrder' => $preOrder->id]
        );

        return view('public.kiosk-ticket', compact('preOrder', 'scanUrl'));
    }

    public function scanAndCreateSale(PreOrder $preOrder)
    {
        if ($preOrder->status === 'fulfilled' && $preOrder->sale_id) {
            return redirect()->route('sales.show', ['sale' => $preOrder->sale_id, 'print' => 1])
                ->with('success', 'Order already fulfilled. Opening receipt for printing.');
        }

        try {
            $sale = DB::transaction(function () use ($preOrder) {
                $preOrder->load('items');
                if ($preOrder->items->isEmpty()) {
                    return null;
                }

                $lineEntries = [];
                $totalAmount = 0;

                $requestedByProduct = [];
                foreach ($preOrder->items as $item) {
                    $requestedByProduct[$item->product_id] = ($requestedByProduct[$item->product_id] ?? 0) + (int) $item->quantity;
                }

                foreach ($requestedByProduct as $productId => $requestedQty) {
                    $product = Product::findOrFail($productId);
                    $allocations = $this->inventoryReleaseService->releaseProduct(
                        (int) $productId,
                        $requestedQty,
                        'pre_order',
                        $product->name,
                        'front'
                    );

                    foreach ($allocations as $allocation) {
                        $lineEntries[] = [
                            'inventory_batch_id' => $allocation['inventory_batch_id'],
                            'product_id' => (int) $productId,
                            'quantity' => $allocation['quantity'],
                            'unit_price' => $product->price,
                            'subtotal' => $allocation['quantity'] * (float) $product->price,
                        ];
                        $totalAmount += $allocation['quantity'] * (float) $product->price;
                    }
                }

                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'patient_id' => null,
                    'prescription_id' => null,
                    'total_amount' => $totalAmount,
                    'payment_method' => $preOrder->payment_method,
                ]);

                foreach ($lineEntries as $entry) {
                    SaleLineItem::create([
                        'sale_id' => $sale->id,
                        'inventory_batch_id' => $entry['inventory_batch_id'],
                        'quantity' => $entry['quantity'],
                        'unit_price' => $entry['unit_price'],
                        'subtotal' => $entry['subtotal'],
                    ]);

                    StockMovement::create([
                        'product_id' => $entry['product_id'],
                        'inventory_batch_id' => $entry['inventory_batch_id'],
                        'moved_by' => auth()->id(),
                        'type' => 'release',
                        'quantity' => $entry['quantity'],
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => 'Released from scanned pre-order #' . $preOrder->id,
                    ]);
                }

                $preOrder->update([
                    'status' => 'fulfilled',
                    'scanned_at' => now(),
                    'fulfilled_at' => now(),
                    'sale_id' => $sale->id,
                    'fulfilled_by' => auth()->id(),
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'sale_created_from_preorder',
                    'auditable_id' => $sale->id,
                    'auditable_type' => Sale::class,
                    'old_values' => null,
                    'new_values' => [
                        'pre_order_id' => $preOrder->id,
                        'sale_id' => $sale->id,
                    ],
                ]);

                return $sale;
            });
        } catch (InsufficientStockException $exception) {
            return redirect()->route('sales.create')
                ->with('error', 'Pre-order cannot be completed: ' . $exception->getMessage());
        }

        if (! $sale) {
            return redirect()->route('sales.create')->with('error', 'Pre-order has no items to process.');
        }

        return redirect()->route('sales.show', ['sale' => $sale->id, 'print' => 1])
            ->with('success', 'Transaction created from QR scan. Print receipt, then release medicine.');
    }
}
