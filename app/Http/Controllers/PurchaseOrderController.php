<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::latest()->paginate(15);
        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('purchase-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->format('Ymd-His'),
            'created_by' => auth()->id(),
            'status' => 'pending',
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $po->items()->create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'unit_cost' => $validated['unit_cost'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'po_created',
            'auditable_id' => $po->id,
            'auditable_type' => PurchaseOrder::class,
            'old_values' => null,
            'new_values' => $po->load('items')->toArray(),
        ]);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product');
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function incomingDeliveries()
    {
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'received'])
            ->latest()
            ->paginate(15);

        return view('purchase-orders.incoming', compact('purchaseOrders'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'PO approved.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after:today',
        ]);

        if ($purchaseOrder->status !== 'approved') {
            return back()->with('error', 'Only approved PO can be received.');
        }

        $purchaseOrder->load('items');
        foreach ($purchaseOrder->items as $item) {
            $batch = InventoryBatch::create([
                'product_id' => $item->product_id,
                'batch_number' => $validated['batch_number'] . '-' . $item->product_id,
                'quantity' => $item->quantity,
                'cost_price' => $item->unit_cost ?? 0,
                'expiry_date' => $validated['expiry_date'],
            ]);

            StockMovement::create([
                'product_id' => $item->product_id,
                'inventory_batch_id' => $batch->id,
                'moved_by' => auth()->id(),
                'type' => 'incoming',
                'quantity' => $item->quantity,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $purchaseOrder->id,
                'notes' => 'PO received',
            ]);
        }

        $purchaseOrder->update(['status' => 'received']);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'po_received',
            'auditable_id' => $purchaseOrder->id,
            'auditable_type' => PurchaseOrder::class,
            'old_values' => null,
            'new_values' => $purchaseOrder->fresh()->toArray(),
        ]);

        return back()->with('success', 'PO received and stock added.');
    }
}
