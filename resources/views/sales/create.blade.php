<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Release Medicine (POS)</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        <h6 class="mb-3">Patient Details</h6>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select name="patient_mode" class="form-select" id="patient_mode">
                                    <option value="existing" {{ old('patient_mode') === 'existing' ? 'selected' : '' }}>Existing Patient</option>
                                    <option value="new" {{ old('patient_mode') === 'new' ? 'selected' : '' }}>New Patient</option>
                                </select>
                            </div>
                            <div class="col-md-9" id="existing_patient_wrap">
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

                        <hr>
                        <h6 class="mb-3">Medicine Details</h6>
                        <div id="sale-lines">
                            <div class="row g-2 mb-2 sale-line">
                                <div class="col-md-7">
                                    <select class="form-select" name="product_ids[]" required>
                                        <option value="">Select medicine</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->inventory_batches_sum_quantity ?? 0 }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" min="1" name="quantities[]" class="form-control" placeholder="Qty" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-line">Remove</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-line" class="btn btn-outline-secondary btn-sm mb-3">+ Add Medicine</button>

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
            const lines = document.getElementById('sale-lines');
            const addBtn = document.getElementById('add-line');

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

            addBtn.addEventListener('click', function () {
                const row = lines.querySelector('.sale-line').cloneNode(true);
                row.querySelectorAll('input').forEach((el) => el.value = '');
                row.querySelectorAll('select').forEach((el) => el.selectedIndex = 0);
                lines.appendChild(row);
            });

            lines.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-line')) {
                    const all = lines.querySelectorAll('.sale-line');
                    if (all.length > 1) {
                        e.target.closest('.sale-line').remove();
                    }
                }
            });
        })();
    </script>
</x-app-layout>
