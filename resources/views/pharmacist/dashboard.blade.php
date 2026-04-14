<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Pharmacist Dashboard') }}</h2>
    </x-slot>

    <style>
        .pharm-card { border-radius: 14px; }
        .pharm-label {
            color: var(--text-muted);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            font-weight: 600;
        }
        .pharm-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }
        .pharm-icon { color: var(--brand-primary); margin-right: 0.35rem; }
        .pharm-table thead th {
            background: var(--text-main);
            color: #f8fafc;
            border: 0;
        }
    </style>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card ui-surface pharm-card mb-3">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                    <div>
                        <h5 class="mb-1 ui-section-title">Front-Shop Queue Mode</h5>
                        <p class="text-muted mb-0">Optimized quick release workflow for busy counter operations.</p>
                    </div>
                    <div class="d-flex gap-2">
                        @can('create sales')
                            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">Quick Release</a>
                        @endcan
                        <a href="{{ route('public.kiosk-order') }}" target="_blank" class="btn btn-outline-primary btn-sm">Open Kiosk Ordering</a>
                        @can('view sales')
                            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">View Transactions</a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card ui-surface pharm-card h-100">
                        <div class="card-body">
                            <div class="pharm-label mb-2"><i class="bi bi-lightning-charge pharm-icon"></i>Releases Today</div>
                            <div class="pharm-value">{{ $mySalesTodayCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ui-surface pharm-card h-100">
                        <div class="card-body">
                            <div class="pharm-label mb-2"><i class="bi bi-cash-stack pharm-icon"></i>Sales Today</div>
                            <div class="pharm-value">P{{ number_format($mySalesTodayTotal, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ui-surface pharm-card h-100">
                        <div class="card-body">
                            <div class="pharm-label mb-2"><i class="bi bi-exclamation-triangle pharm-icon"></i>Low Stock Alerts</div>
                            <div class="pharm-value">{{ $lowStockCount }}</div>
                            <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-outline-warning mt-2">Review</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ui-surface pharm-card h-100">
                        <div class="card-body">
                            <div class="pharm-label mb-2"><i class="bi bi-box-arrow-in-down pharm-icon"></i>Pending Requests</div>
                            <div class="pharm-value">{{ $myPendingStockRequests }}</div>
                            @can('create stock requests')
                                <a href="{{ route('stock-requests.index') }}" class="btn btn-sm btn-outline-primary mt-2">Open Requests</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ui-surface pharm-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 ui-section-title">My Recent Transactions</h5>
                        <small class="text-muted">Latest 6 releases</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 pharm-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRecentSales as $sale)
                                    <tr>
                                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $sale->patient->name ?? 'Walk-in' }}</td>
                                        <td>P{{ number_format($sale->total_amount, 2) }}</td>
                                        <td>{{ ucfirst($sale->payment_method) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent transactions yet.</td>
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
