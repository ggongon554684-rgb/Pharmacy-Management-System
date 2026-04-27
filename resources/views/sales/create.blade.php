<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Release Medicine (POS)</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">

            {{-- ── Pre-order info banner ── --}}
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    <strong>Pre-order loaded:</strong> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($preOrder))
                <div class="alert alert-primary d-flex align-items-center gap-2 mb-3" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"/>
                        <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8zm0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    <span>
                        Pre-Order <strong>#{{ $preOrder->id }}</strong>
                        — {{ $preOrder->customer_name ?? 'Walk-in' }}
                        — Payment: <strong>{{ ucfirst($preOrder->payment_method) }}</strong>
                        — {{ $preOrder->items->count() }} item(s) pre-loaded into cart
                    </span>
                </div>
            @endif

            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <div class="card pos-panel shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        {{-- Link sale back to pre-order if present --}}
                        @if(isset($preOrder))
                            <input type="hidden" name="pre_order_id" value="{{ $preOrder->id }}">
                        @endif

                        <div class="pos-toolbar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <strong>Front-Shop Mode</strong>
                                <div class="text-muted small">Fast search, quick add, and sticky checkout for busy queue times.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="focus-medicine-search">Focus Medicine Search</button>
                        </div>

                        <h6 class="mb-3">Patient Details</h6>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select name="patient_mode" class="form-select" id="patient_mode">
                                    <option value="existing" {{ old('patient_mode') === 'existing' ? 'selected' : '' }}>Existing Patient</option>
                                    <option value="new" {{ old('patient_mode') === 'new' ? 'selected' : '' }}>New Patient</option>
                                </select>
                            </div>
                            <div class="col-md-9" id="existing_patient_wrap" style="{{ old('patient_mode') === 'new' ? 'display: none;' : '' }}">
                                <select class="form-select" name="patient_id">
                                    <option value="">Select patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ (string) old('patient_id') === (string) $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} ({{ $patient->birthdate?->format('Y-m-d') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="new_patient_wrap" style="{{ old('patient_mode') === 'new' ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="text" name="patient_name" value="{{ old('patient_name') }}" class="form-control" placeholder="Patient Name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="date" name="patient_birthdate" value="{{ old('patient_birthdate') }}" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="text" name="patient_contact_info" value="{{ old('patient_contact_info') }}" class="form-control" placeholder="Contact Info">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="patient_allergies" value="{{ old('patient_allergies') }}" class="form-control" placeholder="Allergies (optional)">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Prescription (Optional)</label>
                                <select class="form-select" name="prescription_id" id="prescription_id">
                                    <option value="">No prescription</option>
                                    @foreach($prescriptions as $prescription)
                                        <option value="{{ $prescription->id }}" data-patient-id="{{ $prescription->patient_id }}" {{ (string) old('prescription_id') === (string) $prescription->id ? 'selected' : '' }}>
                                            RX #{{ $prescription->id }} - {{ $prescription->patient->name ?? 'Unknown Patient' }} - {{ $prescription->prescriber->name ?? 'Unknown Prescriber' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Optional: link sale to an active prescription.</small>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Medicine Details</h6>
                        <div class="row mb-3">
                            <div class="col-lg-8">
                                <input type="text" id="medicine-search" class="form-control" placeholder="Search medicine by brand or generic name..." autocomplete="off">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="row g-3" id="medicine-cards">
                                    @foreach($products as $product)
                                        @php
                                            $stock = (int) ($product->sellable_stock ?? 0);
                                            $frontStock = (int) ($product->front_stock ?? 0);
                                            $backStock = (int) ($product->back_stock ?? 0);
                                        @endphp
                                        @if($stock > 0)
                                        <div class="col-md-6 col-xl-4">
                                            <div class="card pos-card h-100 border {{ $stock <= 0 ? 'border-danger-subtle bg-light' : 'border-primary-subtle' }}" data-name="{{ strtolower($product->name) }}" data-generic="{{ strtolower($product->generic_name ?? '') }}">
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $product->name }}</h6>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">...</button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="{{ route('products.show', $product) }}">View details</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @if($product->generic_name)
                                                        <small class="text-muted d-block mb-2">{{ $product->generic_name }}</small>
                                                    @endif
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="badge {{ $stock <= 0 ? 'text-bg-danger' : ($stock <= $product->reorder_level ? 'text-bg-warning' : 'text-bg-success') }}">Stock: {{ $stock }}</span>
                                                        <span class="fw-semibold">P{{ number_format($product->price, 2) }}</span>
                                                    </div>
                                                    <small class="text-muted d-block mb-2">Front: {{ $frontStock }} | Back: {{ $backStock }}</small>
                                                    <div class="d-flex gap-1 mb-2">
                                                        <button type="button" class="pos-qty-chip quick-add" data-quick-qty="1">+1</button>
                                                        <button type="button" class="pos-qty-chip quick-add" data-quick-qty="2">+2</button>
                                                        <button type="button" class="pos-qty-chip quick-add" data-quick-qty="5">+5</button>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm {{ $stock <= 0 ? 'btn-outline-secondary disabled' : 'btn-outline-primary' }} mt-auto add-to-cart"
                                                        data-product-id="{{ $product->id }}"
                                                        data-product-name="{{ $product->name }}"
                                                        data-product-price="{{ (float) $product->price }}"
                                                        data-product-stock="{{ $stock }}"
                                                        {{ $stock <= 0 ? 'disabled' : '' }}
                                                    >Add to order</button>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border-primary-subtle pos-cart-sticky">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">Order Cart</h6>
                                            <span class="badge text-bg-primary" id="cart-count">0</span>
                                        </div>
                                        <div id="cart-empty" class="text-muted small">No medicines selected yet.</div>
                                        <div class="table-responsive d-none" id="cart-table-wrap">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr><th>Medicine</th><th style="width: 90px;">Qty</th><th style="width: 120px;"></th></tr>
                                                </thead>
                                                <tbody id="cart-rows"></tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                            <strong>Total</strong>
                                            <strong id="cart-total">P0.00</strong>
                                        </div>
                                        <div class="mt-3">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Method</label>
                                                <select class="form-select" name="payment_method" id="payment_method" required>
                                                    <option value="cash" {{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="card" {{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : '') === 'card' ? 'selected' : '' }}>Card</option>
                                                    <option value="insurance" {{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : '') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                                                </select>
                                            </div>
                                            <div id="payment_cash_fields" class="row g-3 mb-3" style="{{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : 'cash') === 'cash' ? '' : 'display: none;' }}">
                                                <div class="col-12">
                                                    <label class="form-label">Cash Received</label>
                                                    <input type="number" step="0.01" min="0" class="form-control" name="payment_tendered" id="payment_tendered" value="{{ old('payment_tendered', '') }}" placeholder="Amount customer gives">
                                                    <small class="text-muted">Enter cash after selecting medicines; change due updates live.</small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Change Due</label>
                                                    <div class="form-control-plaintext fw-semibold" id="payment_change_due_display">P0.00</div>
                                                </div>
                                            </div>
                                            <div id="payment_card_fields" class="row mb-3" style="{{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : '') === 'card' ? '' : 'display: none;' }}">
                                                <div class="col-12">
                                                    <label class="form-label">Card Transaction Reference</label>
                                                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" class="form-control" placeholder="Authorization code or reference">
                                                    <small class="text-muted">Required for credit/card payments.</small>
                                                </div>
                                            </div>
                                            <div id="payment_insurance_fields" class="row mb-3" style="{{ old('payment_method', isset($preOrder) ? $preOrder->payment_method : '') === 'insurance' ? '' : 'display: none;' }}">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Insurance Provider</label>
                                                    <input type="text" name="insurance_provider" value="{{ old('insurance_provider') }}" class="form-control" placeholder="Provider name">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Policy / Member Number</label>
                                                    <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number') }}" class="form-control" placeholder="Policy or member number">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Authorization Code</label>
                                                    <input type="text" name="insurance_authorization_code" value="{{ old('insurance_authorization_code') }}" class="form-control" placeholder="Optional authorization code">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="sale-lines-hidden"></div>

                        <button type="submit" class="btn btn-primary mt-3">Complete Release</button>
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary mt-3">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $prescriptionItemsMap = [];
        $prescriptionPatientMap = [];
        foreach ($prescriptions as $prescription):
            $productEntries = [];
            foreach ($prescription->prescriptionItems as $item):
                $productEntries[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ];
            endforeach;
            $prescriptionItemsMap[(string) $prescription->id] = $productEntries;
            $prescriptionPatientMap[(string) $prescription->id] = $prescription->patient_id;
        endforeach;
    @endphp

    <script type="application/json" id="prescription-items-data">@json($prescriptionItemsMap)</script>
    <script type="application/json" id="prescription-patients-data">@json($prescriptionPatientMap)</script>

    {{-- Pre-order prefill data (null-safe: only emitted when $preOrder is set) --}}
    @isset($preOrder)
        <script type="application/json" id="preorder-prefill-data">@json(
            $preOrder->items->map(fn($i) => [
                'product_id' => $i->product_id,
                'quantity'   => $i->quantity,
            ])
        )</script>
    @endisset

    <script>
        (function () {
            const mode = document.getElementById('patient_mode');
            const existingWrap = document.getElementById('existing_patient_wrap');
            const newWrap = document.getElementById('new_patient_wrap');
            const medicineCards = document.getElementById('medicine-cards');
            const hiddenLines = document.getElementById('sale-lines-hidden');
            const cartRows = document.getElementById('cart-rows');
            const cartCount = document.getElementById('cart-count');
            const cartEmpty = document.getElementById('cart-empty');
            const cartTableWrap = document.getElementById('cart-table-wrap');
            const cartTotal = document.getElementById('cart-total');
            const patientSelect = document.querySelector('select[name="patient_id"]');
            const prescriptionSelect = document.getElementById('prescription_id');
            const prescriptionItemsMap = JSON.parse(document.getElementById('prescription-items-data').textContent || '{}');
            const prescriptionPatientMap = JSON.parse(document.getElementById('prescription-patients-data').textContent || '{}');
            const medicineSearch = document.getElementById('medicine-search');
            const focusMedicineSearch = document.getElementById('focus-medicine-search');
            const paymentMethod = document.getElementById('payment_method');
            const cashPaymentFields = document.getElementById('payment_cash_fields');
            const cardPaymentFields = document.getElementById('payment_card_fields');
            const insurancePaymentFields = document.getElementById('payment_insurance_fields');
            const paymentTenderedInput = document.getElementById('payment_tendered');
            const paymentChangeDueDisplay = document.getElementById('payment_change_due_display');
            const form = document.querySelector('form');
            const cart = new Map();

            function parseCurrency(value) {
                return Number(String(value).replace(/[^\d.-]/g, '')) || 0;
            }

            function togglePatientMode() {
                if (mode.value === 'new') {
                    existingWrap.style.display = 'none';
                    newWrap.style.display = 'block';
                } else {
                    existingWrap.style.display = 'block';
                    newWrap.style.display = 'none';
                }
            }

            mode.addEventListener('change', togglePatientMode);
            togglePatientMode();

            function updatePrescriptionOptions() {
                const selectedPatientId = patientSelect.value;
                for (const option of prescriptionSelect.options) {
                    if (!option.value) {
                        option.hidden = false;
                        continue;
                    }
                    const optionPatientId = option.getAttribute('data-patient-id');
                    option.hidden = selectedPatientId && optionPatientId !== selectedPatientId;
                }
                if (prescriptionSelect.options[prescriptionSelect.selectedIndex].hidden) {
                    prescriptionSelect.value = '';
                }
            }

            patientSelect.addEventListener('change', updatePrescriptionOptions);
            prescriptionSelect.addEventListener('change', function () {
                addPrescriptionItems(prescriptionSelect.value);
            });
            updatePrescriptionOptions();
            if (prescriptionSelect.value) {
                addPrescriptionItems(prescriptionSelect.value);
            }

            function addPrescriptionItems(prescriptionId) {
                if (!prescriptionId) {
                    return;
                }

                const items = prescriptionItemsMap[prescriptionId] || [];
                const patientId = prescriptionPatientMap[prescriptionId] || null;

                if (patientId) {
                    mode.value = 'existing';
                    togglePatientMode();
                    patientSelect.value = patientId;
                    updatePrescriptionOptions();
                }

                cart.clear();
                const missingProducts = [];

                items.forEach(function (item) {
                    const button = document.querySelector('button[data-product-id="' + item.product_id + '"]');
                    if (!button) {
                        missingProducts.push(item.product_id);
                        return;
                    }

                    const stock = Number(button.dataset.productStock || 0);
                    const quantity = Math.min(Number(item.quantity || 0), stock);
                    if (stock <= 0 || quantity <= 0) {
                        missingProducts.push(item.product_id);
                        return;
                    }

                    cart.set(String(item.product_id), {
                        id: String(item.product_id),
                        name: button.dataset.productName,
                        price: Number(button.dataset.productPrice),
                        stock: stock,
                        quantity: quantity,
                    });
                });

                renderCart();

                if (missingProducts.length > 0) {
                    window.alert('Some prescription medicines could not be added because they are unavailable or out of stock.');
                }
            }

            function formatCurrency(value) { return 'P' + Number(value).toFixed(2); }

            function createCartRow(item) {
                const row = document.createElement('tr');

                const nameCell = document.createElement('td');
                const nameDiv = document.createElement('div');
                nameDiv.textContent = item.name;
                const stockSmall = document.createElement('small');
                stockSmall.className = 'text-muted';
                stockSmall.textContent = 'Stock: ' + Number(item.stock);
                nameCell.appendChild(nameDiv);
                nameCell.appendChild(stockSmall);

                const qtyCell = document.createElement('td');
                const qtyInput = document.createElement('input');
                qtyInput.type = 'number';
                qtyInput.min = '1';
                qtyInput.max = String(Number(item.stock));
                qtyInput.className = 'form-control form-control-sm cart-qty';
                qtyInput.dataset.productId = String(item.id);
                qtyInput.value = String(Number(item.quantity));
                qtyCell.appendChild(qtyInput);

                const actionsCell = document.createElement('td');
                actionsCell.className = 'text-end';
                const btnGroup = document.createElement('div');
                btnGroup.className = 'btn-group btn-group-sm';
                btnGroup.setAttribute('role', 'group');
                btnGroup.setAttribute('aria-label', 'Quantity actions');

                [
                    { cls: 'btn-outline-secondary cart-dec', label: '-' },
                    { cls: 'btn-outline-secondary cart-inc', label: '+' },
                    { cls: 'btn-outline-danger cart-remove',  label: 'x' },
                ].forEach(function (btnDef) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm ' + btnDef.cls;
                    btn.dataset.productId = String(item.id);
                    btn.textContent = btnDef.label;
                    btnGroup.appendChild(btn);
                });

                actionsCell.appendChild(btnGroup);
                row.appendChild(nameCell);
                row.appendChild(qtyCell);
                row.appendChild(actionsCell);
                return row;
            }

            function syncHiddenInputs() {
                hiddenLines.innerHTML = '';
                for (const item of cart.values()) {
                    const productInput = document.createElement('input');
                    productInput.type = 'hidden';
                    productInput.name = 'product_ids[]';
                    productInput.value = item.id;
                    hiddenLines.appendChild(productInput);

                    const qtyInput = document.createElement('input');
                    qtyInput.type = 'hidden';
                    qtyInput.name = 'quantities[]';
                    qtyInput.value = item.quantity;
                    hiddenLines.appendChild(qtyInput);
                }
            }

            function renderCart() {
                cartRows.innerHTML = '';
                let total = 0;
                for (const item of cart.values()) {
                    total += item.price * item.quantity;
                    cartRows.appendChild(createCartRow(item));
                }
                cartCount.textContent = cart.size;
                cartTotal.textContent = formatCurrency(total);
                const hasItems = cart.size > 0;
                cartEmpty.classList.toggle('d-none', hasItems);
                cartTableWrap.classList.toggle('d-none', !hasItems);
                syncHiddenInputs();
                updateCashChange();
            }

            function addToCart(product) {
                const existing = cart.get(product.id);
                if (existing) {
                    existing.quantity = Math.min(existing.quantity + 1, existing.stock);
                } else {
                    const newProduct = Object.assign({}, product);
                    newProduct.quantity = 1;
                    cart.set(product.id, newProduct);
                }
                renderCart();
            }

            function addManyToCart(product, qty) {
                for (let i = 0; i < qty; i += 1) {
                    addToCart(product);
                }
            }

            medicineCards.addEventListener('click', function (event) {
                const quickAdd = event.target.closest('.quick-add');
                if (quickAdd) {
                    const productCard = quickAdd.closest('.card');
                    const addBtn = productCard ? productCard.querySelector('.add-to-cart') : null;
                    if (!addBtn || addBtn.disabled) {
                        return;
                    }
                    addManyToCart({
                        id: addBtn.dataset.productId,
                        name: addBtn.dataset.productName,
                        price: Number(addBtn.dataset.productPrice),
                        stock: Number(addBtn.dataset.productStock),
                    }, Number(quickAdd.dataset.quickQty || 1));
                    return;
                }

                const addButton = event.target.closest('.add-to-cart');
                if (!addButton || addButton.disabled) {
                    return;
                }
                addToCart({
                    id: addButton.dataset.productId,
                    name: addButton.dataset.productName,
                    price: Number(addButton.dataset.productPrice),
                    stock: Number(addButton.dataset.productStock),
                });
            });

            cartRows.addEventListener('input', function (event) {
                if (!event.target.classList.contains('cart-qty')) {
                    return;
                }
                const productId = event.target.dataset.productId;
                const item = cart.get(productId);
                if (!item) {
                    return;
                }
                const next = Math.max(1, Math.min(Number(event.target.value || 1), item.stock));
                item.quantity = next;
                event.target.value = next;
                renderCart();
            });

            cartRows.addEventListener('click', function (event) {
                const decButton = event.target.closest('.cart-dec');
                if (decButton) {
                    const item = cart.get(decButton.dataset.productId);
                    if (item) {
                        item.quantity = Math.max(1, item.quantity - 1);
                        renderCart();
                    }
                    return;
                }

                const incButton = event.target.closest('.cart-inc');
                if (incButton) {
                    const item = cart.get(incButton.dataset.productId);
                    if (item) {
                        item.quantity = Math.min(item.stock, item.quantity + 1);
                        renderCart();
                    }
                    return;
                }

                const removeButton = event.target.closest('.cart-remove');
                if (removeButton) {
                    cart.delete(removeButton.dataset.productId);
                    renderCart();
                }
            });

            if (medicineSearch) {
                medicineSearch.addEventListener('input', function () {
                    const query = medicineSearch.value.trim().toLowerCase();
                    medicineCards.querySelectorAll('.col-md-6.col-xl-4').forEach(function (cardCol) {
                        const card = cardCol.querySelector('.card');
                        const haystack = card ? (card.dataset.name || '') + ' ' + (card.dataset.generic || '') : '';
                        cardCol.style.display = !query || haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            if (focusMedicineSearch && medicineSearch) {
                focusMedicineSearch.addEventListener('click', function () { medicineSearch.focus(); });
            }

            function togglePaymentFields() {
                const method = paymentMethod ? paymentMethod.value : '';
                if (!method) {
                    return;
                }
                cashPaymentFields.style.display = method === 'cash' ? '' : 'none';
                cardPaymentFields.style.display = method === 'card' ? '' : 'none';
                insurancePaymentFields.style.display = method === 'insurance' ? '' : 'none';
                updateCashChange();
            }

            function updateCashChange() {
                const total = parseCurrency(cartTotal.textContent);
                const tendered = parseCurrency(paymentTenderedInput ? paymentTenderedInput.value : '');
                const changeDue = Math.max(0, tendered - total);
                if (paymentChangeDueDisplay) {
                    paymentChangeDueDisplay.textContent = 'P' + changeDue.toFixed(2);
                }
            }

            if (paymentMethod) {
                paymentMethod.addEventListener('change', togglePaymentFields);
            }
            if (paymentTenderedInput) {
                paymentTenderedInput.addEventListener('input', updateCashChange);
            }
            togglePaymentFields();

            form.addEventListener('submit', function (event) {
                if (cart.size === 0) {
                    event.preventDefault();
                    window.alert('Please add at least one medicine to the cart.');
                    return;
                }

                const total = parseCurrency(cartTotal.textContent);
                const method = paymentMethod ? paymentMethod.value : '';

                if (method === 'cash') {
                    const tendered = parseCurrency(paymentTenderedInput?.value);
                    if (tendered < total) {
                        event.preventDefault();
                        window.alert('Cash received must cover the total amount due.');
                        return;
                    }
                }

                if (method === 'card') {
                    const reference = document.querySelector('input[name="payment_reference"]').value.trim();
                    if (!reference) {
                        event.preventDefault();
                        window.alert('Card transaction reference is required for credit payments.');
                        return;
                    }
                }

                if (method === 'insurance') {
                    const provider = document.querySelector('input[name="insurance_provider"]').value.trim();
                    const policy = document.querySelector('input[name="insurance_policy_number"]').value.trim();
                    if (!provider || !policy) {
                        event.preventDefault();
                        window.alert('Insurance provider and policy number are required for insurance payments.');
                        return;
                    }
                }
            });

            // ── Boot cart from pre-order on page load ────────────────────────
            (function bootPreOrderPrefill() {
                const el = document.getElementById('preorder-prefill-data');
                if (!el) { return; }

                let items;
                try { items = JSON.parse(el.textContent); } catch (e) { return; }
                if (!Array.isArray(items) || items.length === 0) { return; }

                const missing = [];
                items.forEach(function (item) {
                    const btn = document.querySelector(
                        'button.add-to-cart[data-product-id="' + item.product_id + '"]'
                    );
                    if (!btn || btn.disabled) {
                        missing.push(item.product_id);
                        return;
                    }
                    const stock = Number(btn.dataset.productStock || 0);
                    const qty   = Math.min(Number(item.quantity || 1), stock);
                    if (stock <= 0 || qty <= 0) {
                        missing.push(item.product_id);
                        return;
                    }
                    cart.set(String(item.product_id), {
                        id:       String(item.product_id),
                        name:     btn.dataset.productName,
                        price:    Number(btn.dataset.productPrice),
                        stock:    stock,
                        quantity: qty,
                    });
                });

                renderCart();

                if (missing.length > 0) {
                    window.alert(
                        missing.length + ' pre-order item(s) could not be added — ' +
                        'they may be out of stock at the front location.'
                    );
                }
            })();
            // ── end pre-order prefill ────────────────────────────────────────

            renderCart();
        })();
    </script>
</x-app-layout>