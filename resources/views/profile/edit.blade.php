<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Profile</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success">Profile updated.</div>
            @endif
            @if (session('status') === 'password-updated')
                <div class="alert alert-success">Password updated.</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header"><strong>Profile Information</strong></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PATCH')
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header"><strong>Update Password</strong></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white"><strong>Delete Account</strong></div>
                        <div class="card-body">
                            <p class="text-muted">This permanently deletes your account. Enter your password to confirm.</p>
                            <form method="POST" action="{{ route('profile.destroy') }}" class="row g-2">
                                @csrf
                                @method('DELETE')
                                <div class="col-md-4">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-danger">Delete Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
