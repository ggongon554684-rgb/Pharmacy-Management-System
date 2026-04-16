<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Receive PO {{ $purchaseOrder->po_number }}</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="card module-surface">
                <div class="card-body">
                    <p>
                        <strong>Status:</strong>
                        @php
                            $statusClass = $purchaseOrder->status === 'pending' ? 'status-pending' : ($purchaseOrder->status === 'approved' ? 'status-approved' : ($purchaseOrder->status === 'received' ? 'status-received' : 'status-pending'));
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($purchaseOrder->status) }}</span>
                    </p>
                    <p><strong>Expected:</strong> {{ $purchaseOrder->expected_date?->format('M d, Y') ?? '-' }}</p>
                    <p><strong>Notes:</strong> {{ $purchaseOrder->notes ?? '-' }}</p>
                    <hr>
                    <h6>Cost Summary</h6>
                    <p class="mb-1"><strong>Item Cost:</strong> {{ number_format($purchaseOrder->items->sum(fn($item) => ($item->unit_cost ?? 0) * $item->quantity), 2) }}</p>
                    <p class="mb-1"><strong>Delivery Cost:</strong> {{ number_format((float) $purchaseOrder->delivery_cost, 2) }}</p>
                    <p class="mb-1"><strong>Insurance Cost:</strong> {{ number_format((float) $purchaseOrder->insurance_cost, 2) }}</p>
                    <p class="mb-1"><strong>Other Cost:</strong> {{ number_format((float) $purchaseOrder->other_cost, 2) }}</p>
                    <p class="mb-1"><strong>Total Cost:</strong> {{ number_format((float) $purchaseOrder->total_cost, 2) }}</p>
                    <hr>
                    <h6>Items</h6>
                    <ul class="mb-0 module-subtitle">
                        @foreach($purchaseOrder->items as $item)
                            <li>{{ $item->product->name }} - Qty: {{ $item->quantity }} @if($item->unit_cost) / Cost: {{ number_format($item->unit_cost, 2) }} @endif</li>
                        @endforeach
                    </ul>
                    <hr>
                    <h6>Receive Delivery</h6>
                    <form method="POST" action="{{ route('purchase-orders.receive.post', $purchaseOrder) }}" class="row g-2" id="receive-form">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Receive Date</label>
                            <input type="date" name="receive_date" id="receive_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3" id="reason-container" style="display: none;">
                            <label class="form-label">Reason for Backdate</label>
                            <input type="text" name="backdate_reason" class="form-control" placeholder="Why backdating?">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Batch Prefix</label>
                            <input name="batch_number" class="form-control" placeholder="Batch prefix" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success" type="submit">Receive PO</button>
                        </div>
                    </form>

                    <hr>
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary btn-sm">Back to PO Details</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const receiveDateInput = document.getElementById('receive_date');
            const reasonContainer = document.getElementById('reason-container');
            const today = new Date().toISOString().split('T')[0];

            function toggleReasonField() {
                const selectedDate = receiveDateInput.value;
                const isToday = selectedDate === today;
                reasonContainer.style.display = isToday ? 'none' : 'block';
                const reasonInput = reasonContainer.querySelector('input');
                if (reasonInput) {
                    reasonInput.required = !isToday;
                }
            }

            receiveDateInput.addEventListener('change', toggleReasonField);
            toggleReasonField(); // Initial check
        })();
    </script>
</x-app-layout>