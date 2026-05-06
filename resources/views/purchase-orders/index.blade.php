<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Purchase Orders</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="module-title mb-1">Procurement Queue</h5>
                    <div class="module-subtitle">Track purchase order status and expected delivery dates.</div>
                </div>
                @can('create purchase orders')
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">Create PO</a>
                @endcan
            </div>
            <div class="card module-surface mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase-orders.index') }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ $search ?? '' }}" placeholder="Search by PO number or product name">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All</option>
                                <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="received" {{ ($status ?? '') === 'received' ? 'selected' : '' }}>Received</option>
                                <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover mb-0 module-table">
                        <thead>
                            <tr><th>PO #</th><th>Status</th><th>Expected</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $po->po_number }}</td>
                                    <td>
                                        @php
                                            $statusClass = $po->status === 'pending' ? 'status-pending' : ($po->status === 'approved' ? 'status-approved' : ($po->status === 'received' ? 'status-received' : 'status-pending'));
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($po->status) }}</span>
                                    </td>
                                    <td>{{ $po->expected_date?->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <div class="module-actions">
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @can('approve purchase orders')
                                                @if($po->status === 'pending')
                                                    <form method="POST" action="{{ route('purchase-orders.approve', $po) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('edit inventory')
                                                @if($po->status === 'approved')
                                                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-success">Receive</a>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No PO records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $purchaseOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
