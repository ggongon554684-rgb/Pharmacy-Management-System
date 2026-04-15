<?php

namespace App\Http\Controllers;

use App\Models\Prescriber;
use Illuminate\Http\Request;

class PrescriberController extends Controller
{
    public function index()
    {
        $prescribers = Prescriber::latest()->paginate(15);
        return view('prescribers.index', compact('prescribers'));
    }

    public function create()
    {
        return view('prescribers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:100|unique:prescribers,license_number',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $prescriber = Prescriber::create($data);

        return redirect()->route('prescribers.show', $prescriber)
            ->with('success', 'Prescriber created.');
    }

    public function show(Prescriber $prescriber)
    {
        $prescriber->load('prescriptions.patient');
        return view('prescribers.show', compact('prescriber'));
    }

    public function edit(Prescriber $prescriber)
    {
        return view('prescribers.edit', compact('prescriber'));
    }

    public function update(Request $request, Prescriber $prescriber)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:100|unique:prescribers,license_number,' . $prescriber->id,
            'contact_info' => 'nullable|string|max:255',
        ]);

        $prescriber->update($data);

        return redirect()->route('prescribers.show', $prescriber)
            ->with('success', 'Prescriber updated.');
    }

    public function destroy(Prescriber $prescriber)
    {
        if ($prescriber->prescriptions()->exists()) {
            return back()->with('error', 'Cannot delete prescriber with linked prescriptions.');
        }

        $prescriber->delete();
        return redirect()->route('prescribers.index')->with('success', 'Prescriber deleted.');
    }
}
