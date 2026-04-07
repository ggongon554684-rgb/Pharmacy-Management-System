<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Sale #{{ $sale->id }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</div>
                        <div class="col-md-4"><strong>Patient:</strong> {{ $sale->patient->name ?? 'Walk-in' }}</div>
                        <div class="col-md-4"><strong>Cashier:</strong> {{ $sale->user->name ?? '-' }}</div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4"><strong>Payment:</strong> {{ ucfirst($sale->payment_method) }}</div>
                        <div class="col-md-4"><strong>Total:</strong> {{ number_format($sale->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header"><strong>Line Items</strong></div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>Product</th><th>Batch</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @foreach($sale->lineItems as $line)
                                <tr>
                                    <td>{{ $line->inventoryBatch->product->name ?? '-' }}</td>
                                    <td>{{ $line->inventoryBatch->batch_number ?? '-' }}</td>
                                    <td>{{ $line->quantity }}</td>
                                    <td>{{ number_format($line->unit_price, 2) }}</td>
                                    <td>{{ number_format($line->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary mt-3">Back</a>
        </div>
    </div>
</x-app-layout>
