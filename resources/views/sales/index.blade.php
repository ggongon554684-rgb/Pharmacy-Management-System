<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">POS / Sales</h2>
            @can('create sales')
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">Release Medicine</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="card ui-surface">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr><th>Date</th><th>Patient</th><th>Cashier</th><th>Total</th><th>Payment</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $sale->patient->name ?? 'Walk-in' }}</td>
                                        <td>{{ $sale->user->name ?? '-' }}</td>
                                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                                        <td>{{ ucfirst($sale->payment_method) }}</td>
                                        <td><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info">View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No sales yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
</x-app-layout>
