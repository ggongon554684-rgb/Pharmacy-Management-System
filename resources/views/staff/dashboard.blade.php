<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Staff Dashboard') }}</h2>
    </x-slot>

    <div class="py-4 staff-dashboard-page">
        <div class="container-fluid">
            <div class="dash">
                <div class="topbar">
                    <h1>Inventory Manager Dashboard</h1>
                    <span>Front Desk Staff · {{ now()->format('M d, Y') }}</span>
                </div>

                <div class="kpi-grid">
                    <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="kpi-link">
                        <div class="kpi">
                            <div class="kpi-label">Items needing reorder</div>
                            <div class="kpi-value" style="color:#A32D2D;">{{ $lowStockCount }}</div>
                            <div class="kpi-sub"><span class="badge-pill b-danger">Action required</span></div>
                        </div>
                    </a>
                    <div class="kpi">
                        <div class="kpi-label">Pending PO deliveries</div>
                        <div class="kpi-value" style="color:#854F0B;">{{ $pendingIncomingDeliveriesCount }}</div>
                        <div class="kpi-sub"><span class="badge-pill b-warn">Awaiting receipt</span></div>
                    </div>
                    <div class="kpi">
                        <div class="kpi-label">Back to Front requests</div>
                        <div class="kpi-value" style="color:#185FA5;">{{ $pendingStockRequestCount }}</div>
                        <div class="kpi-sub"><span class="badge-pill b-info">Needs approval</span></div>
                    </div>
                    <div class="kpi">
                        <div class="kpi-label">Total back-stock units</div>
                        <div class="kpi-value">{{ number_format($backInventoryStockUnits) }}</div>
                        <div class="kpi-sub"><span class="badge-pill b-ok">Front: {{ number_format($frontShopStockUnits) }} units</span></div>
                    </div>
                </div>

                <div class="row2">
                    <div class="card-soft">
                        <div class="card-hd">Stock level by product</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;">Current stock vs. reorder threshold</div>
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th style="width:38%;">Product</th>
                                    <th style="width:18%;">In stock</th>
                                    <th style="width:18%;">Threshold</th>
                                    <th style="width:26%;">Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $item)
                                    @php
                                        $stock = (int) ($item->inventory_batches_sum_quantity ?? 0);
                                        $threshold = max((int) $item->reorder_level, 1);
                                        $ratio = min(100, (int) round(($stock / $threshold) * 100));
                                        $isCritical = $stock <= (int) floor($threshold * 0.4);
                                        $levelLabel = $isCritical ? 'Critical' : 'Low';
                                    @endphp
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $stock }}</td>
                                        <td>{{ $item->reorder_level }}</td>
                                        <td>
                                            <div class="stock-bar-bg">
                                                <div class="stock-bar {{ $isCritical ? 'bar-critical' : 'bar-low' }}" data-width="{{ $ratio }}"></div>
                                            </div>
                                            <span class="{{ $isCritical ? 'level-critical' : 'level-low' }}" style="font-size:11px;">{{ $levelLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No low stock products right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-soft">
                        <div class="card-hd">Reorder alerts</div>
                        <div class="alert-list">
                            @forelse($lowStockItems as $item)
                                @php
                                    $stock = (int) ($item->inventory_batches_sum_quantity ?? 0);
                                    $isCritical = $stock <= (int) floor(max((int) $item->reorder_level, 1) * 0.4);
                                @endphp
                                <div class="alert-item {{ $isCritical ? 'urgent' : 'warn' }}">
                                    <div>
                                        <div class="alert-name">{{ $item->name }}</div>
                                        <div class="alert-meta">{{ $stock }} units left — threshold: {{ $item->reorder_level }}</div>
                                    </div>
                                    <span class="badge-pill {{ $isCritical ? 'b-danger' : 'b-warn' }}">{{ $isCritical ? 'Order now' : 'Order soon' }}</span>
                                </div>
                            @empty
                                <div class="alert-item">
                                    <div class="alert-meta">No reorder alerts right now.</div>
                                </div>
                            @endforelse
                            <div style="margin-top:4px;padding-top:10px;border-top:1px solid #e2e8f0;">
                                @can('create purchase orders')
                                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-outline-primary w-100">Create purchase orders</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row3">
                    <div class="card-soft" style="align-self: start;">
                        <div class="card-hd">Stock consumption trend — last 7 days</div>
                        <div class="legend-row" id="trend-legend"></div>
                        <div style="height:200px; max-height:200px; overflow:hidden;">
                            <canvas
                                id="trendChart"
                                data-labels='@json($trendLabels ?? [])'
                                data-datasets='@json($consumptionTrendDatasets ?? [])'
                                role="img"
                                aria-label="Line chart showing daily consumption over 7 days"
                            ></canvas>
                        </div>
                    </div>

                    <div class="card-soft">
                        <div class="card-hd">Pending transfers</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Back inventory to Front shop</div>
                        <table class="tbl">
                            <thead>
                                <tr><th>Product</th><th>Qty</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($pendingTransfers as $transfer)
                                    <tr>
                                        <td>{{ $transfer->product->name ?? 'Unknown' }}</td>
                                        <td>{{ $transfer->quantity }}</td>
                                        <td><span class="badge-pill b-warn">{{ ucfirst($transfer->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">No pending transfers.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #e2e8f0;">
                            <div class="card-hd" style="margin-bottom:10px;">Incoming deliveries</div>
                            @forelse($pendingIncomingDeliveries as $po)
                                <div style="padding:10px 12px;background:#f8fafc;border-radius:10px;margin-bottom:8px;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text-main);">{{ $po->po_number }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);margin-top:3px;">
                                        {{ $po->expected_date?->format('M d, Y') ?? 'No expected date' }} — {{ ucfirst($po->status) }}
                                    </div>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);">No pending purchase orders.</div>
                            @endforelse
                            <div style="margin-top:8px;"><span class="badge-pill b-info">{{ $pendingIncomingDeliveriesCount }} PO pending</span></div>
                        </div>
                    </div>
                </div>

                {{-- FRONT & BACK INVENTORY LEVELS --}}
                <div class="card-soft" style="margin-top:16px;">
                    <div class="card-hd">Front & Back Inventory Levels</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;">
                        Live stock per location across all products
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th style="width:35%;">Product</th>
                                <th style="width:15%;text-align:right;">Back Stock</th>
                                <th style="width:15%;text-align:right;">Front Stock</th>
                                <th style="width:15%;text-align:right;">Total</th>
                                <th style="width:20%;text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryLevels as $item)
                                @php
                                    $total      = $item->back_stock + $item->front_stock;
                                    $threshold  = max((int) $item->reorder_level, 1);
                                    $isCritical = $total <= floor($threshold * 0.4);
                                    $isLow      = !$isCritical && $total <= $threshold;
                                @endphp
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td style="text-align:right;">{{ number_format($item->back_stock) }}</td>
                                    <td style="text-align:right;">{{ number_format($item->front_stock) }}</td>
                                    <td style="text-align:right;font-weight:600;">{{ number_format($total) }}</td>
                                    <td style="text-align:center;">
                                        @if($isCritical)
                                            <span class="badge-pill b-danger">Critical</span>
                                        @elseif($isLow)
                                            <span class="badge-pill b-warn">Low</span>
                                        @else
                                            <span class="badge-pill b-ok">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No inventory data found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-soft">
                    <div class="card-hd">Recent inventory activity</div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th style="width:20%;">Action</th>
                                <th style="width:22%;">Entity</th>
                                <th style="width:12%;">Record</th>
                                <th style="width:20%;">By</th>
                                <th style="width:26%;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAudits as $audit)
                                <tr>
                                    <td>
                                        @php
                                            $action = strtolower((string) $audit->action);
                                            $badgeClass = str_contains($action, 'sale') ? 'b-ok' : (str_contains($action, 'po') || str_contains($action, 'purchase') ? 'b-warn' : 'b-info');
                                        @endphp
                                        <span class="badge-pill {{ $badgeClass }}">{{ ucfirst($audit->action) }}</span>
                                    </td>
                                    <td>{{ class_basename($audit->auditable_type) }}</td>
                                    <td>#{{ $audit->auditable_id }}</td>
                                    <td>{{ optional($audit->user)->name ?? 'System' }}</td>
                                    <td>{{ $audit->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No recent activity yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        (function () {
            document.querySelectorAll('.stock-bar[data-width]').forEach(function (bar) {
                const value = Number(bar.dataset.width || 0);
                bar.style.width = Math.max(0, Math.min(value, 100)) + '%';
            });

            const chartEl = document.getElementById('trendChart');
            if (!chartEl || typeof Chart === 'undefined') {
                return;
            }

            const labels = JSON.parse(chartEl.dataset.labels || '[]');
            const rawDatasets = JSON.parse(chartEl.dataset.datasets || '[]');
            const legend = document.getElementById('trend-legend');
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const gc = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)';
            const lc = isDark ? '#999' : '#64748b';

            if (legend) {
                legend.innerHTML = '';
                rawDatasets.forEach(function (dataset) {
                    const item = document.createElement('span');
                    item.innerHTML = '<span class="ld" style="background:' + dataset.borderColor + ';"></span>' + dataset.label;
                    legend.appendChild(item);
                });
            }

            const datasets = rawDatasets.map(function (dataset, index) {
                return {
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: dataset.borderColor,
                    backgroundColor: index === 0 ? 'rgba(55,138,221,0.07)' : 'transparent',
                    tension: 0.4,
                    fill: index === 0,
                    pointRadius: 3,
                    borderWidth: 2,
                    borderDash: index > 1 ? [4, 3] : undefined,
                };
            });

            new Chart(chartEl, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: lc, font: { size: 11 } }, grid: { color: gc } },
                        y: { ticks: { color: lc, font: { size: 11 }, callback: function (v) { return Math.round(v) + ' u'; } }, grid: { color: gc }, beginAtZero: true }
                    }
                }
            });
        })();
    </script>
</x-app-layout>