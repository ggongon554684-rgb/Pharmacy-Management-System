<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center page-header">
            <h2 class="h4 mb-0 page-title">Patients</h2>
            <div class="page-actions">
                <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">Add Patient</a>
            </div>
        </div>
    </x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Birthdate</th>
                                    <th>Contact</th>
                                    <th style="width: 220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patients as $patient)
                                    <tr>
                                        <td>{{ $patient->name }}</td>
                                        <td>{{ $patient->birthdate->format('M d, Y') }}</td>
                                        <td>{{ $patient->contact_info }}</td>
                                        <td>
                                            <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this patient?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No patients yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">{{ $patients->links() }}</div>
        </div>
    </div>
</x-app-layout>