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
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Reorder At</th>
                                    <th style="width: 240px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php $stock = $product->inventory_batches_sum_quantity ?? 0; @endphp
                                    <tr class="{{ $stock <= $product->reorder_level ? 'table-danger' : '' }}">
                                        <td>
                                            {{ $product->name }}
                                            @if($product->generic_name)
                                                <small class="text-muted">({{ $product->generic_name }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $product->sku }}</td>
                                        <td>P{{ number_format($product->price, 2) }}</td>
                                        <td>{{ $stock }}</td>
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
                                        <td colspan="6" class="text-center text-muted">No products yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>