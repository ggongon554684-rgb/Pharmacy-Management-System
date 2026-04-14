<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Dashboard') }}</h2>
    </x-slot>

    <style>
        .dash-card {
            border-radius: 14px;
        }

        .dash-title {
            color: var(--text-muted);
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            font-weight: 600;
        }

        .dash-icon {
            color: var(--brand-primary);
            margin-right: 0.35rem;
        }

        .dash-table thead th {
            background: var(--text-main);
            color: #f8fafc;
            border: 0;
        }
    </style>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card ui-surface dash-card mb-3">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-1 ui-section-title">Pharmacist Workspace</h5>
                        <p class="text-muted mb-0">Use quick release for queue handling and open kiosk page for customer ordering.</p>
                    </div>
                    <div class="d-flex gap-2">
                        @can('create sales')
                            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">Quick Release</a>
                        @endcan
                        <a href="{{ route('public.kiosk-order') }}" target="_blank" class="btn btn-outline-primary btn-sm">Open Kiosk Page</a>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                @can('view patients')
                <div class="col-md-3">
                    <div class="card ui-surface dash-card">
                        <div class="card-body">
                            <h6 class="dash-title"><i class="bi bi-people dash-icon"></i>Patients</h6>
                            <h3 class="mb-2">{{ $patientCount }}</h3>
                            <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-primary">Manage Patients</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view products')
                <div class="col-md-3">
                    <div class="card ui-surface dash-card">
                        <div class="card-body">
                            <h6 class="dash-title"><i class="bi bi-capsule-pill dash-icon"></i>Products</h6>
                            <h3 class="mb-2">{{ $productCount }}</h3>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">Manage Products</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('create stock requests')
                <div class="col-md-3">
                    <div class="card ui-surface dash-card">
                        <div class="card-body">
                            <h6 class="dash-title"><i class="bi bi-box-arrow-in-down dash-icon"></i>Stock Requests</h6>
                            <a href="{{ route('stock-requests.create') }}" class="btn btn-sm btn-outline-primary">Request Medicines</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('create sales')
                <div class="col-md-3">
                    <div class="card ui-surface dash-card">
                        <div class="card-body">
                            <h6 class="dash-title"><i class="bi bi-cart-check dash-icon"></i>POS</h6>
                            <a href="{{ route('sales.create') }}" class="btn btn-sm btn-outline-primary">Release Medicine</a>
                        </div>
                    </div>
                </div>
                @endcan
                @can('view reports')
                <div class="col-md-3">
                    <div class="card ui-surface dash-card">
                        <div class="card-body">
                            <h6 class="dash-title"><i class="bi bi-bar-chart dash-icon"></i>Reports</h6>
                            <a href="{{ route('reports.patient-purchases') }}" class="btn btn-sm btn-outline-primary">Patient History</a>
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            <div class="card ui-surface dash-card">
                <div class="card-header">
                    <h5 class="mb-0 ui-section-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 dash-table">
                            <thead>
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
