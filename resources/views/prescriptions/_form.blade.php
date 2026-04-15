@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Patient</label>
        <select name="patient_id" class="form-select" required>
            <option value="">Select patient</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" {{ (string) old('patient_id', $prescription->patient_id ?? '') === (string) $patient->id ? 'selected' : '' }}>
                    {{ $patient->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Prescriber</label>
        <select name="prescriber_id" class="form-select" required>
            <option value="">Select prescriber</option>
            @foreach($prescribers as $prescriber)
                <option value="{{ $prescriber->id }}" {{ (string) old('prescriber_id', $prescription->prescriber_id ?? '') === (string) $prescriber->id ? 'selected' : '' }}>
                    {{ $prescriber->name }} ({{ $prescriber->license_number }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Issued Date</label>
        <input type="date" class="form-control" name="issued_date" value="{{ old('issued_date', isset($prescription) ? $prescription->issued_date?->toDateString() : now()->toDateString()) }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            @foreach(['active', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" {{ old('status', $prescription->status ?? 'active') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">RX Items</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-rx-item">Add Item</button>
</div>
<div id="rx-items-wrap">
    @php
        $seedItems = old('rx_items', isset($prescription) ? $prescription->prescriptionItems->map(fn ($item) => [
            'product_id' => $item->product_id,
            'dosage' => $item->dosage,
            'quantity' => $item->quantity,
        ])->values()->all() : [['product_id' => '', 'dosage' => '', 'quantity' => 1]]);
    @endphp
    @foreach($seedItems as $i => $item)
        <div class="row g-2 align-items-end mb-2 rx-item-row">
            <div class="col-md-5">
                <label class="form-label">Product</label>
                <select class="form-select" name="rx_items[{{ $i }}][product_id]" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ (string) ($item['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Dosage</label>
                <input type="text" class="form-control" name="rx_items[{{ $i }}][dosage]" value="{{ $item['dosage'] ?? '' }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qty</label>
                <input type="number" min="1" class="form-control" name="rx_items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-rx-item">X</button>
            </div>
        </div>
    @endforeach
</div>

<script>
    (function () {
        const addButton = document.getElementById('add-rx-item');
        const wrap = document.getElementById('rx-items-wrap');
        if (!addButton || !wrap) return;
        const productOptions = `
            <option value="">Select product</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        `;

        function reindexRows() {
            [...wrap.querySelectorAll('.rx-item-row')].forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name.replace(/rx_items\[\d+\]/, `rx_items[${index}]`);
                });
            });
        }

        addButton.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-end mb-2 rx-item-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label">Product</label>
                    <select class="form-select" name="rx_items[0][product_id]" required>${productOptions}</select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dosage</label>
                    <input type="text" class="form-control" name="rx_items[0][dosage]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" min="1" class="form-control" name="rx_items[0][quantity]" value="1" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-rx-item">X</button>
                </div>
            `;
            wrap.appendChild(row);
            reindexRows();
        });

        wrap.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-rx-item');
            if (!removeButton) return;
            const rows = wrap.querySelectorAll('.rx-item-row');
            if (rows.length <= 1) return;
            removeButton.closest('.rx-item-row').remove();
            reindexRows();
        });
    })();
</script>
