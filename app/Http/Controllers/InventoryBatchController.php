<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\AuditLog;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class InventoryBatchController extends Controller
{
    public function create(Product $product)
    {
        return view('products.batches.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'batch_number' => 'required|string|max:100|unique:inventory_batches,batch_number,NULL,id,product_id,' . $product->id,
            'quantity'     => 'required|integer|min:1',
            'cost_price'   => 'required|numeric|min:0',
            'expiry_date'  => 'required|date|after:today',
        ]);

        $data['product_id'] = $product->id;

        $batch = InventoryBatch::create($data);

        StockMovement::create([
            'product_id' => $product->id,
            'inventory_batch_id' => $batch->id,
            'moved_by' => auth()->id(),
            'type' => 'incoming',
            'quantity' => $batch->quantity,
            'reference_type' => 'batch_create',
            'reference_id' => $batch->id,
            'notes' => 'Stock received',
        ]);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'stock_received',
            'auditable_id'   => $batch->id,
            'auditable_type' => InventoryBatch::class,
            'old_values'     => null,
            'new_values'     => $batch->toArray(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Batch added to inventory.');
    }

    public function destroy(Product $product, InventoryBatch $batch)
    {
        if ($batch->product_id !== $product->id) {
            abort(404);
        }

        if ($batch->saleLineItems()->exists()) {
            return back()->with('error', 'Cannot delete a batch that has been used in sales.');
        }

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'batch_deleted',
            'auditable_id'   => $batch->id,
            'auditable_type' => InventoryBatch::class,
            'old_values'     => $batch->toArray(),
            'new_values'     => null,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'inventory_batch_id' => $batch->id,
            'moved_by' => auth()->id(),
            'type' => 'release',
            'quantity' => $batch->quantity,
            'reference_type' => 'batch_delete',
            'reference_id' => $batch->id,
            'notes' => 'Batch removed from inventory',
        ]);

        $batch->delete();

        return redirect()->route('products.show', $product)->with('success', 'Batch removed.');
    }
}