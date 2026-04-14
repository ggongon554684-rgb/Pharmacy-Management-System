<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Edit Product</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card ui-surface">
                        <div class="card-body">
                            <form method="POST" action="{{ route('products.update', $product) }}">
                                @csrf @method('PATCH')
                                <div class="mb-3">
                                    <label class="form-label">Brand Name</label>
                                    <input name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Generic Name</label>
                                    <input name="generic_name" value="{{ old('generic_name', $product->generic_name) }}" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SKU</label>
                                    <input name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" required>
                                    @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Selling Price</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>