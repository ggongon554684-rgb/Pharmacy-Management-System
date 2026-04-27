<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Ticket #{{ $preOrder->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Print styles ───────────────────────────────────── */
        @media print {
            body * { visibility: hidden; }
            #printable-ticket,
            #printable-ticket * { visibility: visible; }
            #printable-ticket {
                position: absolute;
                inset: 0;
                margin: 0;
                padding: 1rem;
                width: 100%;
                max-width: 100%;
            }
            .no-print { display: none !important; }
        }

        /* ── Screen styles ──────────────────────────────────── */
        .ticket-token {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: .25em;
            color: #1d4ed8;
        }
        .qr-wrapper img {
            max-width: 280px;
            width: 100%;
        }
        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li {
            display: flex;
            justify-content: space-between;
            padding: .25rem 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: .875rem;
        }
        .item-list li:last-child { border-bottom: none; }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    {{-- ── Action bar (hidden on print) ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h2 class="h5 mb-0">Order Ticket #{{ $preOrder->id }}</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                🖨️ Print QR Ticket
            </button>
            <a href="{{ route('public.kiosk-order') }}" class="btn btn-outline-secondary">
                New Order
            </a>
        </div>
    </div>

    {{-- ── Printable ticket area ── --}}
    <div id="printable-ticket">
        <div class="card mx-auto" style="max-width: 640px;">
            <div class="card-body">

                {{-- Header --}}
                <div class="text-center mb-3">
                    <h3 class="h5 mb-1">Medicine Pre-Order Ticket</h3>
                    <div class="ticket-token">{{ $preOrder->scan_token }}</div>
                    <small class="text-muted">Show this QR code to the pharmacist / cashier</small>
                </div>

                <div class="row g-3">
                    {{-- QR code --}}
                    <div class="col-md-6 text-center qr-wrapper d-flex align-items-center justify-content-center">
                        <img
                            src="https://quickchart.io/qr?size=280&margin=2&text={{ urlencode($scanUrl) }}"
                            alt="Pre-order QR code"
                            class="border rounded p-2 bg-white"
                        >
                    </div>

                    {{-- Details --}}
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th style="width:90px">Ticket #</th><td>{{ $preOrder->id }}</td></tr>
                            <tr><th>Name</th><td>{{ $preOrder->customer_name ?? 'Walk-in' }}</td></tr>
                            <tr><th>Payment</th><td>{{ ucfirst($preOrder->payment_method) }}</td></tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($preOrder->isFulfilled())
                                        <span class="badge text-bg-success">Fulfilled</span>
                                    @else
                                        <span class="badge text-bg-warning">Pending Scan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th>Expires</th><td>{{ now()->addHours(24)->format('M d, Y g:i A') }}</td></tr>
                        </table>

                        <hr class="my-2">

                        <strong class="d-block mb-2">Items Ordered</strong>
                        <ul class="item-list">
                            @php $grandTotal = 0; @endphp
                            @foreach($preOrder->items as $item)
                                @php
                                    $subtotal    = $item->quantity * (float) $item->unit_price;
                                    $grandTotal += $subtotal;
                                @endphp
                                <li>
                                    <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                                    <span>P{{ number_format($subtotal, 2) }}</span>
                                </li>
                            @endforeach
                            <li class="fw-semibold pt-1">
                                <span>Total</span>
                                <span>P{{ number_format($grandTotal, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="alert alert-info mt-3 mb-0 small">
                    <strong>Instructions:</strong> Hand this ticket to the pharmacist or cashier.
                    They will scan the QR code to open your order. Payment is collected at the counter.
                    This ticket expires in 24 hours.
                </div>

            </div>
        </div>
    </div>
    {{-- end #printable-ticket --}}

    {{-- ── Bottom actions (screen only) ── --}}
    @if($preOrder->isFulfilled())
        <div class="text-center mt-3 no-print">
            <a href="{{ route('sales.show', $preOrder->sale_id) }}" class="btn btn-success">
                View Receipt
            </a>
        </div>
    @endif

</div>
</body>
</html>