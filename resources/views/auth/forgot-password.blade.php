<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Pharmacy') }} - Forgot Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#f5f7fb}.auth-card{max-width:430px;margin:6vh auto;border:0;border-radius:14px}</style>
</head>
<body>
    <div class="container">
        <div class="card shadow auth-card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3 text-center">Forgot Password</h1>
                <p class="text-muted small">Enter your email and we will send a reset link.</p>
                @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Back to Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
