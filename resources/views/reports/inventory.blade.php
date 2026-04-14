<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Inventory Report</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card module-surface mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Report Month</label>
                            <input type="month" class="form-control" name="month" value="{{ $month }}">
                        </div>
                        <div class="col-md-9 module-actions">
                            <button class="btn btn-primary" type="submit">Apply</button>
                            <a class="btn btn-outline-secondary" href="{{ route('reports.inventory') }}">Reset</a>
                            <a class="btn btn-outline-dark" href="{{ route('reports.inventory.pdf', ['month' => $month]) }}">Export PDF</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover mb-0 module-table">
                        <thead>
                            <tr><th>Product</th><th>SKU</th><th>Stock</th><th>Reorder Level</th></tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php $stock = $product->inventory_batches_sum_quantity ?? 0; @endphp
                                <tr class="{{ $stock <= $product->reorder_level ? 'table-danger' : '' }}">
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $stock }}</td>
                                    <td>{{ $product->reorder_level }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No products found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
