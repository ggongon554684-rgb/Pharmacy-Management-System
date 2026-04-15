<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Prescription Report</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card module-surface">
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All status</option>
                                @foreach(['active', 'completed', 'cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
                    </form>
                    <table class="table module-table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Patient</th><th>Prescriber</th><th>Status</th><th>Items</th><th>Issued</th></tr></thead>
                        <tbody>
                            @forelse($prescriptions as $prescription)
                                <tr>
                                    <td>#{{ $prescription->id }}</td>
                                    <td>{{ $prescription->patient?->name }}</td>
                                    <td>{{ $prescription->prescriber?->name }}</td>
                                    <td>{{ ucfirst($prescription->status) }}</td>
                                    <td>{{ $prescription->prescriptionItems->count() }}</td>
                                    <td>{{ $prescription->issued_date?->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No prescriptions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">{{ $prescriptions->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
