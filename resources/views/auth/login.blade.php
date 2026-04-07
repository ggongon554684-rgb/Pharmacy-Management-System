<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Pharmacy') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #f5f7fb; }
        .login-card { max-width: 430px; margin: 6vh auto; border: 0; border-radius: 14px; }
        .logo-wrap svg { width: 48px; height: 48px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <a href="/" class="logo-wrap d-inline-block mb-3 text-secondary">
                        <x-application-logo />
                    </a>
                    <h1 class="h4 mb-1">Sign in</h1>
                    <p class="text-muted mb-0">Access the Pharmacy Management System</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="you@example.com" required autofocus autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
