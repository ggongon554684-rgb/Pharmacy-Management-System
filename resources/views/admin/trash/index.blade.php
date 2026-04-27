<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Trash') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Deleted Records</h4>
                    <p class="admin-support-text mb-0">Restore or permanently delete soft-deleted records within 30 days.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach($trashed as $type => $count)
                    <div class="col-md-4">
                        <div class="card admin-surface admin-kpi-card h-100">
                            <div class="card-body">
                                <div class="admin-kpi-label mb-2 text-capitalize"><i class="bi bi-trash admin-kpi-icon"></i>{{ __(' ') . str_replace('-', ' ', $type) }}</div>
                                <div class="admin-kpi-value">{{ $count }}</div>
                                <p class="admin-support-text mt-2 mb-0">{{ ucfirst(str_replace('-', ' ', $type)) }} in trash.</p>
                                <a href="{{ route('admin.trash.show', $type) }}" class="btn btn-sm btn-outline-primary mt-3">View {{ ucfirst(str_replace('-', ' ', $type)) }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
