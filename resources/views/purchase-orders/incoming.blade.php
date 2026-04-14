<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Incoming Deliveries</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="mb-3">
                <h5 class="module-title mb-1">Incoming Deliveries</h5>
                <div class="module-subtitle">Monitor approved purchase orders waiting for receiving.</div>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover align-middle mb-0 module-table">
                        <thead>
                            <tr>
                                <th>PO #</th>
                                <th>Status</th>
                                <th>Expected Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $po->po_number }}</td>
                                    <td>
                                        <span class="status-badge {{ $po->status === 'received' ? 'status-received' : 'status-pending' }}">{{ ucfirst($po->status) }}</span>
                                    </td>
                                    <td>{{ $po->expected_date?->format('M d, Y') ?? '-' }}</td>
                                    <td><a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No incoming deliveries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $purchaseOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
