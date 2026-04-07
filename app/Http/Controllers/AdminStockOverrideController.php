<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminStockOverrideController extends Controller
{
    public function update(Request $request, InventoryBatch $batch)
    {
        $validated = $request->validate([
            'admin_pin' => 'required|string',
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $configuredPinHash = env('ADMIN_OVERRIDE_PIN_HASH');
        $configuredPinPlain = env('ADMIN_OVERRIDE_PIN', '1234');
        $isValidPin = $configuredPinHash
            ? Hash::check($validated['admin_pin'], $configuredPinHash)
            : hash_equals($configuredPinPlain, $validated['admin_pin']);

        if (! $isValidPin) {
            return back()->with('error', 'Invalid admin PIN.');
        }

        $oldValues = $batch->toArray();
        $batch->update(['quantity' => $validated['new_quantity']]);

        StockMovement::create([
            'product_id' => $batch->product_id,
            'inventory_batch_id' => $batch->id,
            'moved_by' => auth()->id(),
            'type' => 'adjustment',
            'quantity' => $validated['new_quantity'],
            'reference_type' => 'admin_override',
            'reference_id' => $batch->id,
            'notes' => $validated['notes'] ?? 'Admin stock override',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'stock_override',
            'auditable_id' => $batch->id,
            'auditable_type' => InventoryBatch::class,
            'old_values' => $oldValues,
            'new_values' => $batch->fresh()->toArray(),
        ]);

        return back()->with('success', 'Stock overridden successfully.');
    }
}
