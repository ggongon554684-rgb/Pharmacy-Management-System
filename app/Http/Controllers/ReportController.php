<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Prescription;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function inventory(Request $request)
    {
        $month = $request->query('month');
        $products = Product::withSum('inventoryBatches', 'quantity')
            ->orderBy('name')
            ->get();

        return view('reports.inventory', compact('products', 'month'));
    }

    public function patientPurchases(Request $request)
    {
        $month = $request->query('month');
        [$start, $end] = $this->resolveMonthRange($month);
        $sales = Sale::with('patient')
            ->withCount('lineItems')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->latest()
            ->paginate(20);

        return view('reports.patient-purchases', compact('sales', 'month'));
    }

    public function inventoryPdf(Request $request)
    {
        $month = $request->query('month');
        $products = Product::withSum('inventoryBatches', 'quantity')
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('reports.pdf.inventory', [
            'products' => $products,
            'month' => $month,
            'exportedBy' => auth()->user()->name,
            'exportedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileMonth = $month ?: now()->format('Y-m');
        return $pdf->download("inventory-report-{$fileMonth}.pdf");
    }

    public function patientPurchasesPdf(Request $request)
    {
        $month = $request->query('month');
        [$start, $end] = $this->resolveMonthRange($month);
        $sales = Sale::with('patient')
            ->withCount('lineItems')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('reports.pdf.patient-purchases', [
            'sales' => $sales,
            'month' => $month,
            'exportedBy' => auth()->user()->name,
            'exportedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileMonth = $month ?: now()->format('Y-m');
        return $pdf->download("patient-purchases-{$fileMonth}.pdf");
    }

    public function prescriptions(Request $request)
    {
        $status = $request->query('status');
        $prescriptions = Prescription::with(['patient', 'prescriber', 'prescriptionItems'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20);

        return view('reports.prescriptions', compact('prescriptions', 'status'));
    }

    private function resolveMonthRange(?string $month): array
    {
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return [null, null];
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        return [$start, $end];
    }
}
