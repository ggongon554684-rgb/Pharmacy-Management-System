<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Sale #{{ $sale->id }}</h2></x-slot>

    <style>
        /* ── Screen styles ── */
        .receipt-wrapper {
            max-width: 860px;
            margin: 0 auto;
        }

        .receipt-card {
            background: #fff;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(15,23,42,0.08);
            overflow: hidden;
        }

        .receipt-header {
            background: var(--brand-primary);
            color: #fff;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .receipt-header .rx-title {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .receipt-header .rx-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 0.3rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .receipt-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border-bottom: 1px solid var(--border-soft);
        }

        .receipt-meta-cell {
            padding: 1rem 1.5rem;
            border-right: 1px solid var(--border-soft);
        }

        .receipt-meta-cell:last-child { border-right: none; }
        .receipt-meta-cell:nth-child(4),
        .receipt-meta-cell:nth-child(5),
        .receipt-meta-cell:nth-child(6) {
            border-top: 1px solid var(--border-soft);
        }

        .receipt-meta-cell .label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }

        .receipt-meta-cell .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .receipt-table-wrap {
            padding: 0;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-table thead th {
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.75rem 1.5rem;
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
            text-align: left;
        }

        .receipt-table thead th:not(:first-child) { text-align: right; }

        .receipt-table tbody td {
            padding: 0.85rem 1.5rem;
            font-size: 0.92rem;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .receipt-table tbody td:not(:first-child) { text-align: right; }

        .receipt-table tbody tr:last-child td { border-bottom: none; }

        .receipt-table .product-name { font-weight: 600; }
        .receipt-table .batch-chip {
            display: inline-block;
            background: var(--brand-primary-soft);
            color: var(--brand-primary);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 5px;
        }

        .receipt-total {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2rem;
            padding: 1.2rem 1.5rem;
            background: #f8fafc;
            border-top: 2px solid var(--border-soft);
        }

        .receipt-total .total-label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .receipt-total .total-amount {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -0.03em;
        }

        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body, html { margin: 0; padding: 0; background: #fff !important; }
            .app-header, .app-footer, .app-side-nav, .side-nav-menu, .side-nav-link, .navbar, .no-print { display: none !important; }
            .app-main { display: block !important; margin-left: 0 !important; padding: 0 !important; }
            .py-4 { padding: 0 !important; }
            .container-fluid { padding: 0 !important; }
            .receipt-wrapper { max-width: 100% !important; margin: 0 !important; }
            .receipt-card { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
            .receipt-header { background: #1d4ed8 !important; color: #fff !important; padding: 1rem 1.5rem !important; }
            .receipt-header .rx-badge { background: rgba(255,255,255,0.25) !important; }
            .receipt-meta { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; }
            .receipt-meta-cell { padding: 0.65rem 1rem !important; }
            .receipt-table thead th { background: #f4f4f4 !important; padding: 0.5rem 1rem !important; }
            .receipt-table tbody td { padding: 0.5rem 1rem !important; border-bottom: 1px solid #ddd !important; }
            .receipt-table .batch-chip { background: #dbeafe !important; color: #1d4ed8 !important; }
            .receipt-total { background: #f4f4f4 !important; padding: 0.75rem 1rem !important; }
            .receipt-total .total-amount { color: #1d4ed8 !important; }
        }
    </style>

    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success no-print">{{ session('success') }}</div>
            @endif

            <div class="mb-3 d-flex gap-2 align-items-center no-print">
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <button type="button" class="btn btn-sm btn-primary ms-auto" id="print-receipt-btn">
                    <i class="bi bi-printer me-1"></i>Print Receipt
                </button>
            </div>

            <div class="receipt-wrapper receipt-printable">
                <div class="receipt-card">
                    <div class="receipt-header">
                        <div>
                            <p class="rx-title">
                                <i class="bi bi-file-earmark-medical me-2"></i>Official Receipt
                            </p>
                            <div style="font-size:0.82rem;opacity:0.8;margin-top:0.2rem;">
                                {{ config('app.name', 'PharmaCare') }}
                            </div>
                        </div>
                        <div class="rx-badge">SALE #{{ $sale->id }}</div>
                    </div>

                    <div class="receipt-meta">
                        <div class="receipt-meta-cell">
                            <div class="label">Date & Time</div>
                            <div class="value">{{ $sale->created_at->format('M d, Y') }}</div>
                            <div style="font-size:0.8rem;color:var(--text-muted);">{{ $sale->created_at->format('h:i A') }}</div>
                        </div>
                        <div class="receipt-meta-cell">
                            <div class="label">Patient</div>
                            <div class="value">{{ $sale->patient->name ?? 'Walk-in' }}</div>
                        </div>
                        <div class="receipt-meta-cell">
                            <div class="label">Cashier</div>
                            <div class="value">{{ $sale->user->name ?? '—' }}</div>
                        </div>
                        <div class="receipt-meta-cell">
                            <div class="label">Payment Method</div>
                            <div class="value">{{ ucfirst($sale->payment_method) }}</div>
                        </div>
                        @if((float) $sale->payment_tendered > 0)
                            <div class="receipt-meta-cell">
                                <div class="label">Tendered</div>
                                <div class="value">₱{{ number_format((float) $sale->payment_tendered, 2) }}</div>
                            </div>
                        @endif
                        @if((float) $sale->payment_change_due > 0)
                            <div class="receipt-meta-cell">
                                <div class="label">Change Due</div>
                                <div class="value">₱{{ number_format((float) $sale->payment_change_due, 2) }}</div>
                            </div>
                        @endif
                        @if($sale->payment_reference)
                            <div class="receipt-meta-cell">
                                <div class="label">Payment Reference</div>
                                <div class="value">{{ $sale->payment_reference }}</div>
                            </div>
                        @endif
                        @if($sale->insurance_provider || $sale->insurance_policy_number)
                            <div class="receipt-meta-cell">
                                <div class="label">Insurance</div>
                                <div class="value">
                                    {{ $sale->insurance_provider ?? 'Insurance' }}<br>
                                    {{ $sale->insurance_policy_number ?? '' }}
                                </div>
                            </div>
                        @endif
                        <div class="receipt-meta-cell">
                            <div class="label">Prescription</div>
                            <div class="value">
                                @if($sale->prescription_id)
                                    <span style="color:var(--brand-health);">RX #{{ $sale->prescription_id }}</span>
                                @else
                                    <span style="color:var(--text-muted);font-weight:400;">None</span>
                                @endif
                            </div>
                        </div>
                        <div class="receipt-meta-cell">
                            <div class="label">Items</div>
                            <div class="value">{{ $sale->lineItems->count() }} item(s)</div>
                        </div>
                    </div>

                    <div class="receipt-table-wrap">
                        <table class="receipt-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->lineItems as $line)
                                    <tr>
                                        <td class="product-name">{{ $line->inventoryBatch->product->name ?? '—' }}</td>
                                        <td>
                                            <span class="batch-chip">{{ $line->inventoryBatch->batch_number ?? '—' }}</span>
                                        </td>
                                        <td>{{ $line->quantity }}</td>
                                        <td>₱{{ number_format($line->unit_price, 2) }}</td>
                                        <td style="font-weight:600;">₱{{ number_format($line->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="receipt-total">
                        <div class="total-label">Total Amount Due</div>
                        <div class="total-amount">₱{{ number_format($sale->total_amount, 2) }}</div>
                    </div>
                </div>

                <div style="text-align:center;font-size:0.75rem;color:var(--text-muted);margin-top:0.75rem;" class="no-print">
                    This is an official receipt. Please keep for your records.
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const printBtn = document.getElementById('print-receipt-btn');
            if (printBtn) {
                printBtn.addEventListener('click', function () { window.print(); });
            }
            if (new URLSearchParams(window.location.search).get('print') === '1') {
                setTimeout(function () { window.print(); }, 350);
            }
        })();
    </script>
</x-app-layout>
