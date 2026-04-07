<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Create Purchase Order</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('purchase-orders.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product</label>
                                <select class="form-select" name="product_id" required>
                                    <option value="">Select</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="1" name="quantity" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Unit Cost</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit PO</button>
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
