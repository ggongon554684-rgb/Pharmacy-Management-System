<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StockRequestController extends Controller
{
    public function index()
    {
        abort_unless(Gate::allows('create stock requests') || Gate::allows('approve stock release'), 403);
        $stockRequests = StockRequest::with('product')->latest()->paginate(15);
        return view('stock-requests.index', compact('stockRequests'));
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
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('stock-requests.index')->with('success', 'Stock request submitted.');
    }

    public function approve(StockRequest $stockRequest)
    {
        abort_unless(Gate::allows('approve stock release'), 403);
        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be processed.');
        }

        $batches = InventoryBatch::where('product_id', $stockRequest->product_id)
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date')
            ->get();

        $available = $batches->sum('quantity');
        if ($available < $stockRequest->quantity) {
            return back()->with('error', 'Insufficient stock to fulfill request.');
        }

        $remaining = $stockRequest->quantity;
        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($batch->quantity, $remaining);
            $batch->update(['quantity' => $batch->quantity - $deduct]);

            StockMovement::create([
                'product_id' => $stockRequest->product_id,
                'inventory_batch_id' => $batch->id,
                'moved_by' => auth()->id(),
                'type' => 'release',
                'quantity' => $deduct,
                'reference_type' => StockRequest::class,
                'reference_id' => $stockRequest->id,
                'notes' => 'Stock request fulfilled',
            ]);

            $remaining -= $deduct;
        }

        $stockRequest->update([
            'approved_by' => auth()->id(),
            'status' => 'fulfilled',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'stock_request_fulfilled',
            'auditable_id' => $stockRequest->id,
            'auditable_type' => StockRequest::class,
            'old_values' => null,
            'new_values' => $stockRequest->fresh()->toArray(),
        ]);

        return back()->with('success', 'Stock release approved.');
    }
}
