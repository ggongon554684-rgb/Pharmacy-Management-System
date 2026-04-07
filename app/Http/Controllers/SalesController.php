<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryBatch;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLineItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::with('patient')->latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        $products = Product::withSum('inventoryBatches', 'quantity')->orderBy('name')->get();
        return view('sales.create', compact('patients', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_mode' => 'required|in:existing,new',
            'patient_id' => 'nullable|exists:patients,id',
            'patient_name' => 'nullable|string|max:255',
            'patient_birthdate' => 'nullable|date|before:today',
            'patient_contact_info' => 'nullable|string|max:255',
            'patient_allergies' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,insurance',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
        ]);

        if ($validated['patient_mode'] === 'existing' && empty($validated['patient_id'])) {
            return back()->withErrors(['patient_id' => 'Please select an existing patient.'])->withInput();
        }

        if ($validated['patient_mode'] === 'new') {
            if (empty($validated['patient_name']) || empty($validated['patient_birthdate']) || empty($validated['patient_contact_info'])) {
                return back()->withErrors(['patient_name' => 'New patient name, birthdate, and contact are required.'])->withInput();
            }
        }

        return DB::transaction(function () use ($validated) {
            $lineEntries = [];
            $totalAmount = 0;

            foreach ($validated['product_ids'] as $idx => $productId) {
                $requestedQty = (int) $validated['quantities'][$idx];
                if ($requestedQty <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productId);
                $batches = InventoryBatch::where('product_id', $productId)
                    ->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '>=', now()->toDateString())
                    ->orderBy('expiry_date')
                    ->get();

                $available = $batches->sum('quantity');
                if ($available < $requestedQty) {
                    return back()->withErrors([
                        'product_ids.' . $idx => "Insufficient stock for {$product->name}. Requested {$requestedQty}, available {$available}.",
                    ])->withInput();
                }

                $remaining = $requestedQty;
                foreach ($batches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $deduct = min($batch->quantity, $remaining);
                    $lineEntries[] = [
                        'inventory_batch_id' => $batch->id,
                        'product_id' => $productId,
                        'quantity' => $deduct,
                        'unit_price' => $product->price,
                        'subtotal' => $deduct * (float) $product->price,
                    ];
                    $totalAmount += $deduct * (float) $product->price;
                    $remaining -= $deduct;
                }
            }

            if (empty($lineEntries)) {
                return back()->withErrors(['product_ids' => 'Please add at least one valid medicine line.'])->withInput();
            }

            $patientId = $validated['patient_id'] ?? null;
            if ($validated['patient_mode'] === 'new') {
                $patient = Patient::create([
                    'name' => $validated['patient_name'],
                    'birthdate' => $validated['patient_birthdate'],
                    'contact_info' => $validated['patient_contact_info'],
                    'allergies' => $validated['patient_allergies'] ?? null,
                ]);
                $patientId = $patient->id;
            }

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'patient_id' => $patientId,
                'prescription_id' => null,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($lineEntries as $entry) {
                $batch = InventoryBatch::findOrFail($entry['inventory_batch_id']);
                $batch->update(['quantity' => $batch->quantity - $entry['quantity']]);

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
                    'notes' => 'Medicine released via POS',
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'sale_created',
                'auditable_id' => $sale->id,
                'auditable_type' => Sale::class,
                'old_values' => null,
                'new_values' => $sale->load('lineItems')->toArray(),
            ]);

            return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock released.');
        });
    }

    public function show(Sale $sale)
    {
        $sale->load(['patient', 'lineItems.inventoryBatch.product', 'user']);
        return view('sales.show', compact('sale'));
    }
}
