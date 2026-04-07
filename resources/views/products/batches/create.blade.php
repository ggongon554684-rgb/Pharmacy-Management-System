<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Receive Stock - {{ $product->name }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('products.batches.store', $product) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Batch Number</label>
                                    <input name="batch_number" value="{{ old('batch_number') }}" class="form-control" required>
                                    @error('batch_number')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" class="form-control" required>
                                        @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cost Price</label>
                                        <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" min="0" class="form-control" required>
                                        @error('cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control" required>
                                    @error('expiry_date')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Add Batch</button>
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
