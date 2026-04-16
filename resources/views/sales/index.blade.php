<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center page-header">
            <h2 class="h4 mb-0 page-title">POS / Sales</h2>
            @can('create sales')
                <div class="page-actions">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">Release Medicine</a>
                </div>
            @endcan
        </div>
    </x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="card ui-surface">
                <div class="card-body">
                    <div id="sales-refresh-status" class="mb-2 text-muted small">Last updated: {{ now()->format('M d, Y H:i:s') }}</div>
                    <div id="sales-table-container">
                        @include('sales._table', ['sales' => $sales])
                    </div>
                </div>
            </div>
            <div id="sales-pagination">
                @include('sales._pagination', ['sales' => $sales])
            </div>
        </div>
    </div>
    <script>
        (function () {
            const refreshStatus = document.getElementById('sales-refresh-status');
            const tableContainer = document.getElementById('sales-table-container');
            const paginationContainer = document.getElementById('sales-pagination');
            const refreshUri = "{{ route('sales.refresh') }}";

            async function refreshSales() {
                try {
                    const url = refreshUri + window.location.search;
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        return;
                    }
                    const payload = await response.json();
                    tableContainer.innerHTML = payload.table;
                    paginationContainer.innerHTML = payload.pagination;
                    refreshStatus.textContent = 'Last updated: ' + payload.updated_at;
                } catch (error) {
                    console.error('Sales refresh failed:', error);
                }
            }

            setInterval(refreshSales, 10000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    refreshSales();
                }
            });
        })();
    </script>
</x-app-layout>
