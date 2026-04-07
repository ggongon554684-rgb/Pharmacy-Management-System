<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Add Product</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('products.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Brand Name</label>
                                    <input name="name" value="{{ old('name') }}" class="form-control" required>
                                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Generic Name</label>
                                    <input name="generic_name" value="{{ old('generic_name') }}" class="form-control">
                                    @error('generic_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SKU</label>
                                    <input name="sku" value="{{ old('sku') }}" class="form-control" required>
                                    @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Selling Price</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price') }}" min="0" class="form-control" required>
                                        @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" value="{{ old('reorder_level', 0) }}" min="0" class="form-control" required>
                                        @error('reorder_level')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Create Product</button>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>