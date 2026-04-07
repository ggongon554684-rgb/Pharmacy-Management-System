<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">{{ $patient->name }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Birthdate:</strong> {{ $patient->birthdate->format('M d, Y') }}</div>
                        <div class="col-md-6"><strong>Contact:</strong> {{ $patient->contact_info }}</div>
                        <div class="col-12"><strong>Allergies:</strong> {{ $patient->allergies ?? 'None noted' }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header"><strong>Prescription History</strong></div>
                <div class="card-body">
                    @forelse($patient->prescriptions as $rx)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $rx->issued_date->format('M d, Y') }} - Dr. {{ $rx->prescriber->name }}</span>
                            <span class="badge {{ $rx->status === 'active' ? 'bg-success' : ($rx->status === 'completed' ? 'bg-primary' : 'bg-danger') }}">
                                {{ ucfirst($rx->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No prescriptions.</p>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</x-app-layout>