<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Admin Dashboard') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Operations Overview</h4>
                    <p class="admin-support-text mb-0">Track critical signals first, then open the sections that need action.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card admin-surface admin-kpi-card">
                        <div class="card-body">
                            <div class="admin-kpi-label mb-2"><i class="bi bi-graph-up-arrow admin-kpi-icon"></i>Sales Revenue</div>
                            <div class="admin-kpi-value">P{{ number_format($totalRevenue, 2) }}</div>
                            <div class="admin-support-text mt-1">Total released medicine value</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card admin-surface admin-kpi-card">
                        <div class="card-body">
                            <div class="admin-kpi-label mb-2"><i class="bi bi-bag-check admin-kpi-icon"></i>Purchase Cost</div>
                            <div class="admin-kpi-value">P{{ number_format($totalPurchaseCost, 2) }}</div>
                            <div class="admin-support-text mt-1">Total procurement spending</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card admin-surface admin-kpi-card">
                        <div class="card-body">
                            <div class="admin-kpi-label mb-2"><i class="bi bi-cash-stack admin-kpi-icon"></i>Gross Estimate</div>
                            <div class="admin-kpi-value">P{{ number_format($totalRevenue - $totalPurchaseCost, 2) }}</div>
                            <div class="admin-support-text mt-1">Revenue minus purchase cost</div>
                        </div>
                    </div>
                </div>
                @can('view products')
                <div class="col-md-3">
                    <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="admin-kpi-link" aria-label="Open low stock inventory">
                        <div class="card admin-surface admin-kpi-card">
                            <div class="card-body">
                                <div class="admin-kpi-label mb-2"><i class="bi bi-exclamation-triangle admin-kpi-icon"></i>Low Stock</div>
                                <div class="admin-kpi-value">{{ $lowStockCount }}</div>
                                <div class="admin-support-text mt-1">Products at or below reorder level</div>
                                <span class="btn btn-sm btn-outline-warning mt-2">Review Low Stock</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endcan
            </div>

            <div class="card admin-surface mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="admin-section-title mb-1">Financial Trend (Last 7 Days)</h5>
                            <p class="admin-support-text mb-0">Compare released medicine revenue against purchase spending.</p>
                        </div>
                        <span class="admin-pill">Sales vs Purchase Cost</span>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div
                        id="admin-financial-trend-chart"
                        class="admin-chart-wrap"
                        data-labels='@json($trendLabels ?? [])'
                        data-sales='@json($salesTrendSeries ?? [])'
                        data-purchase='@json($purchaseTrendSeries ?? [])'
                    ></div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="card admin-surface h-100 admin-chart-panel">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="admin-section-title mb-0"><i class="bi bi-truck me-2 text-primary"></i>Incoming Deliveries</h5>
                                <span class="admin-section-meta">With expected date</span>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <ul class="admin-list">
                                @forelse($incomingDeliveries as $delivery)
                                    @php
                                        $expectedDate = $delivery->expected_date;
                                        $today = now()->startOfDay();
                                        $statusClass = 'admin-date-none';
                                        $statusLabel = 'No date';

                                        if ($expectedDate) {
                                            $daysUntil = $today->diffInDays($expectedDate->copy()->startOfDay(), false);
                                            if ($daysUntil < 0) {
                                                $statusClass = 'admin-date-overdue';
                                                $statusLabel = 'Overdue';
                                            } elseif ($daysUntil <= 3) {
                                                $statusClass = 'admin-date-soon';
                                                $statusLabel = 'Due soon';
                                            } else {
                                                $statusClass = 'admin-date-upcoming';
                                                $statusLabel = 'Upcoming';
                                            }
                                        }
                                    @endphp
                                    <li>
                                        <div>
                                            <div class="admin-list-item-main">{{ $delivery->po_number }}</div>
                                            <div class="admin-list-item-sub">{{ ucfirst($delivery->status) }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="admin-list-item-sub mb-1">
                                                {{ optional($delivery->expected_date)->format('M d, Y') ?? 'No date' }}
                                            </div>
                                            <span class="admin-date-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>
                                    </li>
                                @empty
                                    <li><span class="admin-list-item-sub">No incoming deliveries scheduled.</span></li>
                                @endforelse
                            </ul>
                            @can('view incoming deliveries')
                                <a href="{{ route('purchase-orders.incoming') }}" class="btn btn-sm btn-outline-primary mt-2">Open Incoming Deliveries</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card admin-surface h-100 admin-chart-panel">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="admin-section-title mb-0"><i class="bi bi-heart-pulse me-2 text-primary"></i>Stock Health</h5>
                                <span class="admin-section-meta">Current inventory balance</span>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div
                                id="admin-stock-health-chart"
                                class="admin-chart-wrap-sm"
                                data-labels='@json($stockHealthLabels ?? [])'
                                data-series='@json($stockHealthSeries ?? [])'
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card admin-surface h-100 admin-chart-panel">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="admin-section-title mb-0"><i class="bi bi-lightning-charge me-2 text-primary"></i>Top Fast-Moving Medicines</h5>
                                <span class="admin-section-meta">By quantity sold</span>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div
                                id="admin-top-products-chart"
                                class="admin-chart-wrap-sm"
                                data-labels='@json($topMovingProductLabels ?? [])'
                                data-series='@json($topMovingProductSeries ?? [])'
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card admin-surface h-100">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="admin-section-title mb-0">Recent Sales</h5>
                                <span class="admin-section-meta">Latest 5 transactions</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="admin-list">
                                @forelse($recentSales as $sale)
                                    <li>
                                        <div>
                                            <div class="admin-list-item-main">#{{ $sale->id }} - {{ number_format($sale->total_amount, 2) }}</div>
                                            <div class="admin-list-item-sub">{{ $sale->patient->name ?? 'Walk-in' }}</div>
                                        </div>
                                        <div class="admin-list-item-sub">{{ $sale->created_at->format('M d, H:i') }}</div>
                                    </li>
                                @empty
                                    <li><span class="admin-list-item-sub">No sales yet.</span></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card admin-surface h-100">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="admin-section-title mb-0">Recent Purchase Costs</h5>
                                <span class="admin-section-meta">Latest 5 purchase orders</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="admin-list">
                                @forelse($recentPurchaseOrders as $po)
                                    <li>
                                        <div>
                                            <div class="admin-list-item-main">{{ $po->po_number }}</div>
                                            <div class="admin-list-item-sub">{{ ucfirst($po->status) }}</div>
                                        </div>
                                        <div class="admin-list-item-main">{{ number_format((float) $po->total_cost, 2) }}</div>
                                    </li>
                                @empty
                                    <li><span class="admin-list-item-sub">No purchase orders yet.</span></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card admin-surface">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="admin-section-title mb-0">Recent Activity</h5>
                        <span class="admin-section-meta">Most recent system changes</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 admin-activity-table">
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function () {
            const chartElement = document.getElementById('admin-financial-trend-chart');
            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const labels = JSON.parse(chartElement.dataset.labels || '[]');
            const salesSeries = JSON.parse(chartElement.dataset.sales || '[]');
            const purchaseSeries = JSON.parse(chartElement.dataset.purchase || '[]');

            const chart = new ApexCharts(chartElement, {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                series: [
                    { name: 'Sales Revenue', data: salesSeries },
                    { name: 'Purchase Cost', data: purchaseSeries }
                ],
                markers: {
                    size: 4,
                    strokeWidth: 0
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#2563eb', '#f59e0b'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.28,
                        opacityTo: 0.06,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: labels
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return 'P' + Number(value).toLocaleString();
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return 'P' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 3
                }
            });

            chart.render();

            const stockChartElement = document.getElementById('admin-stock-health-chart');
            if (stockChartElement) {
                const stockLabels = JSON.parse(stockChartElement.dataset.labels || '[]');
                const stockSeries = JSON.parse(stockChartElement.dataset.series || '[]');
                const stockChart = new ApexCharts(stockChartElement, {
                    chart: {
                        type: 'donut',
                        height: 280
                    },
                    labels: stockLabels,
                    series: stockSeries,
                    colors: ['#ef4444', '#10b981', '#6366f1'],
                    legend: {
                        position: 'bottom'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '72%'
                            }
                        }
                    },
                    stroke: {
                        width: 2,
                        colors: ['#ffffff']
                    },
                    dataLabels: {
                        enabled: true
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: { height: 260 }
                        }
                    }]
                });
                stockChart.render();
            }

            const topProductsChartElement = document.getElementById('admin-top-products-chart');
            if (topProductsChartElement) {
                const topLabels = JSON.parse(topProductsChartElement.dataset.labels || '[]');
                const topSeries = JSON.parse(topProductsChartElement.dataset.series || '[]');
                const topProductsChart = new ApexCharts(topProductsChartElement, {
                    chart: {
                        type: 'bar',
                        height: 280,
                        toolbar: { show: false }
                    },
                    series: [{
                        name: 'Qty Sold',
                        data: topSeries
                    }],
                    xaxis: {
                        categories: topLabels,
                        labels: {
                            rotate: -25
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            columnWidth: '55%'
                        }
                    },
                    colors: ['#0ea5e9'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            shadeIntensity: 0.4,
                            opacityFrom: 0.9,
                            opacityTo: 0.55,
                            stops: [0, 100]
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            colors: ['#334155']
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Units'
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 3
                    }
                });
                topProductsChart.render();
            }
        })();
    </script>
</x-app-layout>