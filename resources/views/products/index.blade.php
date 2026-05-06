<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Products</h2>
            @can('create products')
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add Product</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(($stockStatus ?? null) === 'low')
                @php
                    $locationLabel = 'stock';
                    if (request('location') === 'front') {
                        $locationLabel = 'front-shop stock';
                    } elseif (request('location') === 'back') {
                        $locationLabel = 'back inventory stock';
                    }
                @endphp
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <span>Showing products with low {{ $locationLabel }} only.</span>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-dark">Clear filter</a>
                </div>
            @endif

            <!-- Search and Filters -->
            <div class="card ui-surface mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ $search ?? '' }}" placeholder="Search by name, SKU, or generic name">
                        </div>
                        <div class="col-md-3">
                            <label for="stock_status" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="">All</option>
                                <option value="low" {{ ($stockStatus ?? '') === 'low' ? 'selected' : '' }}>Low Stock</option>
                                <option value="normal" {{ ($stockStatus ?? '') === 'normal' ? 'selected' : '' }}>Normal Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">Location</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">All</option>
                                <option value="front" {{ ($location ?? '') === 'front' ? 'selected' : '' }}>Front Shop</option>
                                <option value="back" {{ ($location ?? '') === 'back' ? 'selected' : '' }}>Back Inventory</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card ui-surface">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Total Stock</th>
                                    <th>Front Shop</th>
                                    <th>Back Inventory</th>
                                    <th>Reorder At</th>
                                    <th style="width: 240px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $stock = $product->inventory_batches_sum_quantity ?? 0;
                                        $frontStock = $product->front_stock ?? 0;
                                        $backStock = $product->back_stock ?? 0;
                                        $highlightStock = $stock;
                                        if (($stockStatus ?? null) === 'low' && request('location') === 'front') {
                                            $highlightStock = $frontStock;
                                        } elseif (($stockStatus ?? null) === 'low' && request('location') === 'back') {
                                            $highlightStock = $backStock;
                                        }
                                    @endphp
                                    <tr class="{{ $highlightStock <= $product->reorder_level ? 'table-danger' : '' }}">
                                        <td>
                                            {{ $product->name }}
                                            @if($product->generic_name)
                                                <small class="text-muted">({{ $product->generic_name }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $product->sku }}</td>
                                        <td>P{{ number_format($product->price, 2) }}</td>
                                        <td>{{ $stock }}</td>
                                        <td><span class="badge text-bg-primary">{{ $frontStock }}</span></td>
                                        <td><span class="badge text-bg-secondary">{{ $backStock }}</span></td>
                                        <td>{{ $product->reorder_level }}</td>
                                        <td>
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                                            @can('edit products')
                                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @endcan
                                            @can('delete products')
                                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No products yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">{{ $products->appends(request()->query())->links() }}</div>
        </div>
    </div>
</x-app-layout>