<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'delivery_cost' => 'nullable|numeric|min:0',
            'insurance_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
        ]);

        $unitCost = (float) ($validated['unit_cost'] ?? 0);
        $itemSubtotal = ((int) $validated['quantity']) * $unitCost;
        $deliveryCost = (float) ($validated['delivery_cost'] ?? 0);
        $insuranceCost = (float) ($validated['insurance_cost'] ?? 0);
        $otherCost = (float) ($validated['other_cost'] ?? 0);
        $totalCost = $itemSubtotal + $deliveryCost + $insuranceCost + $otherCost;

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->format('Ymd-His'),
            'created_by' => auth()->id(),
            'status' => 'pending',
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'delivery_cost' => $deliveryCost,
            'insurance_cost' => $insuranceCost,
            'other_cost' => $otherCost,
            'total_cost' => $totalCost,
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

    public function refreshIncoming(Request $request)
    {
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'received'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'table' => view('purchase-orders._incoming-table', compact('purchaseOrders'))->render(),
            'pagination' => view('purchase-orders._incoming-pagination', compact('purchaseOrders'))->render(),
            'updated_at' => now()->format('M d, Y H:i:s'),
        ]);
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
            'receive_date' => 'required|date|before_or_equal:today',
            'backdate_reason' => 'nullable|string|max:255',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after_or_equal:today',
        ]);

        if ($purchaseOrder->status !== 'approved') {
            return back()->with('error', 'Only approved PO can be received.');
        }

        $receiveDate = $validated['receive_date'];
        $isToday = $receiveDate === now()->format('Y-m-d');
        if (!$isToday && empty($validated['backdate_reason'])) {
            return back()->withErrors(['backdate_reason' => 'Reason is required when receiving on a date other than today.'])->withInput();
        }

        $purchaseOrder->load('items.product');
        foreach ($purchaseOrder->items as $item) {
            $candidateBatchNumber = $validated['batch_number'] . '-' . $item->product_id;
            $batchAlreadyExists = InventoryBatch::query()
                ->where('product_id', $item->product_id)
                ->where('batch_number', $candidateBatchNumber)
                ->exists();

            if ($batchAlreadyExists) {
                $productName = $item->product->name ?? "product #{$item->product_id}";
                return back()->withErrors([
                    'batch_number' => "Batch {$candidateBatchNumber} already exists for {$productName}. Use a different batch number.",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($purchaseOrder, $validated, $isToday) {
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
                    'moved_at' => $validated['receive_date'],
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

            if (!$isToday) {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'po_backdated',
                    'auditable_id' => $purchaseOrder->id,
                    'auditable_type' => PurchaseOrder::class,
                    'old_values' => null,
                    'new_values' => ['receive_date' => $validated['receive_date'], 'reason' => $validated['backdate_reason']],
                ]);
            }
        });

        return back()->with('success', 'PO received and stock added.');
    }

    public function showReceiveForm(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'approved') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'Only approved PO can be received.');
        }

        return view('purchase-orders.receive', compact('purchaseOrder'));
    }
}
