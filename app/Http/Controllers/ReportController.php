<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class ReportController extends Controller
{
    public function inventory()
    {
        $products = Product::withSum('inventoryBatches', 'quantity')
            ->orderBy('name')
            ->get();

        return view('reports.inventory', compact('products'));
    }

    public function patientPurchases()
    {
        $sales = Sale::with(['patient', 'lineItems.inventoryBatch.product'])
            ->latest()
            ->paginate(20);

        return view('reports.patient-purchases', compact('sales'));
    }
}
