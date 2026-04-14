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
    <h2>Patient Purchase History</h2>
    <div class="meta">
        <div>Month: {{ $month ?: 'All' }}</div>
        <div>Exported by: {{ $exportedBy }}</div>
        <div>Exported at: {{ $exportedAt->format('M d, Y H:i') }}</div>
    </div>
    <table>
        <thead>
            <tr><th>Date</th><th>Patient</th><th>Total</th><th>Items</th></tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ $sale->patient->name ?? 'Walk-in' }}</td>
                    <td>{{ number_format($sale->total_amount, 2) }}</td>
                    <td>{{ $sale->line_items_count }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
