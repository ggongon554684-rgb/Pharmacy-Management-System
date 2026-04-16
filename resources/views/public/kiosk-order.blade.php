<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Medicine Order Kiosk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --kiosk-primary: #2563eb;
            --kiosk-border: #dce5f0;
            --kiosk-bg: #f8fafc;
        }

        body {
            background: var(--kiosk-bg);
        }

        .kiosk-shell {
            max-width: 1200px;
            margin: 0 auto;
        }

        .kiosk-surface {
            background: #fff;
            border: 1px solid var(--kiosk-border);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }

        .kiosk-card {
            border: 1px solid var(--kiosk-border);
            border-radius: 12px;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .kiosk-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }

        .kiosk-cart-sticky {
            position: sticky;
            top: 1rem;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4 kiosk-shell">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">Order Medicine</h2>
            <p class="text-muted mb-0">Pick medicines, set quantity, and generate your order ticket for pharmacist scanning.</p>
        </div>
        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Staff Login</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <div class="kiosk-surface p-3 p-lg-4">
        <form method="POST" action="{{ route('public.kiosk-order.store') }}">
            @csrf
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="row g-2 mb-3">
                    <div class="col-md-7">
                        <input type="text" id="kiosk-search" class="form-control" placeholder="Search medicine name or generic...">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="customer_name" id="kiosk-customer-name" class="form-control" placeholder="Your name (optional)">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3" id="kiosk-medicine-cards">
                    @foreach($products as $medicine)
                        @php $stock = (int) ($medicine->sellable_stock ?? 0); @endphp
                        @if($stock > 0)
                        <div class="col-md-6 col-xl-4 medicine-col">
                            <div class="kiosk-card p-3 h-100 d-flex flex-column" data-name="{{ strtolower($medicine->name) }}" data-generic="{{ strtolower($medicine->generic_name ?? '') }}">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-1">{{ $medicine->name }}</h6>
                                    <span class="fw-semibold">P{{ number_format($medicine->price, 2) }}</span>
                                </div>
                                <small class="text-muted">{{ $medicine->generic_name }}</small>
                                <div class="mt-2 mb-2">
                                    <span class="badge {{ $stock > 0 ? 'text-bg-success' : 'text-bg-danger' }}">
                                        Stock: {{ $stock }}
                                    </span>
                                </div>
                                <div class="d-flex gap-1 mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary quick-add" data-id="{{ $medicine->id }}" data-qty="1">+1</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary quick-add" data-id="{{ $medicine->id }}" data-qty="2">+2</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary quick-add" data-id="{{ $medicine->id }}" data-qty="5">+5</button>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-sm mt-auto {{ $stock > 0 ? 'btn-primary add-med' : 'btn-outline-secondary disabled' }}"
                                    data-id="{{ $medicine->id }}"
                                    data-name="{{ $medicine->name }}"
                                    data-price="{{ $medicine->price }}"
                                    data-stock="{{ $stock }}"
                                    {{ $stock <= 0 ? 'disabled' : '' }}
                                >Add</button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kiosk-surface p-3 kiosk-cart-sticky">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Order Summary</h6>
                        <span class="badge text-bg-primary" id="kiosk-cart-count">0</span>
                    </div>
                    <div id="kiosk-empty" class="text-muted small">No medicines selected yet.</div>
                    <div class="table-responsive d-none" id="kiosk-table-wrap">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th>Item</th><th style="width: 90px;">Qty</th><th style="width: 70px;"></th></tr>
                            </thead>
                            <tbody id="kiosk-cart-rows"></tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                        <strong>Total</strong>
                        <strong id="kiosk-total">P0.00</strong>
                    </div>
                    <div class="mt-3 d-grid">
                        <button type="submit" id="kiosk-generate" class="btn btn-primary">Generate QR Ticket</button>
                    </div>
                    <small class="text-muted d-block mt-2">After generation, show this QR to the pharmacist for scanning.</small>
                </div>
            </div>
        </div>
        <div id="kiosk-hidden-lines"></div>
        </form>
    </div>
</div>

<script>
    (function () {
        const cart = new Map();
        const cardsWrap = document.getElementById('kiosk-medicine-cards');
        const cartRows = document.getElementById('kiosk-cart-rows');
        const empty = document.getElementById('kiosk-empty');
        const tableWrap = document.getElementById('kiosk-table-wrap');
        const cartCount = document.getElementById('kiosk-cart-count');
        const total = document.getElementById('kiosk-total');
        const searchInput = document.getElementById('kiosk-search');
        const generateBtn = document.getElementById('kiosk-generate');
        const hiddenLines = document.getElementById('kiosk-hidden-lines');

        function formatCurrency(value) {
            return 'P' + Number(value).toFixed(2);
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
                qtyInput.value = item.qty;
                hiddenLines.appendChild(qtyInput);
            }
        }

        function renderCart() {
            cartRows.innerHTML = '';
            let sum = 0;
            for (const item of cart.values()) {
                sum += item.price * item.qty;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td><input type="number" min="1" max="${item.stock}" value="${item.qty}" class="form-control form-control-sm qty-input" data-id="${item.id}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}">x</button></td>
                `;
                cartRows.appendChild(row);
            }
            const has = cart.size > 0;
            empty.classList.toggle('d-none', has);
            tableWrap.classList.toggle('d-none', !has);
            cartCount.textContent = cart.size;
            total.textContent = formatCurrency(sum);
            syncHiddenInputs();
        }

        function addItem(meta, qtyToAdd) {
            const existing = cart.get(meta.id);
            if (existing) {
                existing.qty = Math.min(existing.stock, existing.qty + qtyToAdd);
            } else {
                cart.set(meta.id, { ...meta, qty: Math.min(meta.stock, qtyToAdd) });
            }
            renderCart();
        }

        cardsWrap.addEventListener('click', function (event) {
            const addBtn = event.target.closest('.add-med');
            const quickBtn = event.target.closest('.quick-add');

            if (quickBtn) {
                const card = quickBtn.closest('.kiosk-card');
                const base = card ? card.querySelector('.add-med') : null;
                if (!base || base.disabled) {
                    return;
                }
                addItem({
                    id: base.dataset.id,
                    name: base.dataset.name,
                    price: Number(base.dataset.price),
                    stock: Number(base.dataset.stock),
                }, Number(quickBtn.dataset.qty || 1));
                return;
            }

            if (!addBtn || addBtn.disabled) {
                return;
            }
            addItem({
                id: addBtn.dataset.id,
                name: addBtn.dataset.name,
                price: Number(addBtn.dataset.price),
                stock: Number(addBtn.dataset.stock),
            }, 1);
        });

        cartRows.addEventListener('input', function (event) {
            if (!event.target.classList.contains('qty-input')) {
                return;
            }
            const item = cart.get(event.target.dataset.id);
            if (!item) {
                return;
            }
            item.qty = Math.max(1, Math.min(item.stock, Number(event.target.value || 1)));
            event.target.value = item.qty;
            renderCart();
        });

        cartRows.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.remove-item');
            if (!removeBtn) {
                return;
            }
            cart.delete(removeBtn.dataset.id);
            renderCart();
        });

        searchInput.addEventListener('input', function () {
            const query = searchInput.value.trim().toLowerCase();
            cardsWrap.querySelectorAll('.medicine-col').forEach(function (col) {
                const card = col.querySelector('.kiosk-card');
                const hay = `${card.dataset.name || ''} ${card.dataset.generic || ''}`;
                col.style.display = !query || hay.includes(query) ? '' : 'none';
            });
        });

        generateBtn.addEventListener('click', function (event) {
            if (cart.size === 0) {
                event.preventDefault();
                window.alert('Please select at least one medicine first.');
            }
        });
    })();
</script>
</body>
</html>
