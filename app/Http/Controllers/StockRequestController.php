<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Services\InventoryReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StockRequestController extends Controller
{
    public function __construct(private readonly InventoryReleaseService $inventoryReleaseService)
    {
    }

    public function index()
    {
        abort_unless(Gate::allows('create stock requests') || Gate::allows('approve stock release'), 403);
        $stockRequests = StockRequest::with('product')->latest()->paginate(15);
        $productIds = collect($stockRequests->items())->pluck('product_id')->filter()->unique()->values();
        $frontStocksByProduct = InventoryBatch::query()
            ->whereIn('product_id', $productIds)
            ->forLocationCode('front')
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id');
        $backStocksByProduct = InventoryBatch::query()
            ->whereIn('product_id', $productIds)
            ->forLocationCode('back')
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id');

        return view('stock-requests.index', compact('stockRequests', 'frontStocksByProduct', 'backStocksByProduct'));
    }

    public function create()
    {
        abort_unless(Gate::allows('create stock requests'), 403);
        $products = Product::orderBy('name')->get();
        return view('stock-requests.create', compact('products'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('create stock requests'), 403);
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        StockRequest::create([
            'requested_by' => auth()->id(),
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'requested_quantity' => $validated['quantity'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('stock-requests.index')->with('success', 'Stock request submitted.');
    }

    public function approve(Request $request, StockRequest $stockRequest)
    {
        abort_unless(Gate::allows('approve stock release'), 403);
        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be processed.');
        }

        $maxQuantity = (int) ($stockRequest->requested_quantity ?: $stockRequest->quantity);
        $validated = $request->validate([
            'approved_quantity' => 'nullable|integer|min:1|max:' . $maxQuantity,
            'adjustment_reason' => 'nullable|string|max:500',
        ]);
        $approvedQuantity = (int) ($validated['approved_quantity'] ?? $maxQuantity);
        $requestedQuantity = (int) ($stockRequest->requested_quantity ?: $stockRequest->quantity);
        if ($approvedQuantity < $requestedQuantity && empty($validated['adjustment_reason'])) {
            return back()->withErrors(['adjustment_reason' => 'Adjustment reason is required when approved quantity is lower than requested quantity.']);
        }

        try {
            DB::transaction(function () use ($stockRequest, $approvedQuantity, $requestedQuantity, $validated) {
                $product = Product::findOrFail($stockRequest->product_id);
                $backLocation = InventoryLocation::query()->where('code', 'back')->firstOrFail();
                $frontLocation = InventoryLocation::query()->where('code', 'front')->firstOrFail();
                $allocations = $this->inventoryReleaseService->releaseProduct(
                    $stockRequest->product_id,
                    $approvedQuantity,
                    'stock_request',
                    $product->name,
                    'back'
                );

                foreach ($allocations as $allocation) {
                    $frontBatch = InventoryBatch::query()
                        ->where('product_id', $stockRequest->product_id)
                        ->where('location_id', $frontLocation->id)
                        ->where('batch_number', $allocation['batch_number'])
                        ->lockForUpdate()
                        ->first();

                    if ($frontBatch) {
                        $frontBatch->increment('quantity', $allocation['quantity']);
                    } else {
                        $frontBatch = InventoryBatch::create([
                            'product_id' => $stockRequest->product_id,
                            'location_id' => $frontLocation->id,
                            'batch_number' => $allocation['batch_number'],
                            'quantity' => $allocation['quantity'],
                            'cost_price' => $allocation['cost_price'] ?? 0,
                            'expiry_date' => $allocation['expiry_date'],
                        ]);
                    }

                    StockMovement::create([
                        'product_id' => $stockRequest->product_id,
                        'inventory_batch_id' => $allocation['inventory_batch_id'],
                        'moved_by' => auth()->id(),
                        'type' => 'release',
                        'quantity' => $allocation['quantity'],
                        'reference_type' => StockRequest::class,
                        'reference_id' => $stockRequest->id,
                        'notes' => "Transfer out: Back Inventory -> Front Shop ({$backLocation->code} to {$frontLocation->code}) by user #" . auth()->id(),
                    ]);

                    StockMovement::create([
                        'product_id' => $stockRequest->product_id,
                        'inventory_batch_id' => $frontBatch->id,
                        'moved_by' => auth()->id(),
                        'type' => 'incoming',
                        'quantity' => $allocation['quantity'],
                        'reference_type' => StockRequest::class,
                        'reference_id' => $stockRequest->id,
                        'notes' => "Transfer in: Back Inventory -> Front Shop ({$backLocation->code} to {$frontLocation->code}) by user #" . auth()->id(),
                    ]);

                    AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'stock_transfer_linked',
                        'auditable_id' => $stockRequest->id,
                        'auditable_type' => StockRequest::class,
                        'old_values' => null,
                        'new_values' => [
                            'product_id' => $stockRequest->product_id,
                            'quantity' => $allocation['quantity'],
                            'actor_id' => auth()->id(),
                            'back_batch_id' => $allocation['inventory_batch_id'],
                            'front_batch_id' => $frontBatch->id,
                        ],
                    ]);
                }

                if ($approvedQuantity < $requestedQuantity) {
                    AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'stock_request_adjusted_before_approval',
                        'auditable_id' => $stockRequest->id,
                        'auditable_type' => StockRequest::class,
                        'old_values' => [
                            'requested_quantity' => $requestedQuantity,
                        ],
                        'new_values' => [
                            'approved_quantity' => $approvedQuantity,
                            'adjustment_reason' => $validated['adjustment_reason'] ?? null,
                        ],
                    ]);
                }

                $stockRequest->update([
                    'approved_quantity' => $approvedQuantity,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'status' => 'fulfilled',
                    'adjustment_reason' => $validated['adjustment_reason'] ?? null,
                    'fulfilled_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'stock_request_fulfilled',
                    'auditable_id' => $stockRequest->id,
                    'auditable_type' => StockRequest::class,
                    'old_values' => null,
                    'new_values' => $stockRequest->fresh()->toArray(),
                ]);
            });
        } catch (InsufficientStockException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Stock request updated and approved. Stock moved from Back Inventory to Front Shop.');
    }
}
