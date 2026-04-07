<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 text-dark mb-0">Audit Record #{{ $auditLog->id }}</h2>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Back to Audit Logs</a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Action:</strong> {{ ucfirst($auditLog->action) }}</p>
                            <p><strong>Entity:</strong> {{ class_basename($auditLog->auditable_type) }}</p>
                            <p><strong>Record ID:</strong> {{ $auditLog->auditable_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>User:</strong> {{ optional($auditLog->user)->name ?? 'System' }}</p>
                            <p><strong>Timestamp:</strong> {{ $auditLog->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Old Values</h5>
                </div>
                <div class="card-body">
                    @if(!empty($auditLog->old_values))
                        <pre class="mb-0 p-3 bg-light rounded">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <p class="text-muted mb-0">No old values captured.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">New Values</h5>
                </div>
                <div class="card-body">
                    @if(!empty($auditLog->new_values))
                        <pre class="mb-0 p-3 bg-light rounded">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <p class="text-muted mb-0">No new values captured.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
