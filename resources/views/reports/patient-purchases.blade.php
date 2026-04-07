<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Patient Purchase History</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Patient</th><th>Total</th><th>Items</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $sale->patient->name ?? 'Walk-in' }}</td>
                                    <td>{{ number_format($sale->total_amount, 2) }}</td>
                                    <td>{{ $sale->lineItems->count() }}</td>
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
