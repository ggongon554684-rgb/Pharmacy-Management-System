<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Ticket #{{ $preOrder->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card mx-auto" style="max-width: 640px;">
        <div class="card-body">
            <h3 class="h5 mb-1">Order Ticket Generated</h3>
            <p class="text-muted mb-3">Show this QR code to the pharmacist for scanning.</p>

            <div class="row g-3">
                <div class="col-md-6 text-center">
                    <img
                        src="https://quickchart.io/qr?size=280&text={{ urlencode($scanUrl) }}"
                        alt="Pre-order QR code"
                        class="img-fluid border rounded p-2 bg-white"
                    >
                </div>
                <div class="col-md-6">
                    <div><strong>Ticket #:</strong> {{ $preOrder->id }}</div>
                    <div><strong>Name:</strong> {{ $preOrder->customer_name ?? 'Walk-in' }}</div>
                    <div><strong>Payment:</strong> {{ ucfirst($preOrder->payment_method) }}</div>
                    <div><strong>Status:</strong> <span class="badge text-bg-warning">Pending Scan</span></div>
                    <hr>
                    <strong>Items</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($preOrder->items as $item)
                            <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="alert alert-info mt-3 mb-0">
                After pharmacist scan, transaction is created automatically and receipt will print.
            </div>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('public.kiosk-order') }}" class="btn btn-outline-secondary">Create New Order</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
