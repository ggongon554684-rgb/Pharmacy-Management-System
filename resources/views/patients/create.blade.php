<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Add Patient</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('patients.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input name="name" value="{{ old('name') }}" class="form-control" required>
                                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Birthdate</label>
                                    <input type="date" name="birthdate" value="{{ old('birthdate') }}" class="form-control" required>
                                    @error('birthdate')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Info</label>
                                    <input name="contact_info" value="{{ old('contact_info') }}" class="form-control" required>
                                    @error('contact_info')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allergies (optional)</label>
                                    <textarea name="allergies" rows="3" class="form-control">{{ old('allergies') }}</textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>