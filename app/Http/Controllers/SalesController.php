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
use Illuminate\Validation\ValidationException;

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

    public function refresh(Request $request)
    {
        $sales = Sale::with(['patient', 'user'])->latest()->paginate(20);

        return response()->json([
            'table' => view('sales._table', compact('sales'))->render(),
            'pagination' => view('sales._pagination', compact('sales'))->render(),
            'updated_at' => now()->format('M d, Y H:i:s'),
        ]);
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
            ->take(500)
            ->get();

        $prescriptions = Prescription::with(['patient', 'prescriber', 'prescriptionItems'])
            ->where('status', 'active')
            ->orderByDesc('issued_date')
            ->take(200)
            ->get();

        return view('sales.create', compact('patients', 'products', 'prescriptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_mode'                  => 'required|in:existing,new',
            'patient_id'                    => 'nullable|exists:patients,id',
            'patient_name'                  => 'nullable|string|max:255',
            'patient_birthdate'             => 'nullable|date|before:today',
            'patient_contact_info'          => 'nullable|string|max:255',
            'patient_allergies'             => 'nullable|string',
            'prescription_id'              => 'nullable|exists:prescriptions,id',
            'payment_method'               => 'required|in:cash,card,insurance',
            'payment_tendered'             => 'nullable|numeric|min:0',
            'payment_reference'            => 'nullable|string|max:255',
            'insurance_provider'           => 'nullable|string|max:255',
            'insurance_policy_number'      => 'nullable|string|max:255',
            'insurance_authorization_code' => 'nullable|string|max:255',
            'product_ids'                  => 'required|array|min:1',
            'product_ids.*'                => 'required|exists:products,id',
            'quantities'                   => 'required|array|min:1',
            'quantities.*'                 => 'required|integer|min:1',
        ]);

        // ─────────────────────────────────────────────────────────────────────
        // Pre-transaction validation — read-only checks with no DB side-effects.
        // These can safely use return back() because no stock has been touched yet.
        // ─────────────────────────────────────────────────────────────────────

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

        // Payment method fields that don't depend on the computed total
        if ($validated['payment_method'] === 'card' && empty($validated['payment_reference'])) {
            return back()->withErrors(['payment_reference' => 'Card transaction reference is required for credit payments.'])->withInput();
        }

        if ($validated['payment_method'] === 'insurance') {
            if (empty($validated['insurance_provider']) || empty($validated['insurance_policy_number'])) {
                return back()->withErrors(['insurance_provider' => 'Insurance provider and policy number are required for insurance payments.'])->withInput();
            }
        }

        // Prescription status — read-only, safe before transaction
        if (! empty($validated['prescription_id'])) {
            $prescriptionCheck = Prescription::findOrFail($validated['prescription_id']);
            if ($prescriptionCheck->status !== 'active') {
                return back()->withErrors(['prescription_id' => 'Only active prescriptions can be linked to sales.'])->withInput();
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // Transaction — stock is touched here, so any failure must throw an
        // exception (not return a Response) to guarantee a rollback.
        // ValidationException propagates out of DB::transaction(), triggers the
        // rollback automatically, and Laravel's exception handler redirects back
        // with errors — exactly the same UX as return back()->withErrors().
        // ─────────────────────────────────────────────────────────────────────

        try {
            return DB::transaction(function () use ($validated) {

                // --- Build line entries (releases stock via FEFO) ---
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
                            'product_id'         => (int) $productId,
                            'quantity'           => $allocation['quantity'],
                            'unit_price'         => $product->price,
                            'subtotal'           => $allocation['quantity'] * (float) $product->price,
                        ];
                        $totalAmount += $allocation['quantity'] * (float) $product->price;
                    }
                }

                if (empty($lineEntries)) {
                    // Throw so the transaction rolls back before any DB writes persist
                    throw ValidationException::withMessages([
                        'product_ids' => 'Please add at least one valid medicine line.',
                    ]);
                }

                // --- Payment amount validation (total is now known) ---
                $paymentTendered  = (float) ($validated['payment_tendered'] ?? 0);
                $paymentChangeDue = 0;
                $paymentReference           = $validated['payment_reference'] ?? null;
                $insuranceProvider          = $validated['insurance_provider'] ?? null;
                $insurancePolicyNumber      = $validated['insurance_policy_number'] ?? null;
                $insuranceAuthorizationCode = $validated['insurance_authorization_code'] ?? null;

                if ($validated['payment_method'] === 'cash') {
                    if ($paymentTendered < $totalAmount) {
                        // Throw — stock already decremented above, must rollback
                        throw ValidationException::withMessages([
                            'payment_tendered' => 'Cash received must be equal to or greater than the total amount.',
                        ]);
                    }
                    $paymentChangeDue = round($paymentTendered - $totalAmount, 2);
                }

                if ($validated['payment_method'] === 'card') {
                    // Card reference was validated pre-transaction; just normalise tendered amount
                    if ($paymentTendered <= 0) {
                        $paymentTendered = $totalAmount;
                    }
                    if ($paymentTendered < $totalAmount) {
                        throw ValidationException::withMessages([
                            'payment_tendered' => 'Card payment must cover the total amount.',
                        ]);
                    }
                }

                if ($validated['payment_method'] === 'insurance') {
                    // Provider/policy validated pre-transaction; insurance pays in full externally
                    $paymentTendered = 0;
                }

                // --- Create or resolve patient ---
                $patientId = $validated['patient_id'] ?? null;

                if ($validated['patient_mode'] === 'new') {
                    $patient   = Patient::create([
                        'name'         => $validated['patient_name'],
                        'birthdate'    => $validated['patient_birthdate'],
                        'contact_info' => $validated['patient_contact_info'],
                        'allergies'    => $validated['patient_allergies'] ?? null,
                    ]);
                    $patientId = $patient->id;
                }

                // --- Prescription patient ownership + quantity checks ---
                if (! empty($validated['prescription_id'])) {
                    $prescription = Prescription::findOrFail($validated['prescription_id']);

                    if ((int) $prescription->patient_id !== (int) $patientId) {
                        throw ValidationException::withMessages([
                            'prescription_id' => 'Selected prescription does not belong to the selected patient.',
                        ]);
                    }

                    // Status was checked pre-transaction but re-check inside to guard against
                    // a concurrent update between the pre-check and the lock here
                    if ($prescription->status !== 'active') {
                        throw ValidationException::withMessages([
                            'prescription_id' => 'Only active prescriptions can be linked to sales.',
                        ]);
                    }

                    $prescription->load('prescriptionItems.product');

                    if ($prescription->prescriptionItems->isNotEmpty()) {
                        $remainingByProduct = $prescription->remainingByProduct();
                        $rxMap              = $prescription->prescriptionItems->keyBy('product_id');
                        $violations         = [];

                        foreach ($requestedByProduct as $productId => $requestedQty) {
                            $rxItem = $rxMap->get($productId);
                            if (! $rxItem) {
                                $violations[] = "Product ID {$productId} is not listed in the selected prescription.";
                                continue;
                            }

                            $remainingQty = (int) ($remainingByProduct[$productId] ?? 0);
                            if ($requestedQty > $remainingQty) {
                                $violations[] = "{$rxItem->product?->name} exceeds remaining RX quantity ({$remainingQty}).";
                            }
                        }

                        if (! empty($violations)) {
                            $mode = strtolower((string) config('rx.dispense_enforcement', 'block'));

                            if ($mode === 'warn') {
                                AuditLog::create([
                                    'user_id'        => auth()->id(),
                                    'action'         => 'rx_dispense_warning_override',
                                    'auditable_id'   => $prescription->id,
                                    'auditable_type' => Prescription::class,
                                    'old_values'     => null,
                                    'new_values'     => [
                                        'violations'            => $violations,
                                        'requested_by_product'  => $requestedByProduct,
                                    ],
                                ]);
                            } else {
                                throw ValidationException::withMessages([
                                    'prescription_id' => implode(' ', $violations),
                                ]);
                            }
                        }
                    }
                }

                // --- Persist sale ---
                $sale = Sale::create([
                    'user_id'                    => auth()->id(),
                    'patient_id'                 => $patientId,
                    'prescription_id'            => $validated['prescription_id'] ?? null,
                    'total_amount'               => $totalAmount,
                    'payment_method'             => $validated['payment_method'],
                    'payment_tendered'           => $paymentTendered,
                    'payment_change_due'         => $paymentChangeDue,
                    'payment_reference'          => $paymentReference,
                    'insurance_provider'         => $insuranceProvider,
                    'insurance_policy_number'    => $insurancePolicyNumber,
                    'insurance_authorization_code' => $insuranceAuthorizationCode,
                ]);

                foreach ($lineEntries as $entry) {
                    SaleLineItem::create([
                        'sale_id'            => $sale->id,
                        'inventory_batch_id' => $entry['inventory_batch_id'],
                        'quantity'           => $entry['quantity'],
                        'unit_price'         => $entry['unit_price'],
                        'subtotal'           => $entry['subtotal'],
                    ]);

                    StockMovement::create([
                        'product_id'         => $entry['product_id'],
                        'inventory_batch_id' => $entry['inventory_batch_id'],
                        'moved_by'           => auth()->id(),
                        'type'               => 'release',
                        'quantity'           => $entry['quantity'],
                        'reference_type'     => Sale::class,
                        'reference_id'       => $sale->id,
                        'notes'              => 'Medicine released via POS',
                    ]);
                }

                AuditLog::create([
                    'user_id'        => auth()->id(),
                    'action'         => 'sale_created',
                    'auditable_id'   => $sale->id,
                    'auditable_type' => Sale::class,
                    'old_values'     => null,
                    'new_values'     => $sale->load('lineItems')->toArray(),
                ]);

                return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock released using FEFO.');
            });
        } catch (InsufficientStockException $exception) {
            return back()->withErrors([$exception->errorKey => $exception->getMessage()])->withInput();
        }
        // ValidationException thrown inside the transaction propagates naturally here.
        // DB::transaction() rolls back on any Throwable, then re-throws.
        // Laravel's exception handler converts ValidationException to a redirect-back-with-errors
        // response automatically — no explicit catch needed.
    }

    public function show(Sale $sale)
    {
        $sale->load(['patient', 'lineItems.inventoryBatch.product', 'user']);
        return view('sales.show', compact('sale'));
    }
}