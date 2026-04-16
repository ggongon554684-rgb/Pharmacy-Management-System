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
