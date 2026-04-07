<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Staff Dashboard') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row g-3 mb-4">
                @can('view inventory')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Inventory</h6>
                            <h3 class="mb-2">{{ $productCount }}</h3>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">Manage Stock</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('create purchase orders')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Purchase Orders</h6>
                            <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-outline-primary">Create PO</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('approve stock release')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Stock Requests</h6>
                            <a href="{{ route('stock-requests.index') }}" class="btn btn-sm btn-outline-primary">Approve Releases</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view reports')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Reports</h6>
                            <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-primary">Open Reports</a>
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Record ID</th>
                                    <th>User</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAudits as $audit)
                                    <tr>
                                        <td>{{ ucfirst($audit->action) }}</td>
                                        <td>{{ class_basename($audit->auditable_type) }}</td>
                                        <td>{{ $audit->auditable_id }}</td>
                                        <td>{{ optional($audit->user)->name ?? 'System' }}</td>
                                        <td>{{ $audit->created_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>