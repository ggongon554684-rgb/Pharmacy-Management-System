<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Pharmacy') }} - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#f5f7fb}.auth-card{max-width:430px;margin:6vh auto;border:0;border-radius:14px}</style>
</head>
<body>
    <div class="container">
        <div class="card shadow auth-card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3 text-center">Create Account</h1>
                @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Register</button>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Already registered?</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
