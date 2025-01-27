@extends('layouts.app')

@section('title', 'Register - URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/register.css'])
@endsection

@section('content')
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-3">Register</h1>
        <p class="lead mb-4">Create your account to start using our URL shortening service.</p>
        <section class="container settings-section">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" title="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                <ul class="list-unstyled mt-2">
                    @foreach($errors->all() as $error)
                    <li class="bg-light rounded-3 mb-2 p-2 font-weight-bold">
                        {{ $error }}
                    </li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" title="Close"></button>
            </div>
            @endif

            <div class="d-flex justify-content-center">
                <div class="settings-card flex-grow-1" style="max-width: 400px; width: 100%;">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus maxlength="255" placeholder="Enter your full name">

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" maxlength="255" placeholder="Enter your email address">

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" minlength="8" maxlength="255" placeholder="Enter your password">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" minlength="8" maxlength="255" placeholder="Confirm your password">
                        </div>

                        <button type="submit" class="btn btn-outline-custom btn-register w-100">
                            {{ __('Register') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection

@section('scripts')
@endsection
