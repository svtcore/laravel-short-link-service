<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="URL shortening service for creating memorable short links">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    @yield('styles')
</head>

<body>
    @if(app()->isDownForMaintenance())
        <div class="maintenance-banner fixed-bottom">
            <div class="container-fluid bg-danger text-white py-2">
                <div class="row align-items-center">
                    <div class="col text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>MAINTENANCE NOTICE:</strong> This website is currently undergoing maintenance. Access is restricted to administrators only.
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">URL Shortening Service</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @role('user|admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.links.index') }}">My Links</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.settings.index') }}">Settings</a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn" type="submit">Log Out</button>
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="btn btn-outline-custom" href="{{ route('login') }}">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-custom" href="{{ route('register') }}">Register</a>
                    </li>
                    @endrole
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Footer Section -->
    <footer class="footer py-1 mt-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mt-2">
                        &copy; {{ date('Y') }} URL Shortening Service. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6">
                    <ul class="nav justify-content-center justify-content-md-end">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">Privacy Policy</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">Terms of Service</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    @yield('scripts')
</body>

</html>