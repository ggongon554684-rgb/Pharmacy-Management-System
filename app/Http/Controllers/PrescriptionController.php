<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Prescriber;
use App\Models\Prescription;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::with(['patient', 'prescriber', 'prescriptionItems.product'])
            ->latest()
            ->paginate(15);
        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        $prescribers = Prescriber::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('prescriptions.create', compact('patients', 'prescribers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePrescription($request);

        $prescription = DB::transaction(function () use ($data) {
            $prescription = Prescription::create([
                'patient_id' => $data['patient_id'],
                'prescriber_id' => $data['prescriber_id'],
                'issued_date' => $data['issued_date'],
                'status' => $data['status'],
            ]);

            foreach ($data['rx_items'] as $item) {
                $prescription->prescriptionItems()->create([
                    'product_id' => $item['product_id'],
                    'dosage' => $item['dosage'],
                    'quantity' => $item['quantity'],
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'prescription_created',
                'auditable_id' => $prescription->id,
                'auditable_type' => Prescription::class,
                'old_values' => null,
                'new_values' => $prescription->load('prescriptionItems')->toArray(),
            ]);

            return $prescription;
        });

        return redirect()->route('prescriptions.show', $prescription)->with('success', 'Prescription created.');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'prescriber', 'prescriptionItems.product']);
        $remainingByProduct = $prescription->remainingByProduct();
        return view('prescriptions.show', compact('prescription', 'remainingByProduct'));
    }

    public function edit(Prescription $prescription)
    {
        $patients = Patient::orderBy('name')->get();
        $prescribers = Prescriber::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $prescription->load('prescriptionItems');

        return view('prescriptions.edit', compact('prescription', 'patients', 'prescribers', 'products'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $data = $this->validatePrescription($request);
        $oldValues = $prescription->load('prescriptionItems')->toArray();

        DB::transaction(function () use ($data, $prescription) {
            $prescription->update([
                'patient_id' => $data['patient_id'],
                'prescriber_id' => $data['prescriber_id'],
                'issued_date' => $data['issued_date'],
                'status' => $data['status'],
            ]);

            $prescription->prescriptionItems()->delete();
            foreach ($data['rx_items'] as $item) {
                $prescription->prescriptionItems()->create([
                    'product_id' => $item['product_id'],
                    'dosage' => $item['dosage'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'prescription_updated',
            'auditable_id' => $prescription->id,
            'auditable_type' => Prescription::class,
            'old_values' => $oldValues,
            'new_values' => $prescription->fresh()->load('prescriptionItems')->toArray(),
        ]);

        return redirect()->route('prescriptions.show', $prescription)->with('success', 'Prescription updated.');
    }

    public function destroy(Prescription $prescription)
    {
        if ($prescription->sales()->exists()) {
            return back()->with('error', 'Cannot delete prescription already linked to sales.');
        }

        $oldValues = $prescription->load('prescriptionItems')->toArray();
        $prescription->prescriptionItems()->delete();
        $prescription->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'prescription_deleted',
            'auditable_id' => $prescription->id,
            'auditable_type' => Prescription::class,
            'old_values' => $oldValues,
            'new_values' => null,
        ]);

        return redirect()->route('prescriptions.index')->with('success', 'Prescription deleted.');
    }

    private function validatePrescription(Request $request): array
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'prescriber_id' => 'required|exists:prescribers,id',
            'issued_date' => 'required|date',
            'status' => 'required|in:active,completed,cancelled',
            'rx_items' => 'required|array|min:1',
            'rx_items.*.product_id' => 'required|exists:products,id',
            'rx_items.*.dosage' => 'required|string|max:255',
            'rx_items.*.quantity' => 'required|integer|min:1',
        ]);

        return $data;
    }
}
