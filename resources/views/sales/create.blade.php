<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Release Medicine (POS)</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="card pos-panel shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

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
                            <div class="col-md-9" id="existing_patient_wrap">
                                <div class="mb-2">
                                    <input type="text" id="patient-search" class="form-control form-control-sm" placeholder="Quick search patient name...">
                                </div>
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

                        <div id="new_patient_wrap" style="display: none;">
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
                                <select class="form-select" name="prescription_id">
                                    <option value="">No prescription</option>
                                    @foreach($prescriptions as $prescription)
                                        <option value="{{ $prescription->id }}" {{ (string) old('prescription_id') === (string) $prescription->id ? 'selected' : '' }}>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="sale-lines-hidden"></div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="insurance">Insurance</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Complete Release</button>
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
            const patientSearch = document.getElementById('patient-search');
            const patientSelect = document.querySelector('select[name="patient_id"]');
            const medicineSearch = document.getElementById('medicine-search');
            const focusMedicineSearch = document.getElementById('focus-medicine-search');
            const form = document.querySelector('form');
            const cart = new Map();

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

            function formatCurrency(value) { return 'P' + Number(value).toFixed(2); }

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
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div>${item.name}</div>
                            <small class="text-muted">Stock: ${item.stock}</small>
                        </td>
                        <td>
                            <input type="number" min="1" max="${item.stock}" class="form-control form-control-sm cart-qty" data-product-id="${item.id}" value="${item.quantity}">
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Quantity actions">
                                <button type="button" class="btn btn-outline-secondary cart-dec" data-product-id="${item.id}">-</button>
                                <button type="button" class="btn btn-outline-secondary cart-inc" data-product-id="${item.id}">+</button>
                                <button type="button" class="btn btn-outline-danger cart-remove" data-product-id="${item.id}">x</button>
                            </div>
                        </td>
                    `;
                    cartRows.appendChild(row);
                }
                cartCount.textContent = cart.size;
                cartTotal.textContent = formatCurrency(total);
                const hasItems = cart.size > 0;
                cartEmpty.classList.toggle('d-none', hasItems);
                cartTableWrap.classList.toggle('d-none', !hasItems);
                syncHiddenInputs();
            }

            function addToCart(product) {
                const existing = cart.get(product.id);
                if (existing) {
                    existing.quantity = Math.min(existing.quantity + 1, existing.stock);
                } else {
                    cart.set(product.id, { ...product, quantity: 1 });
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
                        const haystack = card ? `${card.dataset.name || ''} ${card.dataset.generic || ''}` : '';
                        cardCol.style.display = !query || haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            if (patientSearch && patientSelect) {
                patientSearch.addEventListener('input', function () {
                    const query = patientSearch.value.trim().toLowerCase();
                    for (const option of patientSelect.options) {
                        if (!option.value) continue;
                        option.hidden = query.length > 0 && !option.text.toLowerCase().includes(query);
                    }
                });
            }

            if (focusMedicineSearch && medicineSearch) {
                focusMedicineSearch.addEventListener('click', function () { medicineSearch.focus(); });
            }

            form.addEventListener('submit', function (event) {
                if (cart.size === 0) {
                    event.preventDefault();
                    window.alert('Please add at least one medicine to the cart.');
                }
            });

            renderCart();
        })();
    </script>
</x-app-layout>
