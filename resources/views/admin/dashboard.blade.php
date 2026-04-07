<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Admin Dashboard') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row g-3 mb-4">
                @can('view patients')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Patients</h6>
                            <h3 class="mb-2">{{ $patientCount }}</h3>
                            <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-primary">Manage Patients</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view products')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Products</h6>
                            <h3 class="mb-2">{{ $productCount }}</h3>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">Manage Products</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view purchase orders')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Purchase Orders</h6>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-primary">Review POs</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view incoming deliveries')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Incoming Deliveries</h6>
                            <a href="{{ route('purchase-orders.incoming') }}" class="btn btn-sm btn-outline-primary">View Incoming</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view stock movements')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Stock Movements</h6>
                            <a href="{{ route('stock-movements.index') }}" class="btn btn-sm btn-outline-primary">View Movement Log</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view audit logs')
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Audit Records</h6>
                            <h3 class="mb-2">{{ $auditCount }}</h3>
                            <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-primary">View Audit Logs</a>
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