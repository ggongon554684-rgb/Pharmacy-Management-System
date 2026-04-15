<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Prescribers</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('prescribers.create') }}" class="btn btn-primary btn-sm">New Prescriber</a>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <table class="table table-hover module-table mb-0">
                        <thead><tr><th>Name</th><th>License</th><th>Contact</th><th></th></tr></thead>
                        <tbody>
                            @forelse($prescribers as $prescriber)
                                <tr>
                                    <td>{{ $prescriber->name }}</td>
                                    <td>{{ $prescriber->license_number }}</td>
                                    <td>{{ $prescriber->contact_info ?: '-' }}</td>
                                    <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="{{ route('prescribers.show', $prescriber) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No prescribers yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $prescribers->links() }}</div>
        </div>
    </div>
</x-app-layout>
