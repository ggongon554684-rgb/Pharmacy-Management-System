<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Prescriptions</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="d-flex justify-content-between mb-3">
                <div class="module-subtitle">Manage patient prescriptions and dispensation status.</div>
                <a href="{{ route('prescriptions.create') }}" class="btn btn-primary btn-sm">New Prescription</a>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover module-table mb-0">
                        <thead><tr><th>ID</th><th>Patient</th><th>Prescriber</th><th>Issued</th><th>Status</th><th>Items</th><th></th></tr></thead>
                        <tbody>
                            @forelse($prescriptions as $prescription)
                                <tr>
                                    <td>#{{ $prescription->id }}</td>
                                    <td>{{ $prescription->patient?->name }}</td>
                                    <td>{{ $prescription->prescriber?->name }}</td>
                                    <td>{{ $prescription->issued_date?->format('Y-m-d') }}</td>
                                    <td><span class="status-badge status-{{ $prescription->status === 'active' ? 'approved' : ($prescription->status === 'completed' ? 'fulfilled' : 'pending') }}">{{ ucfirst($prescription->status) }}</span></td>
                                    <td>{{ $prescription->prescriptionItems->count() }}</td>
                                    <td class="text-end"><a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-primary btn-sm">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No prescriptions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $prescriptions->links() }}</div>
        </div>
    </div>
</x-app-layout>
