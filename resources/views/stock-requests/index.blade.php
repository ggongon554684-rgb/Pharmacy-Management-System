<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Stock Requests</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="module-title mb-1">Back to Front Requests</h5>
                    <div class="module-subtitle">Review and fulfill transfer requests for front-shop replenishment.</div>
                </div>
                @can('create stock requests')
                    <a href="{{ route('stock-requests.create') }}" class="btn btn-primary btn-sm">Request Medicine</a>
                @endcan
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover mb-0 module-table">
                        <thead>
                            <tr><th>Product</th><th>Requested</th><th>Approved</th><th>Front Shop</th><th>Back Inventory</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($stockRequests as $request)
                                @php
                                    $frontStock = (int) ($frontStocksByProduct[$request->product_id] ?? 0);
                                    $backStock = (int) ($backStocksByProduct[$request->product_id] ?? 0);
                                    $requestedQty = (int) ($request->requested_quantity ?: $request->quantity);
                                    $approvedQty = (int) ($request->approved_quantity ?: 0);
                                    $warnBackForRequested = $backStock < $requestedQty;
                                    $warnBackForApproved = $approvedQty > 0 && $backStock < $approvedQty;
                                @endphp
                                <tr class="{{ $warnBackForRequested || $warnBackForApproved ? 'approval-warning-cell' : '' }}">
                                    <td>{{ $request->product->name }}</td>
                                    <td>{{ $requestedQty }}</td>
                                    <td>{{ $approvedQty ?: '-' }}</td>
                                    <td><span class="status-badge status-approved">{{ $frontStock }}</span></td>
                                    <td>
                                        <span class="status-badge {{ $warnBackForRequested || $warnBackForApproved ? 'status-danger' : 'status-received' }}">{{ $backStock }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = $request->status === 'pending' ? 'status-pending' : ($request->status === 'fulfilled' ? 'status-fulfilled' : ($request->status === 'approved' ? 'status-approved' : 'status-pending'));
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($request->status) }}</span>
                                    </td>
                                    <td>
                                        @can('approve stock release')
                                            @if($request->status === 'pending')
                                                <form method="POST" action="{{ route('stock-requests.approve', $request) }}" class="module-actions">
                                                    @csrf
                                                    <input
                                                        type="number"
                                                        name="approved_quantity"
                                                        min="1"
                                                        max="{{ $requestedQty }}"
                                                        value="{{ $requestedQty }}"
                                                        class="form-control form-control-sm approval-qty-input"
                                                        aria-label="Approved quantity"
                                                        required
                                                    >
                                                    <input
                                                        type="text"
                                                        name="adjustment_reason"
                                                        class="form-control form-control-sm"
                                                        placeholder="Reason if adjusted"
                                                        aria-label="Adjustment reason"
                                                    >
                                                    <button type="submit" class="btn btn-success btn-sm">Approve & Fulfill</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $stockRequests->links() }}</div>
        </div>
    </div>
</x-app-layout>
