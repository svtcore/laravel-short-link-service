@extends('layouts.app')

@section('title', 'Register - URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/register.css'])
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')
<section class="auth-section">
    <div class="container d-flex flex-column align-items-center">
        <div class="auth-icon mb-4">
            <i class="bi bi-person-plus fs-1 text-primary"></i>
        </div>

        <div class="auth-card shadow-lg bg-white rounded-3 p-4 mb-4" style="max-width: 500px; width: 100%;">
            <h1 class="display-6 mb-3 text-center">Create Account</h1>
            <p class="text-muted text-center mb-4">Start shortening URLs in minutes</p>

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
                <strong>Registration failed!</strong>
                <ul class="list-unstyled mt-2 mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label small text-muted">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input id="name" type="text" 
                               class="form-control form-control-lg rounded-2 @error('name') is-invalid @enderror" 
                               name="name" 
                               placeholder="John Doe"
                               required 
                               autofocus>
                    </div>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small text-muted">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input id="email" type="email" 
                               class="form-control form-control-lg rounded-2 @error('email') is-invalid @enderror" 
                               name="email" 
                               placeholder="name@example.com"
                               required>
                    </div>
                    @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input id="password" type="password" 
                               class="form-control form-control-lg rounded-2 @error('password') is-invalid @enderror" 
                               name="password" 
                               placeholder="••••••••"
                               required>
                    </div>
                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label small text-muted">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-shield-lock text-muted"></i>
                        </span>
                        <input id="password-confirm" type="password" 
                               class="form-control form-control-lg rounded-2" 
                               name="password_confirmation" 
                               placeholder="••••••••"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-1 mb-3">
                    <i class="bi bi-person-plus me-2"></i>
                    Create Account
                </button>

                <div class="text-center small text-muted mt-4">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-decoration-none text-primary">Login here</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@endsection