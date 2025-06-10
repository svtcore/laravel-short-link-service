@extends('layouts.app')

@section('title', 'Login - URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/login.css'])
@endsection

@section('content')
<section class="auth-section">
    <div class="container d-flex flex-column align-items-center">
        <div class="auth-icon mb-4">
            <i class="bi bi-box-arrow-in-right fs-1 text-primary"></i>
        </div>
        
        <div class="auth-card shadow-lg bg-white rounded-3 p-4 mb-4">
            <h1 class="display-6 mb-3 text-center">Welcome Back</h1>
            <p class="text-muted text-center mb-4">Enter your credentials to access your dashboard</p>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Login failed!</strong>
                <ul class="list-unstyled mt-2 mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small text-muted">Email Address</label>
                    <input id="email" type="email" 
                           class="form-control form-control-lg rounded-2 @error('email') is-invalid @enderror" 
                           name="email" 
                           placeholder="name@example.com"
                           required autofocus>
                    @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small text-muted">Password</label>
                    <input id="password" type="password" 
                           class="form-control form-control-lg rounded-2 @error('password') is-invalid @enderror" 
                           name="password" 
                           placeholder="••••••••"
                           required>
                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Remember me
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">
                        Forgot password?
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-1 mb-3">
                    Sign In
                </button>

                <div class="text-center small text-muted mt-4">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-decoration-none text-primary">Create one</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@endsection
