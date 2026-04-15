<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Prescription #{{ $prescription->id }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="card module-surface mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><strong>Patient:</strong> {{ $prescription->patient?->name }}</div>
                        <div class="col-md-3"><strong>Prescriber:</strong> {{ $prescription->prescriber?->name }}</div>
                        <div class="col-md-3"><strong>Issued:</strong> {{ $prescription->issued_date?->format('Y-m-d') }}</div>
                        <div class="col-md-3"><strong>Status:</strong> {{ ucfirst($prescription->status) }}</div>
                    </div>
                </div>
            </div>
            <div class="card module-surface">
                <div class="card-header"><strong>RX Items</strong></div>
                <div class="card-body">
                    <table class="table table-hover module-table mb-0">
                        <thead><tr><th>Product</th><th>Dosage</th><th>Prescribed</th><th>Remaining</th></tr></thead>
                        <tbody>
                            @foreach($prescription->prescriptionItems as $item)
                                <tr>
                                    <td>{{ $item->product?->name }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $remainingByProduct[$item->product_id] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-warning">Edit</a>
                <form method="POST" action="{{ route('prescriptions.destroy', $prescription) }}" onsubmit="return confirm('Delete this prescription?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
                <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
