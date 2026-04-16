<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Incoming Deliveries</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="mb-3">
                <h5 class="module-title mb-1">Incoming Deliveries</h5>
                <div class="module-subtitle">Monitor approved purchase orders waiting for receiving.</div>
            </div>
            <div class="card module-surface">
                <div class="card-body">
                    <div id="incoming-refresh-status" class="mb-2 text-muted small">Last updated: {{ now()->format('M d, Y H:i:s') }}</div>
                    <div id="incoming-table-container">
                        @include('purchase-orders._incoming-table', ['purchaseOrders' => $purchaseOrders])
                    </div>
                </div>
            </div>
            <div id="incoming-pagination">
                @include('purchase-orders._incoming-pagination', ['purchaseOrders' => $purchaseOrders])
            </div>
        </div>
    </div>
    <script>
        (function () {
            const refreshStatus = document.getElementById('incoming-refresh-status');
            const tableContainer = document.getElementById('incoming-table-container');
            const paginationContainer = document.getElementById('incoming-pagination');
            const refreshUri = "{{ route('purchase-orders.incoming.refresh') }}";

            async function refreshIncoming() {
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
                    console.error('Incoming deliveries refresh failed:', error);
                }
            }

            setInterval(refreshIncoming, 10000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    refreshIncoming();
                }
            });
        })();
    </script>
</x-app-layout>
