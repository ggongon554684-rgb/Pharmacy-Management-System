@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" value="{{ old('name', $prescriber->name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">License Number</label>
        <input type="text" class="form-control" name="license_number" value="{{ old('license_number', $prescriber->license_number ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Contact Info</label>
        <input type="text" class="form-control" name="contact_info" value="{{ old('contact_info', $prescriber->contact_info ?? '') }}">
    </div>
</div>
