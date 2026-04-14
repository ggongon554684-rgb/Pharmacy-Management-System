<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        h2 { margin: 0 0 6px; }
        .meta { margin-bottom: 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; }
        th { background: #e2e8f0; text-align: left; }
    </style>
</head>
<body>
    <h2>Inventory Report</h2>
    <div class="meta">
        <div>Month: {{ $month ?: 'All' }}</div>
        <div>Exported by: {{ $exportedBy }}</div>
        <div>Exported at: {{ $exportedAt->format('M d, Y H:i') }}</div>
    </div>
    <table>
        <thead>
            <tr><th>Product</th><th>SKU</th><th>Stock</th><th>Reorder Level</th></tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                @php $stock = $product->inventory_batches_sum_quantity ?? 0; @endphp
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $stock }}</td>
                    <td>{{ $product->reorder_level }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No products found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
