<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLineItem;
use App\Models\StockMovement;
use App\Services\InventoryReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function __construct(private readonly InventoryReleaseService $inventoryReleaseService)
    {
    }

    public function index()
    {
        $sales = Sale::with(['patient', 'user'])->latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        $products = Product::query()
            ->withSum([
                'inventoryBatches as sellable_stock' => fn ($query) => $query->releasable()->forLocationCode('front'),
            ], 'quantity')
            ->withSum([
                'inventoryBatches as front_stock' => fn ($query) => $query->forLocationCode('front'),
            ], 'quantity')
            ->withSum([
                'inventoryBatches as back_stock' => fn ($query) => $query->forLocationCode('back'),
            ], 'quantity')
            ->orderBy('name')
            ->get();
        $prescriptions = Prescription::with(['patient', 'prescriber'])
            ->where('status', 'active')
            ->orderByDesc('issued_date')
            ->get();

        return view('sales.create', compact('patients', 'products', 'prescriptions'));
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
            'prescription_id' => 'nullable|exists:prescriptions,id',
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

        if (! empty($validated['prescription_id']) && $validated['patient_mode'] === 'new') {
            return back()->withErrors(['prescription_id' => 'Prescription can only be linked to an existing patient.'])->withInput();
        }

        try {
            return DB::transaction(function () use ($validated) {
                $lineEntries = [];
                $totalAmount = 0;

                $requestedByProduct = [];
                foreach ($validated['product_ids'] as $idx => $productId) {
                    $requestedQty = (int) ($validated['quantities'][$idx] ?? 0);
                    if ($requestedQty <= 0) {
                        continue;
                    }
                    $requestedByProduct[$productId] = ($requestedByProduct[$productId] ?? 0) + $requestedQty;
                }

                foreach ($requestedByProduct as $productId => $requestedQty) {
                    $product = Product::findOrFail($productId);
                    $allocations = $this->inventoryReleaseService->releaseProduct(
                        (int) $productId,
                        $requestedQty,
                        'product_ids',
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

                if (! empty($validated['prescription_id'])) {
                    $prescription = Prescription::findOrFail($validated['prescription_id']);
                    if ((int) $prescription->patient_id !== (int) $patientId) {
                        return back()->withErrors(['prescription_id' => 'Selected prescription does not belong to the selected patient.'])->withInput();
                    }
                    if ($prescription->status !== 'active') {
                        return back()->withErrors(['prescription_id' => 'Only active prescriptions can be linked to sales.'])->withInput();
                    }

                    $prescription->load('prescriptionItems.product');
                    if ($prescription->prescriptionItems->isNotEmpty()) {
                        $remainingByProduct = $prescription->remainingByProduct();
                        $rxMap = $prescription->prescriptionItems->keyBy('product_id');
                        $violations = [];
                        foreach ($requestedByProduct as $productId => $requestedQty) {
                            $item = $rxMap->get($productId);
                            if (! $item) {
                                $violations[] = "Product ID {$productId} is not listed in the selected prescription.";
                                continue;
                            }

                            $remainingQty = (int) ($remainingByProduct[$productId] ?? 0);
                            if ($requestedQty > $remainingQty) {
                                $violations[] = "{$item->product?->name} exceeds remaining RX quantity ({$remainingQty}).";
                            }
                        }

                        if (! empty($violations)) {
                            $mode = strtolower((string) config('rx.dispense_enforcement', 'block'));
                            if ($mode === 'warn') {
                                AuditLog::create([
                                    'user_id' => auth()->id(),
                                    'action' => 'rx_dispense_warning_override',
                                    'auditable_id' => $prescription->id,
                                    'auditable_type' => Prescription::class,
                                    'old_values' => null,
                                    'new_values' => [
                                        'violations' => $violations,
                                        'requested_by_product' => $requestedByProduct,
                                    ],
                                ]);
                            } else {
                                return back()->withErrors(['prescription_id' => implode(' ', $violations)])->withInput();
                            }
                        }
                    }
                }

                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'patient_id' => $patientId,
                    'prescription_id' => $validated['prescription_id'] ?? null,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
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

                return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock released using FEFO.');
            });
        } catch (InsufficientStockException $exception) {
            return back()->withErrors([$exception->errorKey => $exception->getMessage()])->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['patient', 'lineItems.inventoryBatch.product', 'user']);
        return view('sales.show', compact('sale'));
    }
}
