<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::latest()->paginate(15);
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'birthdate'    => 'required|date|before:today',
            'contact_info' => 'required|string|max:255',
            'allergies'    => 'nullable|string',
        ]);

        $patient = Patient::create($data);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'created',
            'auditable_id'   => $patient->id,
            'auditable_type' => Patient::class,
            'old_values'     => null,
            'new_values'     => $patient->toArray(),
        ]);

        return redirect()->route('patients.index')->with('success', 'Patient added.');
    }

    public function show(Patient $patient)
    {
        $patient->load('prescriptions.prescriber');
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'birthdate'    => 'required|date|before:today',
            'contact_info' => 'required|string|max:255',
            'allergies'    => 'nullable|string',
        ]);

        $old = $patient->toArray();
        $patient->update($data);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'updated',
            'auditable_id'   => $patient->id,
            'auditable_type' => Patient::class,
            'old_values'     => $old,
            'new_values'     => $patient->fresh()->toArray(),
        ]);

        return redirect()->route('patients.show', $patient)->with('success', 'Patient updated.');
    }

    public function destroy(Patient $patient)
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'deleted',
            'auditable_id'   => $patient->id,
            'auditable_type' => Patient::class,
            'old_values'     => $patient->toArray(),
            'new_values'     => null,
        ]);

        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient deleted.');
    }
}