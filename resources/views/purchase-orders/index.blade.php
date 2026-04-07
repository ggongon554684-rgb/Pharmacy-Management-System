<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Purchase Orders</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @can('create purchase orders')
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm mb-3">Create PO</a>
            @endcan
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>PO #</th><th>Status</th><th>Expected</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $po->po_number }}</td>
                                    <td>
                                        <span class="badge {{
                                            $po->status === 'pending' ? 'bg-warning text-dark' :
                                            ($po->status === 'approved' ? 'bg-primary' :
                                            ($po->status === 'received' ? 'bg-success' : 'bg-secondary'))
                                        }}">
                                            {{ ucfirst($po->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $po->expected_date?->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-info">View</a>
                                        @can('approve purchase orders')
                                            @if($po->status === 'pending')
                                                <form method="POST" action="{{ route('purchase-orders.approve', $po) }}" class="d-inline">
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
