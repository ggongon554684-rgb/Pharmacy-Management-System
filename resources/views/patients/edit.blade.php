<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Edit Patient</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('patients.update', $patient) }}">
                                @csrf @method('PATCH')
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input name="name" value="{{ old('name', $patient->name) }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Birthdate</label>
                                    <input type="date" name="birthdate" value="{{ old('birthdate', $patient->birthdate->toDateString()) }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Info</label>
                                    <input name="contact_info" value="{{ old('contact_info', $patient->contact_info) }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allergies</label>
                                    <textarea name="allergies" rows="3" class="form-control">{{ old('allergies', $patient->allergies) }}</textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>