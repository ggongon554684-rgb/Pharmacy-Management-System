<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 text-dark mb-0">{{ __('Trash:') }} {{ ucfirst(str_replace('-', ' ', $type)) }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ ucfirst(str_replace('-', ' ', $type)) }} in Trash</h4>
                    <p class="admin-support-text mb-0">Restore deleted records or remove them permanently.</p>
                </div>
                <a href="{{ route('admin.trash.index') }}" class="btn btn-outline-secondary">Back to Trash Overview</a>
            </div>

            <div class="card admin-surface">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Record</th>
                                    <th scope="col">Deleted At</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td>{{ $record->id }}</td>
                                        <td>
                                            @if(isset($record->name))
                                                {{ $record->name }}
                                            @elseif(isset($record->prescription_number))
                                                {{ $record->prescription_number }}
                                            @elseif(isset($record->po_number))
                                                {{ $record->po_number }}
                                            @elseif(isset($record->invoice_number))
                                                {{ $record->invoice_number }}
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $record->getTable())) }}
                                            @endif
                                        </td>
                                        <td>{{ optional($record->deleted_at)->format('M d, Y H:i') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('admin.trash.restore', ['type' => $type, 'id' => $record->id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                                </form>
                                                <form action="{{ route('admin.trash.force-delete', ['type' => $type, 'id' => $record->id]) }}" method="POST" onsubmit="return confirm('Permanently delete this record? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No deleted records found for this category.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
