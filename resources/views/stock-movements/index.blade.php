<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Stock Movements</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Reference</th><th>Notes</th></tr>
                        </thead>
                        <tbody>
                            @forelse($stockMovements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $movement->product->name ?? '-' }}</td>
                                    <td>{{ ucfirst($movement->type) }}</td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>{{ $movement->reference_type ?? '-' }} {{ $movement->reference_id ?? '' }}</td>
                                    <td>{{ $movement->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No stock movement records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $stockMovements->links() }}</div>
        </div>
    </div>
</x-app-layout>
