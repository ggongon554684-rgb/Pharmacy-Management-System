<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">{{ $prescriber->name }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="card module-surface mb-3">
                <div class="card-body">
                    <div><strong>License:</strong> {{ $prescriber->license_number }}</div>
                    <div><strong>Contact:</strong> {{ $prescriber->contact_info ?: '-' }}</div>
                </div>
            </div>
            <div class="card module-surface mb-3">
                <div class="card-header"><strong>Linked Prescriptions</strong></div>
                <div class="card-body">
                    <ul class="mb-0">
                        @forelse($prescriber->prescriptions as $prescription)
                            <li>
                                <a href="{{ route('prescriptions.show', $prescription) }}">RX #{{ $prescription->id }}</a>
                                - {{ $prescription->patient?->name }} ({{ ucfirst($prescription->status) }})
                            </li>
                        @empty
                            <li class="text-muted">No linked prescriptions.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('prescribers.edit', $prescriber) }}" class="btn btn-warning">Edit</a>
                <form method="POST" action="{{ route('prescribers.destroy', $prescriber) }}" onsubmit="return confirm('Delete this prescriber?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
                <a href="{{ route('prescribers.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
