<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Request Stock</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="mb-3">
                <h5 class="module-title mb-1">Create Replenishment Request</h5>
                <div class="module-subtitle">Send a front-shop refill request to back inventory staff.</div>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('stock-requests.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" min="1" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Submit Request</button>
                        <a href="{{ route('stock-requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
