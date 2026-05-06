<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AuditLog;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $stockStatus = $request->query('stock_status');
        $location = $request->query('location');
        $search = $request->query('search');

        $locationId = null;
        if (in_array($location, ['front', 'back'], true)) {
            $locationId = InventoryLocation::query()
                ->where('code', $location)
                ->value('id');
        }

        $products = Product::withSum('inventoryBatches', 'quantity')
            ->withSum([
                'inventoryBatches as front_stock' => fn ($query) => $query->forLocationCode('front'),
            ], 'quantity')
            ->withSum([
                'inventoryBatches as back_stock' => fn ($query) => $query->forLocationCode('back'),
            ], 'quantity')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%");
                });
            })
            ->when($stockStatus === 'low', function ($query) use ($locationId) {
                if ($locationId !== null) {
                    $query->whereRaw(
                        'COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id AND inventory_batches.location_id = ?), 0) <= reorder_level',
                        [$locationId]
                    );
                } else {
                    $query->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) <= reorder_level');
                }
            })
            ->when($stockStatus === 'normal', function ($query) {
                $query->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) > reorder_level');
            })
            ->latest()
            ->paginate(15);

        return view('products.index', compact('products', 'stockStatus', 'location', 'search'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'generic_name'  => 'nullable|string|max:255',
            'sku'           => 'required|string|max:100|unique:products,sku',
            'price'         => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $product = Product::create($data);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'created',
            'auditable_id'   => $product->id,
            'auditable_type' => Product::class,
            'old_values'     => null,
            'new_values'     => $product->toArray(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product added.');
    }

    public function show(Product $product)
    {
        $product->load('inventoryBatches.location');
        $totalStock = $product->inventoryBatches->sum('quantity');
        $frontStock = (int) $product->inventoryBatches->filter(fn ($batch) => $batch->location?->code === 'front')->sum('quantity');
        $backStock = (int) $product->inventoryBatches->filter(fn ($batch) => $batch->location?->code === 'back')->sum('quantity');
        $lowStock   = $totalStock <= $product->reorder_level;

        return view('products.show', compact('product', 'totalStock', 'lowStock', 'frontStock', 'backStock'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'generic_name'  => 'nullable|string|max:255',
            'sku'           => 'required|string|max:100|unique:products,sku,' . $product->id,
            'price'         => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $old = $product->toArray();
        $product->update($data);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'updated',
            'auditable_id'   => $product->id,
            'auditable_type' => Product::class,
            'old_values'     => $old,
            'new_values'     => $product->fresh()->toArray(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        // Guard: can't delete if batches exist (restrictOnDelete in migration)
        if ($product->inventoryBatches()->exists()) {
            return back()->with('error', 'Cannot delete product with existing inventory batches.');
        }

        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'deleted',
            'auditable_id'   => $product->id,
            'auditable_type' => Product::class,
            'old_values'     => $product->toArray(),
            'new_values'     => null,
        ]);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}