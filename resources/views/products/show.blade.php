<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">{{ $product->name }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card ui-surface mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Generic Name:</strong> {{ $product->generic_name ?? '-' }}</div>
                        <div class="col-md-6"><strong>SKU:</strong> {{ $product->sku }}</div>
                        <div class="col-md-6"><strong>Selling Price:</strong> P{{ number_format($product->price, 2) }}</div>
                        <div class="col-md-6">
                            <strong>Total Stock:</strong>
                            <span class="{{ $lowStock ? 'text-danger' : 'text-success' }}">{{ $totalStock }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ui-surface mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Inventory Batches</strong>
                    <a href="{{ route('products.batches.create', $product) }}" class="btn btn-success btn-sm">Receive Stock</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Batch #</th>
                                    <th>Qty</th>
                                    <th>Cost Price</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->inventoryBatches->sortBy('expiry_date') as $batch)
                                    @php $expired = $batch->expiry_date->isPast(); @endphp
                                    <tr class="{{ $expired ? 'table-danger' : '' }}">
                                        <td>{{ $batch->batch_number }}</td>
                                        <td>{{ $batch->quantity }}</td>
                                        <td>P{{ number_format($batch->cost_price, 2) }}</td>
                                        <td>{{ $batch->expiry_date->format('M d, Y') }}</td>
                                        <td>
                                            @if($expired)
                                                <span class="badge bg-danger">Expired</span>
                                            @elseif($batch->expiry_date->diffInDays(now()) <= 30)
                                                <span class="badge bg-warning text-dark">Expiring Soon</span>
                                            @else
                                                <span class="badge bg-success">Good</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('products.batches.destroy', [$product, $batch]) }}" method="POST" onsubmit="return confirm('Remove this batch?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit">Remove</button>
                                            </form>
                                            @can('override stock')
                                                <form action="{{ route('inventory-batches.override', $batch) }}" method="POST" class="mt-1">
                                                    @csrf @method('PATCH')
                                                    <input type="number" min="0" name="new_quantity" class="form-control form-control-sm mb-1" placeholder="New qty" required>
                                                    <input type="password" name="admin_pin" class="form-control form-control-sm mb-1" placeholder="Admin PIN" required>
                                                    <button class="btn btn-sm btn-outline-dark" type="submit">Override</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No batches. Receive stock to get started.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit Product</a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</x-app-layout>