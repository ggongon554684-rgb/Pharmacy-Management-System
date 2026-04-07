<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 text-dark mb-0">Audit Logs</h2>
            <small class="text-muted">Track CRUD activity and data changes</small>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Record ID</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditLogs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'updated' ? 'warning text-dark' : 'danger') }}">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td>{{ class_basename($log->auditable_type) }}</td>
                                        <td>{{ $log->auditable_id }}</td>
                                        <td>{{ optional($log->user)->name ?? 'System' }}</td>
                                        <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                        <td>
                                            <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No audit records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $auditLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
