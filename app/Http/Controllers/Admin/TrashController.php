<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Prescriber;
use App\Models\StockRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage system');
    }

    public function index()
    {
        $trashed = [
            'products' => Product::onlyTrashed()->count(),
            'purchase_orders' => PurchaseOrder::onlyTrashed()->count(),
            'sales' => Sale::onlyTrashed()->count(),
            'inventory_batches' => InventoryBatch::onlyTrashed()->count(),
            'stock_movements' => StockMovement::onlyTrashed()->count(),
            'prescriptions' => Prescription::onlyTrashed()->count(),
            'patients' => Patient::onlyTrashed()->count(),
            'prescribers' => Prescriber::onlyTrashed()->count(),
            'stock_requests' => StockRequest::onlyTrashed()->count(),
        ];

        return view('admin.trash.index', compact('trashed'));
    }

    public function show(string $type)
    {
        $models = $this->trashModels();

        if (!isset($models[$type])) {
            abort(404);
        }

        $records = $models[$type]::onlyTrashed()->paginate(20);

        return view('admin.trash.show', compact('type', 'records'));
    }

    public function restore(Request $request, string $type, string $id)
    {
        $models = $this->trashModels();

        if (!isset($models[$type])) {
            abort(404);
        }

        $record = $models[$type]::onlyTrashed()->findOrFail($id);
        $record->restore();

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' restored successfully.');
    }

    public function forceDelete(Request $request, string $type, string $id)
    {
        $models = $this->trashModels();

        if (!isset($models[$type])) {
            abort(404);
        }

        $record = $models[$type]::onlyTrashed()->findOrFail($id);

        try {
            $record->forceDelete();
        } catch (QueryException $exception) {
            return redirect()->back()->with('error', ucfirst(str_replace('_', ' ', $type)) . ' could not be permanently deleted because related records still exist. Remove dependent records first.');
        }

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' permanently deleted.');
    }

    private function trashModels(): array
    {
        return [
            'products' => Product::class,
            'purchase_orders' => PurchaseOrder::class,
            'sales' => Sale::class,
            'inventory_batches' => InventoryBatch::class,
            'stock_movements' => StockMovement::class,
            'prescriptions' => Prescription::class,
            'patients' => Patient::class,
            'prescribers' => Prescriber::class,
            'stock_requests' => StockRequest::class,
        ];
    }
}
