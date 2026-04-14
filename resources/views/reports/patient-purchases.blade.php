<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Patient Purchase History</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card module-surface mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.patient-purchases') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Report Month</label>
                            <input type="month" class="form-control" name="month" value="{{ $month }}">
                        </div>
                        <div class="col-md-9 module-actions">
                            <button class="btn btn-primary" type="submit">Apply</button>
                            <a class="btn btn-outline-secondary" href="{{ route('reports.patient-purchases') }}">Reset</a>
                            <a class="btn btn-outline-dark" href="{{ route('reports.patient-purchases.pdf', ['month' => $month]) }}">Export PDF</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover mb-0 module-table">
                        <thead>
                            <tr><th>Date</th><th>Patient</th><th>Total</th><th>Items</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $sale->patient->name ?? 'Walk-in' }}</td>
                                    <td>{{ number_format($sale->total_amount, 2) }}</td>
                                    <td>{{ $sale->line_items_count }}</td>
                                    <td>
                                        @if($sale->patient)
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('patients.show', $sale->patient) }}">Patient</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No patient purchases found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
</x-app-layout>
